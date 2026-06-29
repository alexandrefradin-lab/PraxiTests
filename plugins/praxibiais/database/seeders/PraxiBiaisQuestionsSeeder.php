<?php

namespace Praxis\Plugins\PraxiBiais\Database\Seeders;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSection;
use Illuminate\Database\Seeder;

class PraxiBiaisQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        $test = Test::updateOrCreate(
            ['slug' => 'praxibiais'],
            [
                'name'              => 'Le Cartographe Mental — Biais cognitifs professionnels',
                'description'       => "Ce que ce test mesure : les biais cognitifs qui influencent tes décisions professionnelles à ton insu. En 7 minutes, tu identifies tes freins décisionnels invisibles — les mécanismes cérébraux qui te font rester là où tu es, peser les risques asymétriquement, ou chercher des confirmations plutôt que des vérités.",
                'type'              => 'questionnaire',
                'scoring_engine'    => 'praxibiais-cognitif',
                'estimated_minutes' => 7,
                'published'         => true,
                'public'            => false,
            ]
        );

        $likert = [
            'min_label' => 'Pas du tout moi',
            'max_label' => 'Tout à fait moi',
            'max'       => 5,
            'labels'    => ['Pas du tout moi', 'Plutôt pas moi', 'Partiellement moi', 'Plutôt moi', 'Tout à fait moi'],
        ];

        $sections = [
            [
                'title'     => 'Vos réflexes face au changement',
                'questions' => [
                    // q1 → idx 0
                    ['text' => "Même quand une situation me dérange, je préfère attendre que les choses changent d'elles-mêmes.", 'biais' => 'statu_quo', 'type' => 'direct', 'idx' => 0],
                    // q2 → idx 1
                    ['text' => "L'opinion de ma famille sur mon choix de carrière influence fortement mes décisions.", 'biais' => 'conformite_familiale', 'type' => 'direct', 'idx' => 1],
                    // q3 → idx 2
                    ['text' => "Quand j'explore une piste professionnelle, je recherche surtout les informations qui la valident.", 'biais' => 'biais_confirmation', 'type' => 'direct', 'idx' => 2],
                    // q4 → idx 3
                    ['text' => "La perspective de perdre mes acquis actuels (statut, salaire, sécurité) freine mes envies de changement.", 'biais' => 'aversion_perte', 'type' => 'direct', 'idx' => 3],
                    // q5 → idx 4
                    ['text' => "Quand une figure experte ou un supérieur me donne son avis sur ma carrière, j'ai du mal à ne pas m'y conformer.", 'biais' => 'biais_autorite', 'type' => 'direct', 'idx' => 4],
                    // q6 → idx 5
                    ['text' => "Si un proche a vécu une reconversion difficile, cela me paraît suffisant pour éviter cette voie.", 'biais' => 'disponibilite', 'type' => 'direct', 'idx' => 5],
                    // q7 → idx 6
                    ['text' => "Abandonner une voie dans laquelle j'ai beaucoup investi me semble être un gâchis insupportable.", 'biais' => 'cout_irrecuperable', 'type' => 'direct', 'idx' => 6],
                    // q8 → idx 7
                    ['text' => "Mon métier fait tellement partie de qui je suis que l'idée de le quitter me fait peur de perdre mon identité.", 'biais' => 'identite_metier', 'type' => 'direct', 'idx' => 7],
                    // q9 → idx 8
                    ['text' => "J'ai tendance à sous-estimer le temps et les efforts nécessaires pour réussir une transition professionnelle.", 'biais' => 'surconfiance', 'type' => 'direct', 'idx' => 8],
                    // q10 → idx 9
                    ['text' => "J'ai souvent l'impression que les autres sont plus compétents que moi pour les postes qui m'intéressent.", 'biais' => 'sous_estimation', 'type' => 'direct', 'idx' => 9],
                ],
            ],
            [
                'title'     => 'Vos schémas de décision',
                'questions' => [
                    // q11 → idx 10
                    ['text' => "Je trouve souvent de bonnes raisons pour rester là où je suis plutôt que de bouger.", 'biais' => 'statu_quo', 'type' => 'direct', 'idx' => 10],
                    // q12 → idx 11
                    ['text' => "Je ressentirais une gêne réelle si mon orientation professionnelle déçoit mes proches.", 'biais' => 'conformite_familiale', 'type' => 'direct', 'idx' => 11],
                    // q13 → idx 12
                    ['text' => "Les témoignages qui contredisent mes intuitions professionnelles me semblent moins fiables que ceux qui les confirment.", 'biais' => 'biais_confirmation', 'type' => 'direct', 'idx' => 12],
                    // q14 → idx 13
                    ['text' => "Je pense souvent à ce que je pourrais perdre avant de penser à ce que je pourrais gagner.", 'biais' => 'aversion_perte', 'type' => 'direct', 'idx' => 13],
                    // q15 → idx 14
                    ['text' => "Je remets rarement en question les conseils d'orientation qui me sont donnés par des professionnels.", 'biais' => 'biais_autorite', 'type' => 'direct', 'idx' => 14],
                    // q16 → idx 15
                    ['text' => "Les exemples d'échecs professionnels que j'ai vus autour de moi pèsent plus lourd que les statistiques générales.", 'biais' => 'disponibilite', 'type' => 'direct', 'idx' => 15],
                    // q17 → idx 16
                    ['text' => "Les années passées dans un métier sont pour moi une raison suffisante de continuer, même si je n'y suis plus épanoui.", 'biais' => 'cout_irrecuperable', 'type' => 'direct', 'idx' => 16],
                    // q18 → idx 17
                    ['text' => "Quand on me demande qui je suis, je cite instinctivement mon titre professionnel.", 'biais' => 'identite_metier', 'type' => 'direct', 'idx' => 17],
                    // q19 → idx 18
                    ['text' => "Je pense avoir une bonne compréhension de mes forces sans avoir besoin de les tester vraiment.", 'biais' => 'surconfiance', 'type' => 'direct', 'idx' => 18],
                    // q20 → idx 19
                    ['text' => "Je renonce parfois à postuler parce que je ne me sens pas légitime, même quand je remplis les critères.", 'biais' => 'sous_estimation', 'type' => 'direct', 'idx' => 19],
                ],
            ],
            [
                'title'     => 'Vos filtres cognitifs',
                'questions' => [
                    // q21 → idx 20 (inverse)
                    ['text' => "Face à une décision importante, je me sens à l'aise pour explorer des options radicalement différentes de ma situation actuelle.", 'biais' => 'statu_quo', 'type' => 'inverse', 'idx' => 20],
                    // q22 → idx 21 (inverse)
                    ['text' => "Je suis capable de faire un choix de carrière qui surprend ou déçoit ma famille si je suis convaincu que c'est juste pour moi.", 'biais' => 'conformite_familiale', 'type' => 'inverse', 'idx' => 21],
                    // q23 → idx 22 (inverse)
                    ['text' => "Avant de prendre une décision de carrière, je cherche activement des arguments contre mes propres convictions.", 'biais' => 'biais_confirmation', 'type' => 'inverse', 'idx' => 22],
                    // q24 → idx 23 (inverse)
                    ['text' => "Un gain potentiel important me motive autant qu'une perte équivalente me décourage.", 'biais' => 'aversion_perte', 'type' => 'inverse', 'idx' => 23],
                    // q25 → idx 24 (inverse)
                    ['text' => "Je me sens capable de contredire l'avis d'un expert si mon expérience personnelle me dit autre chose.", 'biais' => 'biais_autorite', 'type' => 'inverse', 'idx' => 24],
                    // q26 → idx 25 (inverse)
                    ['text' => "Je suis capable de relativiser un exemple négatif isolé en le replaçant dans un contexte plus large.", 'biais' => 'disponibilite', 'type' => 'inverse', 'idx' => 25],
                    // q27 → idx 26 (inverse)
                    ['text' => "Je suis capable de stopper un projet dans lequel j'ai beaucoup investi si je réalise qu'il ne mène nulle part.", 'biais' => 'cout_irrecuperable', 'type' => 'inverse', 'idx' => 26],
                    // q28 → idx 27 (inverse)
                    ['text' => "Je m'identifie davantage à mes valeurs et compétences qu'à mon titre professionnel.", 'biais' => 'identite_metier', 'type' => 'inverse', 'idx' => 27],
                    // q29 → idx 28 (direct — surconfiance)
                    ['text' => "J'ai tendance à surestimer ma capacité à contrôler les résultats d'une décision professionnelle.", 'biais' => 'surconfiance', 'type' => 'direct', 'idx' => 28],
                    // q30 → idx 29 (inverse)
                    ['text' => "Je reconnais facilement mes compétences et je suis à l'aise pour les valoriser en entretien.", 'biais' => 'sous_estimation', 'type' => 'inverse', 'idx' => 29],
                ],
            ],
            [
                'title'     => 'Vos réactions en situation réelle',
                'questions' => [
                    // q31 → idx 30 (scenario)
                    ['text' => "On vous propose un poste plus aligné avec vos valeurs mais dans un secteur inconnu. Votre premier réflexe est de lister les risques plutôt que les opportunités.", 'biais' => 'statu_quo', 'type' => 'scenario', 'idx' => 30, 'scenario' => true],
                    // q32 → idx 31 (scenario)
                    ['text' => "Vous souhaitez vous reconvertir dans un domaine que votre entourage considère comme peu sérieux. Vous retardez votre décision pour éviter les tensions familiales.", 'biais' => 'conformite_familiale', 'type' => 'scenario', 'idx' => 31, 'scenario' => true],
                    // q33 → idx 32 (scenario)
                    ['text' => "Vous envisagez une reconversion. Vous tombez sur deux articles : l'un positif, l'autre négatif sur ce secteur. Vous lisez l'article positif en entier et survolez le négatif.", 'biais' => 'biais_confirmation', 'type' => 'scenario', 'idx' => 32, 'scenario' => true],
                    // q34 → idx 33 (scenario)
                    ['text' => "Une reconversion vous permettrait de gagner 20% de plus dans 2 ans mais vous feriez une pause de 6 mois avec moins de revenus. Vous renoncez instinctivement.", 'biais' => 'aversion_perte', 'type' => 'scenario', 'idx' => 33, 'scenario' => true],
                    // q35 → idx 34 (scenario)
                    ['text' => "Un conseiller Pôle Emploi vous déconseille fortement une voie qui vous attire. Vous abandonnez l'idée sans chercher d'autres avis.", 'biais' => 'biais_autorite', 'type' => 'scenario', 'idx' => 34, 'scenario' => true],
                    // q36 → idx 35 (scenario)
                    ['text' => "Les médias rapportent plusieurs cas de reconversions ratées dans un secteur qui vous attire. Vous réduisez significativement votre intérêt pour ce secteur.", 'biais' => 'disponibilite', 'type' => 'scenario', 'idx' => 35, 'scenario' => true],
                    // q37 → idx 36 (scenario)
                    ['text' => "Après 12 ans dans le même secteur, une opportunité hors de votre domaine se présente. Le fait d'avoir \"tant investi\" pèse lourd dans votre décision de rester.", 'biais' => 'cout_irrecuperable', 'type' => 'scenario', 'idx' => 36, 'scenario' => true],
                    // q38 → idx 37 (scenario)
                    ['text' => "On vous propose une reconversion qui vous rend enthousiaste mais qui efface complètement votre titre actuel de votre carte de visite. Vous hésitez sérieusement.", 'biais' => 'identite_metier', 'type' => 'scenario', 'idx' => 37, 'scenario' => true],
                    // q39 → idx 38 (scenario)
                    ['text' => "Vous vous lancez dans une reconversion sans bilan approfondi parce que vous êtes convaincu de savoir ce qu'il vous faut. Six mois après, des obstacles imprévus surgissent.", 'biais' => 'surconfiance', 'type' => 'scenario', 'idx' => 38, 'scenario' => true],
                    // q40 → idx 39 (scenario)
                    ['text' => "Une offre correspond à 80% de votre profil. Vous ne postulez pas à cause des 20% manquants.", 'biais' => 'sous_estimation', 'type' => 'scenario', 'idx' => 39, 'scenario' => true],
                ],
            ],
        ];

        foreach ($sections as $sOrder => $sData) {
            $section = TestSection::updateOrCreate(
                ['test_id' => $test->id, 'order' => $sOrder + 1],
                ['title' => $sData['title']]
            );

            foreach ($sData['questions'] as $qOrder => $q) {
                $type = isset($q['scenario']) && $q['scenario'] ? 'scenario' : 'scale';

                TestQuestion::updateOrCreate(
                    ['section_id' => $section->id, 'order' => $qOrder + 1],
                    [
                        'type'    => 'scale',
                        'prompt'  => $q['text'],
                        'options' => $likert,
                        'scoring' => [
                            'idx'   => $q['idx'],
                            'biais' => $q['biais'],
                            'type'  => $q['type'],
                            'max'   => 5,
                            'min'   => 1,
                        ],
                        'required' => true,
                    ]
                );
            }
        }
    }
}
