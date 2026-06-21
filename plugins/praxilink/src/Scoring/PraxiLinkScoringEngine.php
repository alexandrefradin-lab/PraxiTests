<?php

namespace Praxis\Plugins\PraxiLink\Scoring;

use App\Models\TestAttempt;
use Praxis\Core\TestEngine\Contracts\ScoringEngineContract;
use Carbon\Carbon;

class PraxiLinkScoringEngine implements ScoringEngineContract
{
    /**
     * Clé unique du moteur de scoring, référencée dans plugin.json et dans les filtres.
     */
    public function key(): string
    {
        return 'praxilink-scoring';
    }

    /**
     * Dimensions évaluées et leurs poids relatifs dans le score global.
     * Total des poids = 1.0
     *
     * @return array<string, float>
     */
    public function dimensions(): array
    {
        return [
            'ecoute_active'          => 0.20,
            'expression_assertive'   => 0.25,
            'gestion_conflits'       => 0.20,
            'empathie_relationnelle' => 0.20,
            'feedback_constructif'   => 0.15,
        ];
    }

    /**
     * Point d'entrée standard du contrat de scoring.
     *
     * Reconstruit le tableau de réponses indexé par exercise id à partir des
     * réponses persistées de l'attempt, puis délègue au moteur de calcul interne.
     *
     * Chaque TestAnswer porte :
     *   - question->scoring['exercise_id']  → l'identifiant d'exercice (ex. 'ea-01')
     *   - value (cast array)                → le payload de réponse de l'exercice
     */
    public function score(TestAttempt $attempt): array
    {
        $answers = [];

        $rows = $attempt->answers()->with('question')->get();

        foreach ($rows as $row) {
            $scoringMeta = $row->question->scoring ?? [];
            $exerciseId  = $scoringMeta['exercise_id']
                ?? $row->question->meta['exercise_id']
                ?? null;

            if ($exerciseId === null) {
                continue;
            }

            // value est déjà casté en array sur TestAnswer ; sinon on encapsule.
            $payload = is_array($row->value) ? $row->value : ['answer' => $row->value];

            $answers[$exerciseId] = $payload;
        }

        $context = [
            'user_id'    => $attempt->user_id,
            'test_id'    => $attempt->test_id,
            'attempt_id' => $attempt->id,
        ];

        return $this->computeScores($answers, $context);
    }

