<?php

namespace Praxis\Plugins\PraxiSelf\Scoring;

use App\Models\TestAttempt;
use Praxis\Core\TestEngine\Contracts\ScoringEngineContract;
use Praxis\Plugins\PraxiSelf\Data\Exercises;

/**
 * Moteur de scoring PraxiSelf — Affirmation de soi.
 *
 * Structure des réponses attendues :
 *   Chaque réponse (TestAnswer) est liée à une question dont le champ `scoring`
 *   contient au minimum :
 *     - 'exercise_id'  : string  ex. 'SE-01'
 *     - 'dimension'    : string  ex. 'estime_de_soi'
 *     - 'weight'       : float   ex. 1.2
 *     - 'max'          : int     ex. 5 (échelle Likert)
 *
 *   La valeur de réponse (answer.value) est un entier entre 1 et 5 (Likert),
 *   ou 0/1 pour les questions binaires.
 *
 * Dimensions scorées :
 *   estime_de_soi, assertivite_comportementale, gestion_du_regard,
 *   expression_des_besoins, resilience_identitaire
 */
class PraxiSelfScoringEngine implements ScoringEngineContract
{
    private const DIMENSIONS = [
        'estime_de_soi',
        'assertivite_comportementale',
        'gestion_du_regard',
        'expression_des_besoins',
        'resilience_identitaire',
    ];

    private const MAX_LIKERT = 5;

    public function key(): string
    {
        return 'praxiself-scoring';
    }

    public function score(TestAttempt $attempt): array
    {
        // Accumulateurs : somme pondérée et poids total par dimension
        $weighted = array_fill_keys(self::DIMENSIONS, 0.0);
        $totals   = array_fill_keys(self::DIMENSIONS, 0.0);

        $answers = $attempt->answers()->with('question')->get();

        foreach ($answers as $answer) {
            $scoringMeta = $answer->question->scoring ?? [];
            $dimension   = $scoringMeta['dimension'] ?? null;
            $weight      = (float) ($scoringMeta['weight'] ?? 1.0);
            $max         = (int) ($scoringMeta['max'] ?? self::MAX_LIKERT);
            $value       = (float) $answer->value;

            if (!$dimension || !in_array($dimension, self::DIMENSIONS, true)) {
                continue;
            }

            // Normalise la valeur brute en [0, 1]
            $normalized = $max > 0 ? min(1.0, max(0.0, $value / $max)) : 0.0;

            $weighted[$dimension] += $normalized * $weight;
            $totals[$dimension]   += $weight;
        }

        // Score brut par dimension [0–100]
        $rawScores = [];
        foreach (self::DIMENSIONS as $dim) {
            $rawScores[$dim] = $totals[$dim] > 0
                ? round(($weighted[$dim] / $totals[$dim]) * 100, 1)
                : 0.0;
        }

        // Score global : moyenne arithmétique des 5 dimensions
        $globalScore = round(array_sum($rawScores) / count($rawScores), 1);

        // Identification de la dimension la plus faible (pour recommandations)
        $weakestDimension = array_key_first(
            array_slice(arsort($rawScores) ? array_reverse($rawScores, true) : $rawScores, 0, 1, true)
        );
        // arsort modifie le tableau en place — recalculer proprement
        $sortedAsc = $rawScores;
        asort($sortedAsc);
        $weakestDimension = (string) array_key_first($sortedAsc);

        // Profil assertif : label calculé depuis le score global
        $profile = $this->profileLabel($globalScore);

        // Exercices recommandés ciblant la dimension la plus faible
        $recommendedExercises = Exercises::recommendedFor($weakestDimension, 3);

        // Métadonnées des dimensions (labels, couleurs)
        $dimensionsMeta = Exercises::dimensionsLabels();

        return [
            'engine'                  => $this->key(),
            'dimensions'              => $rawScores,
            'raw_scores'              => $rawScores,
            'global_score'            => $globalScore,
            'profile'                 => $profile,
            'weakest_dimension'       => $weakestDimension,
            'recommended_exercises'   => $recommendedExercises,
            'dimensions_meta'         => $dimensionsMeta,
            'meta' => [
                'total_answers'   => $answers->count(),
                'dimensions_used' => array_keys(array_filter($totals, fn ($t) => $t > 0)),
            ],
            'computed_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Traduit le score global en libellé de profil assertif.
     */
    private function profileLabel(float $score): array
    {
        return match (true) {
            $score >= 85 => [
                'label'   => 'Assertif(ve) confirmé(e)',
                'summary' => 'Tu possèdes une base solide d\'affirmation de soi. Ton prochain défi : maintenir cette posture sous pression extrême.',
                'emoji'   => '🌟',
                'level'   => 5,
            ],
            $score >= 70 => [
                'label'   => 'Assertif(ve) en progression',
                'summary' => 'Tu t\'affirmes bien dans la plupart des situations. Quelques domaines ciblés peuvent encore être renforcés.',
                'emoji'   => '💪',
                'level'   => 4,
            ],
            $score >= 55 => [
                'label'   => 'Assertivité en construction',
                'summary' => 'Tu as des ressources réelles, mais certaines situations te coûtent encore beaucoup d\'énergie. Les exercices ciblés produiront des effets rapides.',
                'emoji'   => '🌱',
                'level'   => 3,
            ],
            $score >= 40 => [
                'label'   => 'Assertivité à développer',
                'summary' => 'Il existe des blocages significatifs. C\'est tout à fait normal et tout à fait modifiable. Commence par les exercices de niveau 1.',
                'emoji'   => '🔧',
                'level'   => 2,
            ],
            default => [
                'label'   => 'Assertivité naissante',
                'summary' => 'Tu es au début de ce parcours. Chaque petit pas compte énormément. Les bases de l\'estime de soi sont le meilleur point de départ.',
                'emoji'   => '🌅',
                'level'   => 1,
            ],
        };
    }
}
