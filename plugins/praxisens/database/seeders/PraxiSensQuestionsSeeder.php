<?php

namespace Praxis\Plugins\PraxiSens\Database\Seeders;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSection;
use Illuminate\Database\Seeder;
use Praxis\Plugins\PraxiSens\Data\Questions;

class PraxiSensQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Créer ou mettre à jour le test ──
        $test = Test::updateOrCreate(
            ['slug' => 'praxisens'],
            [
                'name'              => "Le Radar des Sens — Hypersensibilité",
                'description'       => "Ce que ce test mesure : votre sensibilité de traitement sensoriel — sur-stimulation, seuil sensoriel, profondeur esthétique et sensibilité émotionnelle. 30 affirmations sur une échelle d'accord, 4 sous-dimensions et une échelle de validité, restitution par profil. Inspiré du modèle d'Elaine Aron (Sensory Processing Sensitivity).",
                'type'              => 'questionnaire',
                'scoring_engine'    => 'praxisens-sps',
                'estimated_minutes' => 8,
                'published'         => true,
                'public'            => false,
            ]
        );

        // ── 2. Grouper les questions par section (sous-dimension) ──
        $bySection = collect(Questions::all())->groupBy('section');

        $sectionOrder = 0;
        foreach ($bySection as $sectionTitle => $questions) {
            $section = TestSection::updateOrCreate(
                ['test_id' => $test->id, 'order' => ++$sectionOrder],
                [
                    'title'       => $sectionTitle,
                    // Section « Vérification » = items de contrôle (désirabilité sociale).
                    'description' => $sectionTitle === 'Vérification'
                        ? 'Quelques affirmations courantes pour vérifier la fiabilité de vos réponses.'
                        : null,
                ]
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