    /**
     * Calcule les scores à partir des réponses d'un attempt.
     *
     * Structure des réponses attendue :
     * [
     *   'ea-01'  => ['answer' => 'B', 'duration_seconds' => 45],
     *   'cnv-01' => ['osbd' => ['O' => '...', 'S' => '...', 'B' => '...', 'D' => '...'], 'self_score' => 7],
     *   ...
     * ]
     *
     * @param  array<string, mixed>  $answers   Réponses indexées par exercise id
     * @param  array<string, mixed>  $context   Métadonnées de l'attempt (user_id, test_id, etc.)
     * @return array<string, mixed>
     */
    private function computeScores(array $answers, array $context = []): array
    {
        $dimensions    = $this->dimensions();
        $rawScores     = array_fill_keys(array_keys($dimensions), 0.0);
        $maxScores     = array_fill_keys(array_keys($dimensions), 0.0);
        $exerciseCount = array_fill_keys(array_keys($dimensions), 0);

        // Définition des exercices et de leur dimension + poids de scoring
        $exerciseMeta = $this->buildExerciseMeta();

        foreach ($answers as $exerciseId => $response) {
            if (!isset($exerciseMeta[$exerciseId])) {
                continue;
            }

            $meta = $exerciseMeta[$exerciseId];

            // Normalise la réponse en tableau (défensif).
            $response = is_array($response) ? $response : ['answer' => $response];

            // Récupère le score brut pour cet exercice (0.0 – 1.0)
            $exerciseScore = $this->scoreExercise($exerciseId, $response, $meta);

            // Répartit sur les dimensions concernées
            foreach ($meta['dimensions'] as $dim => $weight) {
                if (!isset($rawScores[$dim])) {
                    continue;
                }
                $rawScores[$dim]  += $exerciseScore * $weight;
                $maxScores[$dim]  += $weight;
                $exerciseCount[$dim]++;
            }
        }

        // Normalise chaque dimension sur 0-100
        $normalizedScores = [];
        foreach (array_keys($dimensions) as $dim) {
            $normalizedScores[$dim] = $maxScores[$dim] > 0
                ? round(($rawScores[$dim] / $maxScores[$dim]) * 100, 1)
                : 0.0;
        }

        // Score global pondéré
        $globalScore = 0.0;
        foreach ($dimensions as $dim => $weight) {
            $globalScore += ($normalizedScores[$dim] * $weight);
        }
        $globalScore = round($globalScore, 1);

        // Style communicant dominant
        $dominantStyle = $this->computeDominantStyle($normalizedScores);

        return [
            'engine'           => $this->key(),
            'dimensions'       => array_keys($dimensions),
            'raw_scores'       => $rawScores,
            'norm_scores'      => $normalizedScores,
            'global_score'     => $globalScore,
            'meta'             => [
                'dominant_style'    => $dominantStyle['label'],
                'dominant_style_key'=> $dominantStyle['key'],
                'dominant_dimension'=> $dominantStyle['top_dimension'],
                'exercise_count'    => array_sum($exerciseCount),
                'dimensions_count'  => $exerciseCount,
                'interpretation'    => $this->interpretScore($globalScore),
                'strengths'         => $this->detectStrengths($normalizedScores),
                'growth_areas'      => $this->detectGrowthAreas($normalizedScores),
            ],
            'computed_at'      => Carbon::now()->toIso8601String(),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // PRIVATE — Scoring par exercice
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Calcule un score normalisé (0.0 – 1.0) pour un exercice donné.
     *
     * @param  string               $exerciseId
     * @param  array<string, mixed> $response
     * @param  array<string, mixed> $meta
     * @return float
     */
    private function scoreExercise(string $exerciseId, array $response, array $meta): float
    {
        $type = $meta['type'] ?? 'choix_multiple';

        return match ($type) {
            'choix_multiple'          => $this->scoreMultipleChoice($response, $meta),
            'cases_a_cocher'          => $this->scoreCheckboxes($response, $meta),
            'classification_binaire'  => $this->scoreBinaryClassification($response, $meta),
            'classement'              => $this->scoreRanking($response, $meta),
            'association'             => $this->scoreAssociation($response, $meta),
            'redaction_libre',
            'redaction_structuree',
            'redaction_et_analyse',
            'redaction_libre_integratrice',
            'plan_negociation',
            'reformulation_et_reponse',
            'analyse_modale',
            'analyse_et_plan',
            'tableau_interets',
            'association_justifiee',
            'selection_et_justification',
            'transformation'          => $this->scoreFreeText($response, $meta),
            default                   => 0.5,
        };
    }

    /**
     * Score pour un QCM à réponse unique.
     */
    private function scoreMultipleChoice(array $response, array $meta): float
    {
        $answer  = $response['answer'] ?? null;
        $correct = $meta['correct']    ?? null;

        if ($answer === null || $correct === null) {
            return 0.0;
        }

        return strtoupper((string) $answer) === strtoupper((string) $correct) ? 1.0 : 0.0;
    }

    /**
     * Score pour les cases à cocher (toutes correctes attendues).
     */
    private function scoreCheckboxes(array $response, array $meta): float
    {
        $selected = array_map('strtoupper', (array) ($response['selected'] ?? []));
        $correct  = array_map('strtoupper', (array) ($meta['correct'] ?? []));

        if (empty($correct)) {
            return 0.0;
        }

        sort($selected);
        sort($correct);

        if ($selected === $correct) {
            return 1.0;
        }

        // Score partiel : proportion de bonnes réponses sélectionnées moins les erreurs
        $truePositives  = count(array_intersect($selected, $correct));
        $falsePositives = count(array_diff($selected, $correct));
        $totalCorrect   = count($correct);

        $partial = max(0.0, ($truePositives - $falsePositives) / $totalCorrect);
        return round(min(1.0, $partial), 2);
    }

    /**
     * Score pour la classification binaire (O/E, etc.).
     */
    private function scoreBinaryClassification(array $response, array $meta): float
    {
        $items    = $meta['items']    ?? [];
        $answers  = $response['answers'] ?? [];

        if (empty($items)) {
            return 0.0;
        }

        $correct = 0;
        foreach ($items as $idx => $item) {
            $userAnswer = strtoupper((string) ($answers[$idx] ?? $answers[$item['phrase']] ?? ''));
            if ($userAnswer === strtoupper((string) $item['correct'])) {
                $correct++;
            }
        }

        return round($correct / count($items), 2);
    }

    /**
     * Score pour le classement (ordre attendu).
     */
    private function scoreRanking(array $response, array $meta): float
    {
        $userOrder    = (array) ($response['order'] ?? []);
        $correctOrder = (array) ($meta['correct_order'] ?? []);

        if (empty($correctOrder) || count($userOrder) !== count($correctOrder)) {
            return 0.0;
        }

        // Kendall tau distance simplifiée
        $n          = count($correctOrder);
        $concordant = 0;

        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $correctRel = ($correctOrder[$i] <=> $correctOrder[$j]);
                $userRel    = (array_search($correctOrder[$i], $userOrder) <=>
                               array_search($correctOrder[$j], $userOrder));

                if ($correctRel * $userRel > 0) {
                    $concordant++;
                }
            }
        }

        $total = ($n * ($n - 1)) / 2;
        return $total > 0 ? round($concordant / $total, 2) : 0.0;
    }

