<?php

namespace Database\Seeders;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSection;
use Illuminate\Database\Seeder;

/**
 * L'Étoffe du Bâtisseur — évaluation des compétences entrepreneuriales.
 *
 * Auto-positionnement sur 7 compétences clés de l'entrepreneur (inspiré des
 * cadres type EntreComp et de la recherche sur l'orientation entrepreneuriale) :
 * chaque compétence est mesurée par 4 énoncés Likert 1–5, agrégés par le moteur
 * `default` (normalisation min-max : le minimum réel s'affiche bien à 0 %).
 *
 * Idempotent : upsert par (section_id, order).
 */
class EntrepreneurTestSeeder extends Seeder
{
    public function run(): void
    {
        $test = Test::updateOrCreate(
            ['slug' => 'competences-entrepreneuriales'],
            [
                'name'        => "L'Étoffe du Bâtisseur — Compétences entrepreneuriales",
                'description' => "Un auto-positionnement en 32 énoncés sur 8 compétences clés de l'entrepreneur (référentiel EntreComp) : repérage d'opportunités, créativité & vision, prise d'initiative, tolérance au risque, persévérance, mobilisation, auto-efficacité et gestion des ressources.",
                'type'        => 'questionnaire',
                'scoring_engine' => 'default',
                'estimated_minutes' => 10,
                'published'   => true,
                'public'      => false,
            ]
        );

        $section = TestSection::updateOrCreate(
            ['test_id' => $test->id, 'order' => 1],
            [
                'title'           => 'Ton profil d\'entrepreneur',
                'narrative_intro' => "Positionne-toi sur chaque énoncé, sans chercher la « bonne » réponse : cherche ce qui te ressemble vraiment aujourd'hui.",
            ]
        );

        // 4 énoncés par compétence (clé = dimension du scoring). Formulations
        // adoucies (moins de désirabilité sociale, pas de double-barrel ni
        // d'extrêmes) — validation psychométrique via le référentiel EntreComp.
        $competences = [
            'opportunites' => [
                "Je repère des besoins que d'autres n'ont pas encore vus.",
                "Dans une situation banale, il m'arrive de voir une occasion à saisir.",
                "Je m'intéresse aux évolutions du marché et aux nouvelles tendances.",
                "Face à un problème courant, j'imagine des façons d'y répondre autrement.",
            ],
            'vision' => [
                "J'aime imaginer ce qu'un projet pourrait devenir à long terme.",
                "Je génère assez facilement des idées nouvelles.",
                "Je me projette volontiers dans un objectif ambitieux.",
                "Je transforme une idée floue en un projet plus concret.",
            ],
            'proactivite' => [
                "Je préfère agir plutôt qu'attendre que les choses se décantent.",
                "Quand un sujet me tient à cœur, je lance moi-même les premières étapes.",
                "Je vais chercher l'information ou les contacts dont j'ai besoin.",
                "Je propose des choses plutôt que d'attendre qu'on me le demande.",
            ],
            'prise_risque' => [
                "Je peux avancer sans connaître toutes les réponses à l'avance.",
                "Investir du temps ou de l'argent sans garantie ne me bloque pas.",
                "Je reste à l'aise quand une situation demeure floue un moment.",
                "Je préfère un pari qui peut beaucoup rapporter à une option sûre mais limitée.",
            ],
            'resilience' => [
                "Après un revers, je retrouve assez vite de l'élan.",
                "Je continue d'avancer même quand les résultats se font attendre.",
                "Les difficultés répétées entament peu ma détermination.",
                "Quand je crois en un projet, j'ai du mal à l'abandonner.",
            ],
            'leadership' => [
                "J'arrive à donner envie aux autres de participer à un projet.",
                "Je fédère assez facilement des personnes autour d'un objectif.",
                "On me suit plutôt naturellement quand je propose une direction.",
                "Je sais aller chercher les bonnes personnes pour m'entourer.",
            ],
            'auto_efficacite' => [
                "Je me sens capable de mener un projet du début à la fin.",
                "Face à une tâche nouvelle, je pars du principe que je vais y arriver.",
                "Je me fais confiance pour les décisions importantes d'un projet.",
                "Même sans expérience préalable, je m'estime capable d'apprendre ce qu'il faut.",
            ],
            'gestion' => [
                "J'organise mes actions en étapes concrètes pour atteindre un objectif.",
                "Je garde un œil sur les moyens (temps, argent) que demande un projet.",
                "Je trouve des solutions pour faire avec des ressources limitées.",
                "J'anticipe ce dont j'aurai besoin avant de me lancer.",
            ],
        ];

        // Entremêlement (round-robin : 1 énoncé par compétence à chaque tour)
        // pour limiter le straight-lining. Ordre = 1..28.
        $order  = 0;
        $rounds = max(array_map('count', $competences)); // 4
        for ($r = 0; $r < $rounds; $r++) {
            foreach ($competences as $dim => $prompts) {
                if (! isset($prompts[$r])) {
                    continue;
                }
                $order++;
                TestQuestion::updateOrCreate(
                    ['section_id' => $section->id, 'order' => $order],
                    [
                        'type'     => 'scale',
                        'prompt'   => $prompts[$r],
                        'scoring'  => ['dimension' => $dim, 'max' => 5],
                        'options'  => ['min_label' => 'Pas du tout', 'max_label' => 'Tout à fait', 'max' => 5],
                        'required' => true,
                    ]
                );
            }
        }
    }
}
