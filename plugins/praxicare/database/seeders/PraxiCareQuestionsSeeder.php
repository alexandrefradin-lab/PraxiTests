<?php

namespace Praxis\Plugins\PraxiCare\Database\Seeders;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSection;
use Illuminate\Database\Seeder;
use Praxis\Plugins\PraxiCare\Data\Questions;

class PraxiCareQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        $test = Test::updateOrCreate(
            ['slug' => 'praxicare'],
            [
                'name'              => "La Sentinelle Intérieure — Mesure de la souffrance au travail",
                'description'       => "Ce que ce test mesure : ton niveau de souffrance au travail. D'un côté la pression que tu subis (exigences du poste, marge de manœuvre, soutien — modèle Karasek), de l'autre les signes d'épuisement professionnel (fatigue émotionnelle, mise à distance, accomplissement — modèle MBI). 48 questions, 10 min. Outil de prise de conscience, pas un diagnostic médical.",
                'type'              => 'questionnaire',
                'scoring_engine'    => 'praxicare-karasek-mbi',
                'estimated_minutes' => 10,
                'published'         => true,
                'public'            => false,
            ]
        );

        $karasekScale = [
            'min_label' => "Pas du tout d'accord",
            'max_label' => "Tout à fait d'accord",
            'max'       => 4,
        ];
        $mbiScale = [
            'min_label' => 'Jamais',
            'max_label' => 'Toujours',
            'max'       => 4,
        ];

        $order = 0;
        foreach (Questions::sections() as $key => $sec) {
            $section = TestSection::updateOrCreate(
                ['test_id' => $test->id, 'order' => ++$order],
                [
                    'title'       => $sec['label'],
                    'description' => $sec['description'],
                ]
            );

            $qOrder = 0;
            $isMbi = str_starts_with($sec['scale'], 'mbi');
            foreach ($sec['questions'] as $q) {
                TestQuestion::updateOrCreate(
                    ['section_id' => $section->id, 'order' => ++$qOrder],
                    [
                        'type'    => 'scale',
                        'prompt'  => $q['texte'],
                        'options' => $isMbi ? $mbiScale : $karasekScale,
                        'scoring' => [
                            'key'        => $q['key'],
                            'sub_test'   => $key,
                            'inverse'    => $q['inverse'] ?? false,
                            'optional'   => $q['optional'] ?? null,
                            'max'        => $isMbi ? 3 : 4,
                            'min'        => $isMbi ? 0 : 1,
                        ],
                        'required' => true,
                    ]
                );
            }
        }
    }
}