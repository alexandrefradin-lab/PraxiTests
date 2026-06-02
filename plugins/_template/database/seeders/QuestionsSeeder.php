<?php

namespace Praxis\Plugins\PLUGIN_CLASS\Database\Seeders;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSection;
use Illuminate\Database\Seeder;
use Praxis\Plugins\PLUGIN_CLASS\Data\Questions;

class QuestionsSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Créer ou mettre à jour le test ─────────────────────────────
        $test = Test::updateOrCreate(
            ['slug' => 'PLUGIN_SLUG'],
            [
                'name'              => 'PLUGIN_NAME',
                'description'       => 'Description du test visible par le candidat avant de démarrer.',
                'type'              => 'questionnaire',
                'scoring_engine'    => 'PLUGIN_SLUG-scoring',
                'estimated_minutes' => 10,       // ← ajuster
                'published'         => true,
                'public'            => false,    // true = accessible sans invitation
            ]
        );

        // ── 2. Grouper les questions par section ──────────────────────────
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
