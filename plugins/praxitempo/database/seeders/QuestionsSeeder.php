<?php

namespace Praxis\Plugins\PraxiTempo\Database\Seeders;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSection;
use Illuminate\Database\Seeder;
use Praxis\Plugins\PraxiTempo\Data\Questions;

class QuestionsSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Créer ou mettre à jour le test ─────────────────────────────
        $test = Test::updateOrCreate(
            ['slug' => 'praxitempo'],
            [
                'name'              => 'Maître du Temps — Gestion du temps',
                'description'       => "Ce que ce test mesure : ta façon de gérer ton temps au quotidien. 4 dimensions évaluées — priorisation, planification, focus & interruptions, équilibre & énergie. 16 affirmations, échelle de 1 à 5. Format conversationnel, ~4 minutes.",
                'type'              => 'questionnaire',
                'scoring_engine'    => 'praxitempo-scoring',
                'estimated_minutes' => 4,
                'published'         => true,
                'public'            => false,
            ]
        );

        // ── 2. Grouper les questions par section (ordre stable) ───────────
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
