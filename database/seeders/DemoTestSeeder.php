<?php

namespace Database\Seeders;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSection;
use Illuminate\Database\Seeder;

/**
 * Orientation Express — auto-positionnement RIASEC.
 *
 * Instrument renforcé : plusieurs items par dimension (fini le « 1 question =
 * 100 % »). Chaque type RIASEC est mesuré par 4 énoncés Likert 1–5, agrégés par
 * DefaultScoringEngine puis normalisés sur [min..max] réel (4→0 %, 20→100 %) :
 * la mesure discrimine enfin les degrés au lieu de saturer.
 *
 * Deux méta-échelles transverses (Conscience de soi, Ouverture au changement)
 * complètent le profil, chacune sur 3 items.
 *
 * Idempotent : upsert par (section_id, order). Les anciennes questions (1 item
 * par dimension) sont écrasées en place ; le nombre d'items ne fait qu'augmenter,
 * donc aucune question orpheline à supprimer (les résultats déjà calculés restent
 * dans test_results, indépendants des questions).
 */
class DemoTestSeeder extends Seeder
{
    public function run(): void
    {
        $test = Test::updateOrCreate(
            ['slug' => 'orientation-express'],
            [
                'name'        => 'Orientation Express',
                'description' => "Un auto-positionnement rapide en 30 énoncés pour cartographier tes affinités professionnelles (modèle RIASEC de Holland).",
                'type'        => 'questionnaire',
                'scoring_engine' => 'default',
                'estimated_minutes' => 10,
                'published'   => true,
                'public'      => false,
            ]
        );

        // ── SECTION 1 — RIASEC : 6 dimensions × 4 items Likert, entremêlés ──────
        $s1 = TestSection::updateOrCreate(
            ['test_id' => $test->id, 'order' => 1],
            ['title' => 'Tes terrains de jeu', 'narrative_intro' => "Positionne-toi sur chaque énoncé. Il n'y a pas de bonne réponse : cherche ce qui te ressemble vraiment."]
        );

        // 4 énoncés par type RIASEC (clé = dimension du scoring).
        $riasec = [
            'realistic' => [
                "J'aime réparer, bricoler ou fabriquer des choses de mes mains.",
                "Manipuler des outils, des machines ou des matériaux me plaît.",
                "Je préfère les tâches concrètes et tangibles aux idées abstraites.",
                "Travailler en extérieur ou sur le terrain m'attire.",
            ],
            'investigative' => [
                "J'aime comprendre en profondeur comment les choses fonctionnent.",
                "Analyser des données ou résoudre des problèmes complexes me stimule.",
                "Je suis curieux·se et j'aime enquêter avant de conclure.",
                "Explorer des questions théoriques ou scientifiques me passionne.",
            ],
            'artistic' => [
                "J'aime créer, imaginer ou inventer de nouvelles choses.",
                "M'exprimer (écriture, musique, design, arts…) compte pour moi.",
                "Je préfère les environnements libres où l'originalité est valorisée.",
                "Sortir des cadres établis me stimule plus que suivre des règles.",
            ],
            'social' => [
                "Aider les autres à progresser me procure de la satisfaction.",
                "J'aime écouter, conseiller et accompagner les gens.",
                "Être en contact avec les autres est essentiel dans mon travail.",
                "Transmettre, former ou enseigner me motive.",
            ],
            'enterprising' => [
                "J'aime convaincre, négocier et défendre des idées.",
                "Prendre des initiatives et des décisions me met en énergie.",
                "Diriger un projet ou une équipe m'attire.",
                "Relever des défis ambitieux me motive.",
            ],
            'conventional' => [
                "J'aime organiser, classer et structurer l'information.",
                "Suivre des procédures claires et précises me convient bien.",
                "Je suis à l'aise avec les chiffres, les tableaux et le suivi rigoureux.",
                "Un cadre ordonné me rend plus efficace qu'un environnement changeant.",
            ],
        ];

        // Entremêlement : round-robin (1 item par dimension à chaque tour) pour
        // limiter le straight-lining. Ordre = 1..24.
        $order = 0;
        $rounds = max(array_map('count', $riasec)); // 4
        for ($r = 0; $r < $rounds; $r++) {
            foreach ($riasec as $dim => $prompts) {
                if (!isset($prompts[$r])) {
                    continue;
                }
                $order++;
                TestQuestion::updateOrCreate(
                    ['section_id' => $s1->id, 'order' => $order],
                    [
                        'type'    => 'scale',
                        'prompt'  => $prompts[$r],
                        'scoring' => ['dimension' => $dim, 'max' => 5],
                        'options' => ['min_label' => 'Pas du tout', 'max_label' => 'Tout à fait', 'max' => 5],
                        'required'=> true,
                    ]
                );
            }
        }

        // ── SECTION 2 — Toi, en mouvement : méta-échelles + réflexion libre ─────
        $s2 = TestSection::updateOrCreate(
            ['test_id' => $test->id, 'order' => 2],
            ['title' => 'Toi, en mouvement', 'narrative_intro' => "Quelques repères sur ta connaissance de toi et ton rapport au changement."]
        );

        $meta = [
            'self_awareness' => [
                "Je connais bien mes forces et mes limites.",
                "Je sais reconnaître ce qui me motive vraiment.",
                "Je prends du recul sur mes réactions et mes émotions.",
            ],
            'change_readiness' => [
                "Je suis prêt·e à faire évoluer significativement ma trajectoire.",
                "Sortir de ma zone de confort m'attire plus qu'il ne m'inquiète.",
                "Je m'adapte facilement aux situations nouvelles ou imprévues.",
            ],
        ];

        $order = 0;
        $rounds = max(array_map('count', $meta)); // 3
        for ($r = 0; $r < $rounds; $r++) {
            foreach ($meta as $dim => $prompts) {
                if (!isset($prompts[$r])) {
                    continue;
                }
                $order++;
                TestQuestion::updateOrCreate(
                    ['section_id' => $s2->id, 'order' => $order],
                    [
                        'type'    => 'scale',
                        'prompt'  => $prompts[$r],
                        'scoring' => ['dimension' => $dim, 'max' => 5],
                        'options' => ['min_label' => 'Pas du tout', 'max_label' => 'Tout à fait', 'max' => 5],
                        'required'=> true,
                    ]
                );
            }
        }

        // Réflexion libre (non scorée) en clôture.
        $order++;
        TestQuestion::updateOrCreate(
            ['section_id' => $s2->id, 'order' => $order],
            [
                'type'     => 'text',
                'prompt'   => "Décris en 2-3 phrases un moment professionnel où tu t'es senti·e particulièrement vivant·e.",
                'scoring'  => null,
                'options'  => null,
                'required' => true,
            ]
        );
    }
}
