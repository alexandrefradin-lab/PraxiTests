<?php

namespace Praxis\Plugins\PraxiFocus\Database\Seeders;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSection;
use Illuminate\Database\Seeder;
use Praxis\Plugins\PraxiFocus\Data\Questions;

class QuestionsSeeder extends Seeder
{
    public function run(): void
    {
        $test = Test::updateOrCreate(
            ['slug' => 'praxifocus'],
            [
                'name'              => 'La Boussole de l\'Attention — Repères TDAH (ASRS-v1.1)',
                'description'       => "Auto-questionnaire de repérage des symptômes d'attention et d'hyperactivité chez l'adulte (échelle ASRS-v1.1 de l'OMS, 18 items). ⚠️ Outil de REPÉRAGE uniquement : il ne constitue ni un diagnostic ni un avis médical. Un résultat élevé n'établit pas un TDAH. Seul un professionnel de santé peut poser un diagnostic — vérifie toujours tes résultats avec un professionnel.",
                'type'              => 'questionnaire',
                'scoring_engine'    => 'praxifocus-asrs',
                'estimated_minutes' => 7,
                'published'         => true,
                'public'            => false,
            ]
        );

        $bySection = collect(Questions::all())->groupBy('section');

        $sectionOrder = 0;
        foreach ($bySection as $sectionTitle => $questions) {
            $section = TestSection::updateOrCreate(
                ['test_id' => $test->id, 'order' => ++$sectionOrder],
                ['title' => $sectionTitle]
            );

            $questionOrder = 0;
            foreach ($questions as $q) {
                TestQuestion::updateOrCreate(
                    ['section_id' => $section->id, 'order' => ++$questionOrder],
                    [
                        'type'    => $q['type'],
                        'prompt'  => $q['prompt'],
                        'helper'  => $q['helper'] ?? null,
                        'options' => $q['options'] ?? null,
                        'scoring' => $q['scoring'] ?? null,
                    ]
                );
            }
        }
    }
}
