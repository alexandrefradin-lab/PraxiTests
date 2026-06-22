<?php

namespace Praxis\Plugins\PraxiFlow\Database\Seeders;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSection;
use Illuminate\Database\Seeder;
use Praxis\Plugins\PraxiFlow\Data\Exercises;

class ExercisesSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Créer ou mettre à jour le test ────────────────────────────────
        $test = Test::updateOrCreate(
            ['slug' => 'praxiflow-productivite'],
            [
                'name'              => "Le Maître du Temps — Évaluation de ta productivité",
                'description'       => "Ce que ce test mesure : ta gestion du temps et ta productivité, sur 5 leviers — planification, concentration, gestion des priorités, gestion de l'énergie et lutte contre la procrastination. Tu repars avec un plan d'action personnalisé sur 7 jours.",
                'type'              => 'questionnaire',
                'scoring_engine'    => 'praxiflow-scoring',
                'estimated_minutes' => 7,
                'published'         => false,
                'public'            => false,
            ]
        );

        // ── 2. Options Likert communes ────────────────────────────────────────
        $likert = [
            'min_label' => 'Jamais',
            'max_label' => 'Toujours',
            'max'       => 4,
            'labels'    => ['Jamais', 'Rarement', 'Souvent', 'Toujours'],
        ];

        // ── 3. Construire les sections par dimension ──────────────────────────
        $dimensions = Exercises::dimensions();
        $questions  = Exercises::questions();

        // Indexer les questions par dimension
        $byDim = [];
        foreach ($questions as $q) {
            $byDim[$q['dim']][] = $q;
        }

        $sectionOrder = 0;

        foreach ($dimensions as $dimKey => $dimInfo) {
            $sectionOrder++;

            $section = TestSection::updateOrCreate(
                [
                    'test_id' => $test->id,
                    'order'   => $sectionOrder,
                ],
                [
                    'title'       => $dimInfo['label'],
                    'description' => $dimInfo['description'],
                ]
            );

            $qOrder = 0;
            foreach ($byDim[$dimKey] ?? [] as $q) {
                $qOrder++;

                TestQuestion::updateOrCreate(
                    [
                        'section_id' => $section->id,
                        'order'      => $qOrder,
                    ],
                    [
                        'type'     => 'scale',
                        'prompt'   => $q['text'],
                        'options'  => $likert,
                        'scoring'  => [
                            'idx'       => $q['idx'],
                            'dim'       => $q['dim'],
                            'max'       => 4,
                            'min'       => 1,
                        ],
                        'required' => true,
                    ]
                );
            }
        }

        $this->command?->info(
            'PraxiFlow seeder OK — test slug: praxiflow-productivite, '
            . count($questions) . ' questions, '
            . count($dimensions) . ' dimensions.'
        );
    }
}
