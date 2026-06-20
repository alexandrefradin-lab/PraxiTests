<?php

namespace Praxis\Plugins\Praxis360\Database\Seeders;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSection;
use Illuminate\Database\Seeder;
use Praxis\Plugins\Praxis360\Data\Questions;

class QuestionsSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Créer ou mettre à jour le test ─────────────────────────────
        $test = Test::updateOrCreate(
            ['slug' => 'praxis360'],
            [
                'name'              => "La Constellation des Talents — Soft skills 360°",
                'description'       => "Ce que ce test mesure : tes compétences comportementales (soft skills), en auto-évaluation, sur 6 dimensions — communication, collaboration, adaptabilité, intelligence relationnelle, fiabilité, leadership. 36 affirmations sur une échelle de fréquence.",
                'type'              => 'questionnaire',
                'scoring_engine'    => 'praxis360-softskills',
                'estimated_minutes' => 8,
                'published'         => true,
                'public'            => false,
            ]
        );

        // ── 2. Grouper les questions par section (= dimension) ────────────
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
