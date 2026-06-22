<?php

namespace Praxis\Plugins\PraxiSpeak\Database\Seeders;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSection;
use Illuminate\Database\Seeder;
use Praxis\Plugins\PraxiSpeak\Data\Exercises;

class ExercisesSeeder extends Seeder
{
    public function run(): void
    {
        $test = Test::updateOrCreate(
            ['slug' => 'praxispeak'],
            [
                'name'              => "La Voix du Héros — Prise de parole en public",
                'description'       => "Ce que ce test mesure : ton aisance en prise de parole en public, sur 5 dimensions — gestion du trac, préparation mentale, présence physique, structure du discours et impact vocal. 20 exercices guidés (2-5 min) pour gagner en confiance.",
                'type'              => 'exercises',
                'scoring_engine'    => 'praxispeak-scoring',
                'estimated_minutes' => 8,
                'published'         => false,
                'public'            => false,
            ]
        );

        $dimensions  = Exercises::dimensions();
        $allExercises = Exercises::exercises();

        // Regroupement des exercices par catégorie (= section)
        $byCategory = [];
        foreach ($allExercises as $ex) {
            $byCategory[$ex['category']][] = $ex;
        }

        $sectionOrder = 0;
        foreach ($dimensions as $dimId => $dimInfo) {
            if (empty($byCategory[$dimId])) {
                continue;
            }

            $section = TestSection::updateOrCreate(
                ['test_id' => $test->id, 'order' => ++$sectionOrder],
                [
                    'title'       => $dimInfo['label'],
                    'description' => $dimInfo['description'],
                ]
            );

            $questionOrder = 0;
            foreach ($byCategory[$dimId] as $ex) {
                TestQuestion::updateOrCreate(
                    ['section_id' => $section->id, 'order' => ++$questionOrder],
                    [
                        // 'single' est rendu par le frontend (AttemptPlay.vue)
                        // et émet directement la value de l'option choisie
                        // (0 est donc soumissible). Le moteur interprète la
                        // value en booléen fait/pas fait (filter_var).
                        'type'    => 'single',
                        'prompt'  => $ex['title'] . ' — Te sens-tu à l\'aise avec cet exercice ?',
                        'options' => [
                            ['value' => 1, 'label' => 'Oui, à l\'aise'],
                            ['value' => 0, 'label' => 'Pas encore'],
                        ],
                        'meta' => [
                            'duration_minutes' => $ex['duration_minutes'],
                            'difficulty'       => $ex['difficulty'],
                            'instructions'     => $ex['instructions'],
                            'scientific_basis' => $ex['scientific_basis'],
                        ],
                        'scoring' => [
                            'exercise_id' => $ex['id'],
                            'dimension'   => $ex['scoring']['dimension'],
                            'points'      => $ex['scoring']['points'],
                        ],
                        'required' => true,
                    ]
                );
            }
        }
    }
}
