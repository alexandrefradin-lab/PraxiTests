<?php

namespace Database\Seeders;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSection;
use Illuminate\Database\Seeder;

class DemoTestSeeder extends Seeder
{
    public function run(): void
    {
        $test = Test::updateOrCreate(
            ['slug' => 'orientation-express'],
            [
                'name'        => 'Orientation Express',
                'description' => 'Un test rapide en 12 questions pour cartographier tes affinités professionnelles.',
                'type'        => 'questionnaire',
                'scoring_engine' => 'default',
                'estimated_minutes' => 8,
                'published'   => true,
                'public'      => false,
            ]
        );

        // Section 1 — RIASEC light
        $s1 = TestSection::updateOrCreate(
            ['test_id' => $test->id, 'order' => 1],
            ['title' => 'Tes terrains de jeu', 'narrative_intro' => 'Identifions sur quels types d\'environnements tu prends de l\'élan.']
        );

        $questions1 = [
            ['type' => 'scale', 'prompt' => "J'aime résoudre des problèmes concrets et techniques.",       'scoring' => ['dimension' => 'realistic', 'max' => 5]],
            ['type' => 'scale', 'prompt' => "J'aime analyser des données et formuler des hypothèses.",   'scoring' => ['dimension' => 'investigative', 'max' => 5]],
            ['type' => 'scale', 'prompt' => "J'aime créer, imaginer, expérimenter de nouvelles formes.", 'scoring' => ['dimension' => 'artistic', 'max' => 5]],
            ['type' => 'scale', 'prompt' => "J'aime accompagner et faire grandir les autres.",            'scoring' => ['dimension' => 'social', 'max' => 5]],
            ['type' => 'scale', 'prompt' => "J'aime convaincre, prendre des décisions, entreprendre.",   'scoring' => ['dimension' => 'enterprising', 'max' => 5]],
            ['type' => 'scale', 'prompt' => "J'aime organiser, structurer et optimiser des processus.",  'scoring' => ['dimension' => 'conventional', 'max' => 5]],
        ];

        foreach ($questions1 as $i => $q) {
            TestQuestion::updateOrCreate(
                ['section_id' => $s1->id, 'order' => $i + 1],
                array_merge($q, [
                    'options' => ['min_label' => 'Pas du tout', 'max_label' => 'Tout à fait', 'max' => 5],
                ])
            );
        }

        // Section 2 — Préférences
        $s2 = TestSection::updateOrCreate(
            ['test_id' => $test->id, 'order' => 2],
            ['title' => 'Tes préférences', 'narrative_intro' => 'Tes réponses affinent ton profil.']
        );

        $questions2 = [
            [
                'type' => 'single', 'prompt' => 'Dans une équipe, tu te vois plutôt :',
                'options' => [
                    ['value' => 'leader',     'label' => 'Donner le cap'],
                    ['value' => 'expert',     'label' => 'Apporter une expertise pointue'],
                    ['value' => 'connector',  'label' => 'Faciliter les liens entre personnes'],
                    ['value' => 'doer',       'label' => 'Faire avancer concrètement'],
                ],
                'scoring' => ['dimension' => 'role', 'values' => ['leader' => 5, 'expert' => 4, 'connector' => 3, 'doer' => 2]],
            ],
            [
                'type' => 'single', 'prompt' => 'Quel cadre te fait sentir le plus en énergie ?',
                'options' => [
                    ['value' => 'startup',    'label' => "Une startup en pleine croissance"],
                    ['value' => 'corporate',  'label' => 'Un grand groupe structuré'],
                    ['value' => 'freelance',  'label' => 'En indépendant·e, libre des choix'],
                    ['value' => 'mission',    'label' => 'Une organisation à mission / impact'],
                ],
                'scoring' => ['dimension' => 'context', 'values' => ['startup' => 4, 'corporate' => 3, 'freelance' => 5, 'mission' => 4]],
            ],
            [
                'type' => 'multi', 'prompt' => 'Coche ce qui te tient à cœur (plusieurs choix possibles) :',
                'options' => [
                    ['value' => 'autonomy',  'label' => 'Autonomie'],
                    ['value' => 'impact',    'label' => 'Impact'],
                    ['value' => 'security',  'label' => 'Sécurité'],
                    ['value' => 'creation',  'label' => 'Création'],
                    ['value' => 'expertise', 'label' => 'Expertise'],
                    ['value' => 'people',    'label' => 'Humain'],
                ],
                'scoring' => ['dimension' => 'values', 'values' => ['autonomy' => 1, 'impact' => 1, 'security' => 1, 'creation' => 1, 'expertise' => 1, 'people' => 1]],
            ],
            [
                'type' => 'text', 'prompt' => 'Décris en 2-3 phrases un moment professionnel où tu t\'es senti·e particulièrement vivant·e.',
                'required' => true,
            ],
            [
                'type' => 'scale', 'prompt' => "Je suis prêt·e à changer significativement ma trajectoire dans les 12 prochains mois.",
                'scoring' => ['dimension' => 'change_readiness', 'max' => 5],
                'options' => ['min_label' => 'Pas du tout', 'max_label' => 'Tout à fait', 'max' => 5],
            ],
            [
                'type' => 'scale', 'prompt' => "Je connais bien mes forces et mes angles morts.",
                'scoring' => ['dimension' => 'self_awareness', 'max' => 5],
                'options' => ['min_label' => 'Pas du tout', 'max_label' => 'Tout à fait', 'max' => 5],
            ],
        ];

        foreach ($questions2 as $i => $q) {
            TestQuestion::updateOrCreate(
                ['section_id' => $s2->id, 'order' => $i + 1],
                $q
            );
        }
    }
}
