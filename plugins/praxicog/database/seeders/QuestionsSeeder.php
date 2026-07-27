<?php

namespace Praxis\Plugins\PraxiCog\Database\Seeders;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSection;
use Illuminate\Database\Seeder;
use Praxis\Plugins\PraxiCog\Data\Questions;

class QuestionsSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Test ───────────────────────────────────────────────────────
        $test = Test::updateOrCreate(
            ['slug' => 'praxicog'],
            [
                'name'              => 'PraxiCog — Raisonnement & aptitude cognitive',
                'description'       => "Un parcours chronométré de 40 énigmes réparties sur quatre familles de "
                                     . "raisonnement : logique, verbal, numérique et spatial. À l'arrivée, un profil "
                                     . "de vos aptitudes et vos points d'appui. Test indicatif — ce n'est pas une "
                                     . "mesure de QI.",
                'type'              => 'questionnaire',
                'scoring_engine'    => 'praxicog-scoring',
                'estimated_minutes' => 25,
                'published'         => true,
                'public'            => false,
            ]
        );

        // ── 2. Questions par section ──────────────────────────────────────
        // On préserve l'ordre déclaré dans Questions::all() (sections dans
        // l'ordre logique → verbal → numérique → spatial).
        $bySection = [];
        foreach (Questions::all() as $q) {
            $bySection[$q['section']][] = $q;
        }

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
                        'type'     => $q['type'],
                        'prompt'   => $q['prompt'],
                        'helper'   => $q['helper'] ?? null,
                        'options'  => $q['options'] ?? null,
                        'scoring'  => $q['scoring'] ?? null,
                        // meta : figures SVG + time_limit + shuffle (mélange des
                        // options au runtime → pas de clé de réponses positionnelle).
                        'meta'     => array_merge(['shuffle' => true], $q['meta'] ?? []),
                        'required' => true,
                    ]
                );
            }
        }
    }
}
