<?php

namespace Praxis\Plugins\PraxiMet\Database\Seeders;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSection;
use Illuminate\Database\Seeder;
use Praxis\Plugins\PraxiMet\Data\Questions;

class RiasecQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        $test = Test::updateOrCreate(
            ['slug' => 'praximet-riasec'],
            [
                'name'              => 'Test RIASEC PraxiMet',
                'description'       => "Cartographie tes affinités professionnelles selon le modèle Holland : 84 questions binaires.",
                'type'              => 'questionnaire',
                'scoring_engine'    => 'praximet-riasec',
                'estimated_minutes' => 12,
                'published'         => true,
                'public'            => false,
            ]
        );

        // Une section par couple type/sous-domaine (12 sections de 7 questions).
        $bySection = collect(Questions::all())->groupBy(fn ($q) => $q['type'] . '|' . $q['sous_domaine']);

        $order = 0;
        foreach ($bySection as $key => $questions) {
            [, $sousDomaine] = explode('|', $key);
            $type = $questions->first()['type'];
            $typeMeta = Questions::typesLabels()[$type];

            $section = TestSection::updateOrCreate(
                ['test_id' => $test->id, 'order' => ++$order],
                [
                    'title'           => $typeMeta['label'] . ' — ' . $sousDomaine,
                    'description'     => $typeMeta['desc'],
                    'narrative_intro' => null,
                ]
            );

            $qOrder = 0;
            foreach ($questions as $q) {
                TestQuestion::updateOrCreate(
                    ['section_id' => $section->id, 'order' => ++$qOrder],
                    [
                        'type'    => 'single',
                        'prompt'  => $q['texte'],
                        'options' => [
                            ['value' => 1, 'label' => 'Oui'],
                            ['value' => 0, 'label' => 'Non'],
                        ],
                        'scoring' => [
                            'rid'       => $q['id'],
                            'dimension' => $q['type'],
                            'max'       => 1,
                            'values'    => ['1' => 1, '0' => 0],
                        ],
                        'required' => true,
                    ]
                );
            }
        }
    }
}
