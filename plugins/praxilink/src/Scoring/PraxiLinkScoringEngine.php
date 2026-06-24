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
     * Le test est un questionnaire d'auto-évaluation : 20 items "scale" (1-5),
     * 4 par dimension. Chaque TestAnswer porte :
     *   - question->scoring['dimension']  → la dimension évaluée
     *   - value (entier 1-5)              → le niveau d'accord déclaré
     *
     * On moyenne les items par dimension, on normalise sur 0-100 (amplitude
     * réelle 1-5), puis on calcule un score global pondéré par les poids des
     * dimensions. Le format de sortie respecte PraxiLinkResult.vue.
     */
    public function score(TestAttempt $attempt): array
    {
        $dimensions = $this->dimensions();
        $sums   = array_fill_keys(array_keys($dimensions), 0.0);
        $counts = array_fill_keys(array_keys($dimensions), 0);

        foreach ($attempt->answers()->with('question')->get() as $answer) {
            $dim = $answer->question->scoring['dimension'] ?? null;
            if ($dim === null || ! isset($sums[$dim])) {
                continue;
            }
            // Le frontend émet 1..options.max (= 5), jamais 0.
            $val = max(1, min(5, (int) $answer->value));
            $sums[$dim] += $val;
            $counts[$dim]++;
        }

        // Normalisation 0-100 sur l'amplitude réelle 1-5 : (moy - 1) / 4 * 100.
        $normalizedScores = [];
        foreach (array_keys($dimensions) as $dim) {
            $moy = $counts[$dim] > 0 ? $sums[$dim] / $counts[$dim] : 1.0;
            $normalizedScores[$dim] = round((($moy - 1) / 4) * 100, 1);
        }

        // Score global pondéré (les poids de dimensions() somment à 1.0).
        $globalScore = 0.0;
        foreach ($dimensions as $dim => $weight) {
            $globalScore += $normalizedScores[$dim] * $weight;
        }
        $globalScore = round($globalScore, 1);

        $dominantStyle = $this->computeDominantStyle($normalizedScores);

        return [
            'engine'       => $this->key(),
            'dimensions'   => $dimensions,
            'norm_scores'  => $normalizedScores,
            'global_score' => $globalScore,
            'meta'         => [
                'dominant_style'     => $dominantStyle['label'],
                'dominant_style_key' => $dominantStyle['key'],
                'dominant_dimension' => $dominantStyle['top_dimension'],
                'interpretation'     => $this->interpretScore($globalScore),
                'strengths'          => $this->detectStrengths($normalizedScores),
                'growth_areas'       => $this->detectGrowthAreas($normalizedScores),
            ],
            'computed_at'  => Carbon::now()->toIso8601String(),
        ];
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
}
