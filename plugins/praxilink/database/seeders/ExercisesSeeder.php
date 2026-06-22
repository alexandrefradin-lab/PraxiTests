<?php

namespace Praxis\Plugins\PraxiLink\Database\Seeders;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSection;
use Illuminate\Database\Seeder;
use Praxis\Plugins\PraxiLink\Data\Questions;

/**
 * Seeder PraxiLink — Communication assertive.
 *
 * Crée / met à jour :
 *   - Le test "praxilink-assertivite"
 *   - 5 sections (une par dimension)
 *   - 20 questions d'auto-évaluation, échelle 1-5 (type "scale")
 *
 * Échelle : 1 = Pas du tout, 5 = Tout à fait. Le champ `scoring` de chaque
 * question porte sa `dimension`, lue par PraxiLinkScoringEngine.
 */
class ExercisesSeeder extends Seeder
{
    public function run(): void
    {
        $test = Test::updateOrCreate(
            ['slug' => 'praxilink-assertivite'],
            [
                'name'              => "L'Art des Liens — Communication assertive",
                'description'       => "Ce que ce test mesure : ta communication assertive, sur 5 dimensions — écoute active, expression assertive de tes besoins, gestion des conflits, empathie relationnelle et feedback constructif. 20 affirmations, échelle de 1 à 5.",
                'type'              => 'questionnaire',
                'scoring_engine'    => 'praxilink-scoring',
                'estimated_minutes' => 5,
                'published'         => false,
                'public'            => false,
            ]
        );

        $scale = [
            'max'       => 5,
            'min_label' => 'Pas du tout',
            'max_label' => 'Tout à fait',
        ];

        $order = 0;
        foreach (Questions::sections() as $dimension => $section) {
            $testSection = TestSection::updateOrCreate(
                ['test_id' => $test->id, 'order' => ++$order],
                [
                    'title'       => $section['label'],
                    'description' => null,
                ]
            );

            $qOrder = 0;
            foreach ($section['questions'] as $q) {
                TestQuestion::updateOrCreate(
                    ['section_id' => $testSection->id, 'order' => ++$qOrder],
                    [
                        // 'scale' est le seul type d'échelle rendu par le
                        // frontend (AttemptPlay.vue), qui émet 1..options.max.
                        'type'     => 'scale',
                        'prompt'   => $q['texte'],
                        'options'  => $scale,
                        'scoring'  => [
                            'key'       => $q['key'],
                            'dimension' => $dimension,
                            'max'       => 5,
                            'min'       => 1,
                        ],
                        'required' => true,
                    ]
                );
            }
        }
    }
}
