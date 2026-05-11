<?php

namespace Praxis\Plugins\PraxiMum\Database\Seeders;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSection;
use Illuminate\Database\Seeder;
use Praxis\Plugins\PraxiMum\Data\Catalog;

class PraxiMumQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        $test = Test::updateOrCreate(
            ['slug' => 'praximum'],
            [
                'name'              => 'PraxiMum — Test Big Five',
                'description'       => "Cartographie ta personnalité en 5 dimensions OCEAN et 30 facettes. 128 questions Likert 4.",
                'type'              => 'questionnaire',
                'scoring_engine'    => 'praximum-bigfive',
                'estimated_minutes' => 25,
                'published'         => true,
                'public'            => false,
            ]
        );

        $likert = [
            'min_label' => 'Fortement en désaccord',
            'max_label' => "Fortement d'accord",
            'max'       => 4,
            'labels'    => ['Fortement en désaccord', 'Plutôt en désaccord', "Plutôt d'accord", "Fortement d'accord"],
        ];

        // Une section par groupe de 8 questions (16 sections — UX rythmée).
        $chunks = array_chunk(Catalog::questions(), 8);
        $order = 0;
        foreach ($chunks as $chunkIdx => $chunk) {
            $section = TestSection::updateOrCreate(
                ['test_id' => $test->id, 'order' => ++$order],
                [
                    'title' => 'Étape ' . ($chunkIdx + 1) . ' / ' . count($chunks),
                ]
            );

            $qOrder = 0;
            foreach ($chunk as $q) {
                TestQuestion::updateOrCreate(
                    ['section_id' => $section->id, 'order' => ++$qOrder],
                    [
                        'type'    => 'scale',
                        'prompt'  => $q['texte'],
                        'options' => $likert,
                        'scoring' => [
                            'qid'     => $q['id'],
                            'dim'     => $q['dim'],
                            'facette' => $q['facette'] ?? null,
                            'inv'     => $q['inv'],
                            'max'     => 4,
                            'min'     => 1,
                        ],
                        'required' => true,
                    ]
                );
            }
        }
    }
}