    /**
     * Score pour les associations (clé => valeur).
     */
    private function scoreAssociation(array $response, array $meta): float
    {
        $userAssoc    = (array) ($response['associations'] ?? []);
        $correctAssoc = (array) ($meta['correct'] ?? []);

        if (empty($correctAssoc)) {
            return 0.0;
        }

        $correct = 0;
        foreach ($correctAssoc as $key => $expectedValue) {
            $userValue = strtolower(trim((string) ($userAssoc[$key] ?? '')));
            if ($userValue === strtolower(trim((string) $expectedValue))) {
                $correct++;
            }
        }

        return round($correct / count($correctAssoc), 2);
    }

    /**
     * Score pour les exercices de rédaction libre basé sur l'auto-évaluation
     * ou sur un score de complétude (champs remplis).
     */
    private function scoreFreeText(array $response, array $meta): float
    {
        // 1. Auto-évaluation explicite (0-10 → normalisé)
        if (isset($response['self_score'])) {
            return min(1.0, max(0.0, (float) $response['self_score'] / 10));
        }

        // 2. Évaluation par critères cochés
        if (isset($response['criteria_met']) && is_array($response['criteria_met'])) {
            $total = count($meta['indicateurs'] ?? $response['criteria_met']);
            $met   = count(array_filter($response['criteria_met']));
            return $total > 0 ? round($met / $total, 2) : 0.5;
        }

        // 3. Score de complétude : combien de champs obligatoires sont remplis
        if (isset($meta['champs']) && is_array($meta['champs'])) {
            $filled = 0;
            foreach ($meta['champs'] as $champ) {
                $key = strtolower(str_replace(' ', '_', $champ));
                if (!empty($response[$key]) || !empty($response[$champ])) {
                    $filled++;
                }
            }
            $total = count($meta['champs']);
            return $total > 0 ? round($filled / $total, 2) : 0.5;
        }

        // 4. Fallback : réponse fournie ou non
        $hasContent = !empty($response['content'])
            || !empty($response['text'])
            || !empty($response['reponse']);

        return $hasContent ? 0.7 : 0.0;
    }

