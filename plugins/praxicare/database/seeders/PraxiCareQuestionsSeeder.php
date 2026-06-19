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
                'name'              => 'PraxiCare — Souffrance au travail',
                'description'       => "Karasek (demandes / latitude / soutien) + MBI (épuisement, dépersonnalisation, accomplissement). 48 questions, 10 minutes. Outil de prise de conscience, pas de diagnostic médical.",
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
