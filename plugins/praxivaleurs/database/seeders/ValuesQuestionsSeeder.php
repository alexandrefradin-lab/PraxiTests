<?php

namespace Praxis\Plugins\PraxiValeurs\Database\Seeders;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSection;
use Illuminate\Database\Seeder;
use Praxis\Plugins\PraxiValeurs\Data\Values;

class ValuesQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        $test = Test::updateOrCreate(
            ['slug' => 'praxivaleurs'],
            [
                'name'              => 'Test des Valeurs Professionnelles',
                'description'       => "40 valeurs à évaluer sur une échelle de 1 à 6. Découvre tes 5 valeurs prioritaires.",
                'type'              => 'questionnaire',
                'scoring_engine'    => 'praxivaleurs-schwartz',
                'estimated_minutes' => 8,
                'published'         => true,
                'public'            => false,
            ]
        );

        // Une seule section : 40 valeurs Likert.
        $section = TestSection::updateOrCreate(
            ['test_id' => $test->id, 'order' => 1],
            [
                'title'       => 'Tes valeurs',
                'description' => "Quelle importance accordes-tu à chacune de ces valeurs dans ta vie professionnelle ?",
            ]
        );

        $likertOptions = [
            'min_label' => 'Aucune importance',
            'max_label' => 'Importance suprême',
            'max'       => 6,
        ];

        foreach (Values::questions() as $i => $q) {
            TestQuestion::updateOrCreate(
                ['section_id' => $section->id, 'order' => $i + 1],
                [
                    'type'    => 'scale',
                    'prompt'  => $q['texte'],
                    'options' => $likertOptions,
                    'scoring' => [
                        'dimension' => $q['dim'],
                        'qid'       => $q['id'],
                        'max'       => 6,
                    ],
                    'required' => true,
                ]
            );
        }
    }
}