    // ──────────────────────────────────────────────────────────────────────
    // PRIVATE — Style communicant et interprétation
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Détermine le style communicant dominant à partir des scores normalisés.
     *
     * @param  array<string, float> $normalizedScores
     * @return array<string, string>
     */
    private function computeDominantStyle(array $normalizedScores): array
    {
        arsort($normalizedScores);
        $topDimension = array_key_first($normalizedScores);
        $topScore     = reset($normalizedScores);

        $profiles = [
            'ecoute_active' => [
                'key'   => 'facilitateur_bienveillant',
                'label' => 'Facilitateur Bienveillant',
            ],
            'expression_assertive' => [
                'key'   => 'affirmateur_direct',
                'label' => 'Affirmateur Direct',
            ],
            'gestion_conflits' => [
                'key'   => 'diplomate_strategique',
                'label' => 'Diplomate Stratégique',
            ],
            'empathie_relationnelle' => [
                'key'   => 'diplomate_empathique',
                'label' => 'Diplomate Empathique',
            ],
            'feedback_constructif' => [
                'key'   => 'coach_developpeur',
                'label' => 'Coach Développeur',
            ],
        ];

        $profile = $profiles[$topDimension] ?? [
            'key'   => 'communicant_equilibre',
            'label' => 'Communicant Équilibré',
        ];

        // Si le score global est homogène (toutes dimensions proches), profil équilibré
        $scores     = array_values($normalizedScores);
        $maxScore   = max($scores);
        $minScore   = min($scores);
        if (($maxScore - $minScore) < 10 && $maxScore > 60) {
            $profile = [
                'key'   => 'communicant_equilibre',
                'label' => 'Communicant Équilibré',
            ];
        }

        return array_merge($profile, ['top_dimension' => $topDimension ?? '']);
    }

    /**
     * Interprétation textuelle du score global.
     */
    private function interpretScore(float $score): string
    {
        return match (true) {
            $score >= 85 => 'Excellence communicante — vous maîtrisez les fondamentaux de la communication assertive et empathique.',
            $score >= 70 => 'Communicant confirmé — vous disposez de bases solides avec quelques axes de perfectionnement.',
            $score >= 55 => 'En développement — vous avez acquis des fondations mais certaines compétences méritent une pratique régulière.',
            $score >= 40 => 'Débutant structuré — les concepts sont en cours d\'assimilation, une pratique guidée accélérera vos progrès.',
            default      => 'Point de départ — ce module est une excellente opportunité de découvrir et développer votre communication.',
        };
    }

    /**
     * Identifie les points forts (dimensions >= 70).
     *
     * @param  array<string, float> $normalizedScores
     * @return array<string>
     */
    private function detectStrengths(array $normalizedScores): array
    {
        return array_keys(array_filter($normalizedScores, fn (float $s) => $s >= 70));
    }

    /**
     * Identifie les axes de développement (dimensions < 55).
     *
     * @param  array<string, float> $normalizedScores
     * @return array<string>
     */
    private function detectGrowthAreas(array $normalizedScores): array
    {
        return array_keys(array_filter($normalizedScores, fn (float $s) => $s < 55));
    }

