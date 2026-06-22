<?php

namespace Praxis\Plugins\PraxiZen\Database\Seeders;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSection;
use Illuminate\Database\Seeder;
use Praxis\Plugins\PraxiZen\Data\Exercises;

class ExercisesSeeder extends Seeder
{
    public function run(): void
    {
        // ── Création / mise à jour du test ───────────────────────────────────
        $test = Test::updateOrCreate(
            ['slug' => 'praxizen-stress'],
            [
                'name'              => "Le Refuge Intérieur — Évaluation & gestion du stress",
                'description'       => "Ce que ce test mesure : ta gestion du stress professionnel, sur 5 dimensions — régulation émotionnelle, résilience, gestion des tensions physiques, pleine conscience et recadrage cognitif. 20 questions Likert, puis un programme d'exercices personnalisé.",
                'type'              => 'questionnaire',
                'scoring_engine'    => 'praxizen-stress',
                'estimated_minutes' => 8,
                'published'         => false,
                'public'            => false,
            ]
        );

        // ── Sections (1 par dimension, 4 questions chacune) ─────────────────
        $dimensions = Exercises::dimensions();
        $questions  = Exercises::questions();

        // Grouper les questions par dimension
        $byDimension = collect($questions)->groupBy(fn ($q) => $q['scoring']['dimension']);

        $order = 0;
        foreach ($dimensions as $dimKey => $dimMeta) {
            $dimQuestions = $byDimension->get($dimKey, collect());

            $section = TestSection::updateOrCreate(
                ['test_id' => $test->id, 'order' => ++$order],
                [
                    'title'           => $dimMeta['label'],
                    'description'     => $dimMeta['description'],
                    'narrative_intro' => null,
                ]
            );

            $qOrder = 0;
            foreach ($dimQuestions as $q) {
                TestQuestion::updateOrCreate(
                    ['section_id' => $section->id, 'order' => ++$qOrder],
                    [
                        'type'     => 'single',
                        'prompt'   => $q['prompt'],
                        'options'  => $q['options'],
                        'scoring'  => $q['scoring'],
                        'required' => true,
                    ]
                );
            }
        }
    }
}
