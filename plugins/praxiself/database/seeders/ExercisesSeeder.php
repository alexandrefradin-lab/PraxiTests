<?php

namespace Praxis\Plugins\PraxiSelf\Database\Seeders;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSection;
use Illuminate\Database\Seeder;
use Praxis\Plugins\PraxiSelf\Data\Exercises;

/**
 * Seeder PraxiSelf — Affirmation de soi.
 *
 * Crée ou met à jour dans la base de données :
 *   - Le test "praxiself-affirmation"
 *   - 4 sections (une par catégorie d'exercice)
 *   - 20 questions (une par exercice), avec échelle Likert 1–5
 *
 * Question Likert : "Dans quelle mesure te sens-tu à l'aise avec la situation
 * décrite ?" — 1 = Pas du tout, 5 = Tout à fait.
 *
 * Le champ `scoring` de chaque question contient les informations nécessaires
 * au moteur de scoring : dimension, weight, max.
 */
class ExercisesSeeder extends Seeder
{
    private const SECTION_LABELS = [
        'confiance'    => ['title' => 'Estime de soi & confiance',       'order' => 1],
        'assertivite'  => ['title' => 'Assertivité comportementale',      'order' => 2],
        'communication'=> ['title' => 'Expression des besoins (CNV)',     'order' => 3],
        'roleplay'     => ['title' => 'Résilience & jeux de rôle',        'order' => 4],
    ];

    public function run(): void
    {
        // ── 1. Créer / mettre à jour le test ─────────────────────────────
        $test = Test::updateOrCreate(
            ['slug' => 'praxiself-affirmation'],
            [
                'name'              => 'PraxiSelf — Affirmation de soi',
                'description'       => 'Évalue ton assertivité sur 5 dimensions et reçois des exercices personnalisés basés sur la CNV, la théorie de l\'auto-efficacité et les techniques d\'Alberti & Emmons.',
                'type'              => 'mini-app',
                'scoring_engine'    => 'praxiself-scoring',
                'estimated_minutes' => 7,
                'published'         => true,
                'public'            => false,
            ]
        );

        // ── 2. Grouper les exercices par catégorie ────────────────────────
        $byCategory = collect(Exercises::all())->groupBy('category');

        foreach (self::SECTION_LABELS as $category => $sectionMeta) {
            $exercises = $byCategory->get($category, collect());
            if ($exercises->isEmpty()) {
                continue;
            }

            // ── 3. Créer / mettre à jour la section ───────────────────────
            $section = TestSection::updateOrCreate(
                [
                    'test_id' => $test->id,
                    'order'   => $sectionMeta['order'],
                ],
                [
                    'title'           => $sectionMeta['title'],
                    'description'     => null,
                    'narrative_intro' => null,
                ]
            );

            // ── 4. Créer / mettre à jour chaque question ──────────────────
            $qOrder = 0;
            foreach ($exercises as $exercise) {
                $scoringMeta = $exercise['scoring'];

                TestQuestion::updateOrCreate(
                    [
                        'section_id' => $section->id,
                        'order'      => ++$qOrder,
                    ],
                    [
                        'type'   => 'likert',
                        'prompt' => $this->buildPrompt($exercise),
                        'options' => [
                            ['value' => 1, 'label' => 'Pas du tout à l\'aise'],
                            ['value' => 2, 'label' => 'Plutôt pas à l\'aise'],
                            ['value' => 3, 'label' => 'Moyennement à l\'aise'],
                            ['value' => 4, 'label' => 'Plutôt à l\'aise'],
                            ['value' => 5, 'label' => 'Tout à fait à l\'aise'],
                        ],
                        'scoring' => [
                            'exercise_id' => $exercise['id'],
                            'dimension'   => $scoringMeta['dimension'],
                            'weight'      => $scoringMeta['weight'],
                            'max'         => 5,
                            'values'      => ['1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5],
                        ],
                        'meta' => [
                            'exercise_title'    => $exercise['title'],
                            'scientific_basis'  => $exercise['scientific_basis'],
                            'duration_minutes'  => $exercise['duration_minutes'],
                            'difficulty'        => $exercise['difficulty'],
                            'instructions'      => $exercise['instructions'],
                        ],
                        'required' => true,
                    ]
                );
            }
        }
    }

    /**
     * Construit le texte de la question Likert à partir de l'exercice.
     * La question invite l'utilisateur à évaluer son niveau de confort
     * face à la situation décrite dans l'exercice.
     */
    private function buildPrompt(array $exercise): string
    {
        // Extrait la 1ère ligne des instructions comme contexte situationnel
        $firstLine = strtok($exercise['instructions'], "\n");
        $firstLine = ltrim($firstLine, "Étape 1 — Contexte de l'exercice : ");

        return sprintf(
            '%s — Dans quelle mesure te sens-tu à l\'aise pour gérer ce type de situation ?',
            $exercise['title']
        );
    }
}
