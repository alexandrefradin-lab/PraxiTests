<?php

namespace Praxis\Plugins\PLUGIN_CLASS\Data;

/**
 * Données statiques du test : questions et métadonnées des dimensions.
 *
 * Option A : tout en PHP ici (simple, peu de questions)
 * Option B : charger depuis un JSON (recommandé si > 30 questions)
 *   static $cache = json_decode(file_get_contents(__DIR__ . '/questions.json'), true);
 */
class Questions
{
    /**
     * Retourne toutes les questions du test.
     *
     * Champ 'scoring' : objet JSON libre utilisé par le ScoringEngine.
     * Convention recommandée : { "dimension": "dim_alpha", "weight": 1 }
     */
    public static function all(): array
    {
        // ── Exemple : 6 questions sur 2 dimensions ────────────────────────
        return [
            // Dimension alpha — 3 questions, échelle 1-4
            [
                'section'  => 'Dimension Alpha',
                'prompt'   => 'Question 1 de la dimension Alpha.',
                'type'     => 'scale',       // scale | single | multi | text
                'options'  => ['min' => 1, 'max' => 4, 'min_label' => 'Pas du tout', 'max_label' => 'Tout à fait'],
                'scoring'  => ['dimension' => 'dim_alpha', 'weight' => 1],
            ],
            [
                'section'  => 'Dimension Alpha',
                'prompt'   => 'Question 2 de la dimension Alpha.',
                'type'     => 'scale',
                'options'  => ['min' => 1, 'max' => 4, 'min_label' => 'Jamais', 'max_label' => 'Toujours'],
                'scoring'  => ['dimension' => 'dim_alpha', 'weight' => 1],
            ],
            [
                'section'  => 'Dimension Alpha',
                'prompt'   => 'Question 3 (inversée) de la dimension Alpha.',
                'type'     => 'scale',
                'options'  => ['min' => 1, 'max' => 4, 'min_label' => 'Tout à fait', 'max_label' => 'Pas du tout'],
                'scoring'  => ['dimension' => 'dim_alpha', 'weight' => 1, 'reversed' => true],
            ],

            // Dimension beta — 3 questions, choix unique
            [
                'section'  => 'Dimension Beta',
                'prompt'   => 'Question 1 de la dimension Beta.',
                'type'     => 'single',
                'options'  => [
                    ['value' => 4, 'label' => 'Réponse très favorable'],
                    ['value' => 3, 'label' => 'Réponse favorable'],
                    ['value' => 2, 'label' => 'Réponse défavorable'],
                    ['value' => 1, 'label' => 'Réponse très défavorable'],
                ],
                'scoring'  => ['dimension' => 'dim_beta', 'weight' => 1],
            ],
            [
                'section'  => 'Dimension Beta',
                'prompt'   => 'Question 2 de la dimension Beta.',
                'type'     => 'single',
                'options'  => [
                    ['value' => 4, 'label' => 'Option A'],
                    ['value' => 3, 'label' => 'Option B'],
                    ['value' => 2, 'label' => 'Option C'],
                    ['value' => 1, 'label' => 'Option D'],
                ],
                'scoring'  => ['dimension' => 'dim_beta', 'weight' => 1],
            ],
            [
                'section'  => 'Dimension Beta',
                'prompt'   => 'Question 3 de la dimension Beta.',
                'type'     => 'scale',
                'options'  => ['min' => 1, 'max' => 4],
                'scoring'  => ['dimension' => 'dim_beta', 'weight' => 1],
            ],
        ];
    }

    /**
     * Métadonnées des dimensions — utilisées pour l'affichage et les prompts IA.
     */
    public static function dimensions(): array
    {
        return [
            'dim_alpha' => [
                'label'       => 'Dimension Alpha',
                'description' => 'Ce que mesure cette dimension.',
                'color'       => '#B8913A', // utiliser var(--pt-gold) de préférence en Vue
            ],
            'dim_beta' => [
                'label'       => 'Dimension Beta',
                'description' => 'Ce que mesure cette dimension.',
                'color'       => '#1B2B3A',
            ],
        ];
    }
}
