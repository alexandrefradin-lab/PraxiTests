<?php

namespace Praxis\Plugins\PraxiEmo\Database\Seeders;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSection;
use Illuminate\Database\Seeder;
use Praxis\Plugins\PraxiEmo\Data\Dimensions;

class PraxiEmoQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        $test = Test::updateOrCreate(
            ['slug' => 'praxiemo'],
            [
                'name'              => "La Boussole des Émotions — Intelligence émotionnelle",
                'description'       => "Ce que ce test mesure : ton intelligence émotionnelle, c'est-à-dire ta capacité à reconnaître, comprendre et réguler tes émotions, et à t'en servir dans tes relations et ta posture de leader. 16 dimensions en 4 familles : conscience de soi, régulation, relations & communication, leadership émotionnel.",
                'type'              => 'questionnaire',
                'scoring_engine'    => 'praxiemo-eqi',
                'estimated_minutes' => 18,
                'published'         => true,
                'public'            => false,
            ]
        );

        $likert = [
            'min_label' => 'Jamais',
            'max_label' => 'Toujours',
            'max'       => 4,
            'labels'    => ['Jamais', 'Rarement', 'Souvent', 'Toujours'],
        ];

        // Une section par famille (5 sections — la 5e = désirabilité sociale).
        $families = Dimensions::families() + [5 => ['label' => 'Vérification', 'icon' => 'check']];
        $byFamily = [];
        foreach (Dimensions::questions() as $q) {
            $byFamily[$q['f']][] = $q;
        }

        $order = 0;
        foreach ($byFamily as $famId => $questions) {
            $section = TestSection::updateOrCreate(
                ['test_id' => $test->id, 'order' => ++$order],
                [
                    'title' => $families[$famId]['label'] ?? "Famille {$famId}",
                    'description' => $famId === 5
                        ? 'Quelques affirmations courantes pour vérifier la fiabilité de tes réponses.'
                        : null,
                ]
            );

            $qOrder = 0;
            foreach ($questions as $q) {
                TestQuestion::updateOrCreate(
                    ['section_id' => $section->id, 'order' => ++$qOrder],
                    [
                        'type'    => 'scale',
                        'prompt'  => $q['text'],
                        'options' => $likert,
                        'scoring' => [
                            'idx'       => $q['idx'],
                            'dim'       => $q['dim'],
                            'famille'   => $q['f'],
                            'max'       => 4,
                            'min'       => 1,
                        ],
                        'required' => true,
                    ]
                );
            }
        }
    }
}
