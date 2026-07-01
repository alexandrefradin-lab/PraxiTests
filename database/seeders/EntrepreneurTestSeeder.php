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
                'description' => "Un auto-positionnement en 28 énoncés sur 7 compétences clés de l'entrepreneur : proactivité, prise de risque, repérage d'opportunités, résilience, leadership, sens commercial et autonomie.",
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

        // 4 énoncés par compétence (clé = dimension du scoring).
        $competences = [
            'proactivite' => [
                "Je prends les devants au lieu d'attendre qu'on me dise quoi faire.",
                "Quand je repère un problème, j'agis sans attendre qu'il s'aggrave.",
                "Je lance des projets de ma propre initiative.",
                "Je cherche activement de nouvelles occasions plutôt que de subir les événements.",
            ],
            'prise_risque' => [
                "Je suis prêt·e à engager du temps ou de l'argent sans certitude de retour.",
                "L'incertitude ne m'empêche pas de me lancer.",
                "Je préfère tenter et apprendre plutôt que de tout sécuriser à l'avance.",
                "J'accepte de sortir de ma zone de confort pour saisir une opportunité.",
            ],
            'opportunites' => [
                "Je remarque des besoins que les autres ne voient pas encore.",
                "J'imagine facilement de nouvelles idées de produits ou de services.",
                "Je transforme les contraintes en occasions de faire différemment.",
                "Je suis attentif·ve aux tendances qui pourraient créer des opportunités.",
            ],
            'resilience' => [
                "Après un échec, je rebondis rapidement.",
                "Les obstacles me motivent plus qu'ils ne me découragent.",
                "Je persévère même quand les résultats tardent à venir.",
                "Je garde mon énergie face aux difficultés répétées.",
            ],
            'leadership' => [
                "Je sais convaincre les autres de me suivre sur un projet.",
                "J'aime fédérer une équipe autour d'un objectif commun.",
                "Je prends naturellement des décisions pour le groupe.",
                "Je donne de l'élan et de la confiance à ceux qui m'entourent.",
            ],
            'sens_commercial' => [
                "Je comprends vite ce qui a de la valeur pour un client.",
                "Je pense au marché et à la demande quand j'ai une idée.",
                "Je suis à l'aise pour vendre une idée ou un produit.",
                "Je cherche comment rendre une idée rentable, pas seulement séduisante.",
            ],
            'autonomie' => [
                "Je me sens capable de mener un projet du début à la fin.",
                "Je décide seul·e sans avoir besoin d'une validation constante.",
                "Je m'organise et je m'auto-discipline sans supervision.",
                "Je crois en ma capacité à réussir ce que j'entreprends.",
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