    // ──────────────────────────────────────────────────────────────────────
    // PRIVATE — Métadonnées de scoring par exercice
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Construit la table de correspondance exercice → dimensions + poids + type.
     *
     * @return array<string, array<string, mixed>>
     */
    private function buildExerciseMeta(): array
    {
        return [
            'ea-01'  => ['type' => 'choix_multiple',         'correct' => 'B',   'dimensions' => ['ecoute_active' => 1.0]],
            'ea-02'  => ['type' => 'classement',             'correct_order' => [4,3,2,1], 'dimensions' => ['ecoute_active' => 0.8]],
            'ea-03'  => ['type' => 'redaction_libre',        'champs' => ['reformulation'], 'dimensions' => ['ecoute_active' => 1.0]],
            'ea-04'  => ['type' => 'cases_a_cocher',         'correct' => ['A','B','C','E'], 'dimensions' => ['ecoute_active' => 0.9]],

            'cnv-01' => ['type' => 'redaction_structuree',   'champs' => ['Observation','Sentiment','Besoin','Demande'], 'dimensions' => ['expression_assertive' => 1.0]],
            'cnv-02' => ['type' => 'classification_binaire', 'items' => [
                ['phrase' => 'Tu as interrompu Camille trois fois pendant sa présentation.',   'correct' => 'O'],
                ['phrase' => 'Tu es irrespectueux envers tes collègues.',                       'correct' => 'E'],
                ['phrase' => 'Ce rapport a été remis deux jours après la deadline convenue.',   'correct' => 'O'],
                ['phrase' => 'Tu ne t\'impliques jamais dans les projets collectifs.',          'correct' => 'E'],
                ['phrase' => 'Lors de la réunion du 10 juin, tu n\'as pas pris la parole.',    'correct' => 'O'],
                ['phrase' => 'Tu es toujours dans la lune en réunion.',                         'correct' => 'E'],
            ], 'dimensions' => ['expression_assertive' => 0.8]],
            'cnv-03' => ['type' => 'selection_et_justification', 'dimensions' => ['empathie_relationnelle' => 0.9]],

            'ass-01' => ['type' => 'association',            'correct' => ['A' => 'Passif','B' => 'Agressif','C' => 'Assertif','D' => 'Agressif passif'], 'dimensions' => ['expression_assertive' => 1.0]],
            'ass-02' => ['type' => 'redaction_libre',        'champs' => ['relance_1','relance_2','relance_3'], 'dimensions' => ['expression_assertive' => 1.0]],
            'ass-03' => ['type' => 'redaction_libre',        'champs' => ['reponse_assertive'], 'dimensions' => ['expression_assertive' => 1.0]],

            'conf-01'=> ['type' => 'analyse_modale',         'correct_mode' => 'Collaboration', 'dimensions' => ['gestion_conflits' => 1.0]],
            'conf-02'=> ['type' => 'tableau_interets',       'dimensions' => ['gestion_conflits' => 1.0]],
            'conf-03'=> ['type' => 'redaction_structuree',   'champs' => ['Validation','Clarification','Proposition'], 'dimensions' => ['gestion_conflits' => 1.0]],

            'fb-01'  => ['type' => 'redaction_structuree',   'champs' => ['D — Describe','E — Express','S — Specify','C — Consequences'], 'dimensions' => ['feedback_constructif' => 1.0]],
            'fb-02'  => ['type' => 'choix_multiple',         'correct' => 'B', 'dimensions' => ['feedback_constructif' => 0.7]],
            'fb-03'  => ['type' => 'redaction_et_analyse',   'champs' => ['Positif','Axe d\'amélioration','Positif de clôture'], 'dimensions' => ['feedback_constructif' => 0.9]],

            'emp-01' => ['type' => 'association_justifiee',  'dimensions' => ['empathie_relationnelle' => 1.0]],
            'emp-02' => ['type' => 'analyse_et_plan',        'dimensions' => ['empathie_relationnelle' => 0.9]],
            'emp-03' => ['type' => 'transformation',         'dimensions' => ['expression_assertive' => 0.8]],

            'neg-01' => ['type' => 'plan_negociation',       'dimensions' => ['gestion_conflits' => 1.0]],
            'neg-02' => ['type' => 'reformulation_et_reponse', 'dimensions' => ['gestion_conflits' => 0.9]],

            'int-01' => ['type' => 'redaction_libre_integratrice', 'champs' => ['ouverture','reflet','osbd','validation','demande','cloture'], 'dimensions' => [
                'ecoute_active'          => 0.2,
                'expression_assertive'   => 0.2,
                'gestion_conflits'       => 0.2,
                'empathie_relationnelle' => 0.2,
                'feedback_constructif'   => 0.2,
            ]],
        ];
    }
}
