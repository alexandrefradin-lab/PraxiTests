<?php

namespace Praxis\Plugins\PraxiBiais\Scoring;

use App\Models\TestAttempt;
use Praxis\Core\TestEngine\Contracts\ScoringEngineContract;

class BiaisScoringEngine implements ScoringEngineContract
{
    // ── Catalogue des biais ───────────────────────────────────────────────────
    private const BIAIS = [
        'statu_quo' => [
            'label'  => 'Statu quo',
            'teaser' => 'Votre cerveau confond prudence et paralysie.',
        ],
        'aversion_perte' => [
            'label'  => 'Aversion à la perte',
            'teaser' => 'Vous calculez les risques du changement — jamais les risques de rester.',
        ],
        'cout_irrecuperable' => [
            'label'  => 'Coût irrécupérable',
            'teaser' => 'Le passé pèse sur votre futur plus que vous ne le croyez.',
        ],
        'conformite_familiale' => [
            'label'  => 'Conformité familiale',
            'teaser' => 'Votre carrière — ou celle que les autres ont imaginée pour vous ?',
        ],
        'biais_autorite' => [
            'label'  => "Biais d'autorité",
            'teaser' => 'Vous faites confiance aux experts. Mais faites-vous confiance à vous ?',
        ],
        'identite_metier' => [
            'label'  => 'Identité métier figée',
            'teaser' => "Vous n'êtes pas votre titre. Mais votre cerveau, lui, y croit encore.",
        ],
        'biais_confirmation' => [
            'label'  => 'Biais de confirmation',
            'teaser' => 'Vous cherchez des réponses — ou des confirmations ?',
        ],
        'disponibilite' => [
            'label'  => 'Biais de disponibilité',
            'teaser' => 'Un échec vu de près pèse plus lourd que mille succès lointains.',
        ],
        'surconfiance' => [
            'label'  => 'Surconfiance',
            'teaser' => "Vous avez un plan. Mais avez-vous prévu ce que vous n'avez pas prévu ?",
        ],
        'sous_estimation' => [
            'label'  => 'Sous-estimation de soi',
            'teaser' => "Vous êtes qualifié. Votre cerveau, lui, n'en est pas convaincu.",
        ],
    ];

    // ── Profils composites ────────────────────────────────────────────────────
    private const PROFILES = [
        'prisonnier_passe' => [
            'label'  => "Le Gardien de l'Acquis",
            'biais'  => ['cout_irrecuperable', 'identite_metier', 'aversion_perte'],
            'seuil'  => 65,
            'desc'   => "Vous avez construit quelque chose. Des années, des compétences, un statut. Et votre cerveau, en bon gardien, protège cet acquis avec une vigilance que vous ne contrôlez plus tout à fait. Le changement n'est pas un danger — mais il est traité comme tel.",
        ],
        'suradaptation_sociale' => [
            'label'  => 'Le Professionnel Invisible',
            'biais'  => ['conformite_familiale', 'biais_autorite', 'sous_estimation'],
            'seuil'  => 65,
            'desc'   => "Vous excellez à vous adapter aux attentes des autres. Mais dans ce processus, votre propre voix s'est progressivement effacée. Vos choix professionnels portent souvent l'empreinte de ce que les autres attendaient de vous.",
        ],
        'faux_rationnel' => [
            'label'  => "L'Analyste de sa propre chambre d'écho",
            'biais'  => ['biais_confirmation', 'disponibilite', 'surconfiance'],
            'seuil'  => 65,
            'desc'   => "Vous analysez beaucoup. Vous avez des arguments solides pour chaque décision. Mais vos analyses confirment surtout ce que vous pensez déjà — et les exemples qui vous viennent spontanément biaisent votre lecture du réel.",
        ],
        'oscillant' => [
            'label'  => 'Le Décideur en Dents de Scie',
            'biais'  => ['surconfiance', 'sous_estimation'],
            'seuil'  => 65,
            'desc'   => "Votre potentiel est réel — mais votre regard sur vous-même oscille trop pour l'exploiter pleinement. Vous alternez entre des moments de grande confiance et des phases de doute profond, rendant vos décisions professionnelles imprévisibles.",
        ],
        'mixte' => [
            'label'  => 'Le Profil Pluriel',
            'biais'  => [],
            'seuil'  => 0,
            'desc'   => "Plusieurs mécanismes cognitifs influencent vos décisions de façon équilibrée. Votre diagnostic complet détaille les tendances les plus actives et leurs interactions.",
        ],
    ];

    // ── Descriptions détaillées des biais (pour la page résultats) ───────────
    private const BIAIS_DETAILS = [
        'statu_quo' => [
            'definition'    => "Votre cerveau accorde une valeur supérieure à l'état actuel des choses. Le changement est perçu comme un risque, même quand l'immobilisme en est un plus grand.",
            'manifestation' => "Vous trouvez instinctivement de bonnes raisons pour rester là où vous êtes — même quand cette situation ne vous convient plus.",
            'cout'          => "Des opportunités réelles passent inaperçues parce qu'elles nécessitent de quitter une zone connue.",
            'piste'         => "La prochaine fois que vous hésitez, posez-vous cette question : \"Que se passerait-il si je ne faisais rien ?\" Le statu quo a aussi un coût.",
        ],
        'aversion_perte' => [
            'definition'    => "La douleur d'une perte est psychologiquement environ deux fois plus intense qu'un gain équivalent. Vous surpondérez ce que vous pourriez perdre.",
            'manifestation' => "Avant chaque décision importante, la liste des inconvénients est plus longue et plus vivante que celle des bénéfices potentiels.",
            'cout'          => "Des reconversions rentables à long terme sont écartées à cause d'une transition à court terme difficile à envisager.",
            'piste'         => "Calculez le coût de rester : stress, désengagement, années perdues. Les pertes liées à l'immobilisme sont réelles mais invisibles.",
        ],
        'cout_irrecuperable' => [
            'definition'    => "Les ressources passées (temps, diplômes, statut, années investies) influencent vos décisions futures — alors qu'elles ne peuvent pas être récupérées.",
            'manifestation' => "Vous restez dans une voie parce que vous y avez \"trop investi pour arrêter\", même quand cette voie ne vous correspond plus.",
            'cout'          => "Chaque année supplémentaire passée à honorer un investissement passé est une année de moins pour construire ce qui vous correspond vraiment.",
            'piste'         => "La vraie question n'est pas \"Qu'ai-je investi ?\" mais \"Qu'est-ce qui me correspond aujourd'hui et demain ?\"",
        ],
        'conformite_familiale' => [
            'definition'    => "Votre système de décision intègre fortement les normes et attentes de votre groupe d'appartenance familial ou social.",
            'manifestation' => "Certaines options professionnelles sont écartées avant même d'être étudiées — parce qu'elles seraient mal comprises ou mal acceptées par vos proches.",
            'cout'          => "Une carrière construite pour les autres est une carrière qui ne vous appartient pas vraiment.",
            'piste'         => "Distinguez les conseils bienveillants des injonctions implicites. Les personnes qui vous aiment peuvent vouloir votre sécurité plutôt que votre épanouissement.",
        ],
        'biais_autorite' => [
            'definition'    => "Vous accordez un poids excessif aux avis des figures légitimes — experts, supérieurs, conseillers — au détriment de votre propre jugement.",
            'manifestation' => "Un avis négatif d'un professionnel suffit souvent à clore une piste que vous n'avez pas encore explorée vous-même.",
            'cout'          => "Votre discernement personnel — construit sur des années d'expérience vécue — est sous-exploité dans vos décisions importantes.",
            'piste'         => "Les experts connaissent leur domaine. Vous, vous connaissez votre vie. Ces deux sources d'information ont le même droit de cité.",
        ],
        'identite_metier' => [
            'definition'    => "Votre identité personnelle s'est fusionnée avec votre rôle professionnel. Changer de métier est inconsciemment vécu comme perdre une partie de vous-même.",
            'manifestation' => "Quand vous imaginez une reconversion, la première résistance n'est pas logistique — elle est existentielle. Qui seriez-vous sans ce titre ?",
            'cout'          => "Des options pourtant alignées avec vos valeurs sont écartées parce qu'elles effacent un titre qui vous a longtemps défini.",
            'piste'         => "Listez 5 compétences que vous maîtrisez indépendamment de votre intitulé de poste. C'est là que se trouve votre vraie valeur transférable.",
        ],
        'biais_confirmation' => [
            'definition'    => "Votre cerveau filtre l'information : il retient ce qui confirme vos intuitions et minimise ce qui les contredit.",
            'manifestation' => "Votre exploration des options professionnelles est plus large en apparence qu'en réalité — les informations contraires glissent sur vous.",
            'cout'          => "Des décisions prises avec une conviction solide mais construite sur un dossier incomplet.",
            'piste'         => "Pour chaque piste sérieuse, cherchez délibérément trois arguments contre. Ce n'est pas du pessimisme — c'est de la rigueur décisionnelle.",
        ],
        'disponibilite' => [
            'definition'    => "Votre cerveau surestime la probabilité d'un événement s'il est facilement mémorisable — typiquement parce qu'il est émotionnellement marquant ou récent.",
            'manifestation' => "Un exemple d'échec observé chez un proche pèse plus lourd dans votre calcul que les statistiques générales sur les reconversions réussies.",
            'cout'          => "Les cas d'échec visibles biaisent votre perception du risque réel. Les succès silencieux n'alimentent pas vos scenarii.",
            'piste'         => "Quand un exemple négatif s'impose à vous, demandez-vous : \"Est-ce que je connais autant de cas positifs que négatifs ? Pourquoi pas ?\"",
        ],
        'surconfiance' => [
            'definition'    => "Vous surestimez votre capacité à prévoir, contrôler et maîtriser les résultats de vos décisions professionnelles.",
            'manifestation' => "Les étapes de préparation, de bilan ou d'exploration vous semblent parfois superflues — vous avez l'intuition de savoir déjà ce qu'il vous faut.",
            'cout'          => "Des transitions mal préparées qui rencontrent des obstacles prévisibles, vécus comme des injustices plutôt que comme des signaux anticipables.",
            'piste'         => "Avant toute décision importante, listez les trois choses que vous ne savez pas encore. L'humilité épistémique est une compétence décisionnelle.",
        ],
        'sous_estimation' => [
            'definition'    => "Vous sous-évaluez systématiquement vos compétences, votre légitimité et votre valeur professionnelle réelle.",
            'manifestation' => "Vous vous auto-censurez avant même de candidater. Les 20% qui manquent pèsent plus lourd que les 80% que vous maîtrisez.",
            'cout'          => "Des opportunités réelles non saisies, des négociations salariales déficientes, une carrière construite en dessous de votre potentiel réel.",
            'piste'         => "Demandez à trois personnes de confiance de lister trois compétences qu'ils vous reconnaissent. Leurs réponses vous surprendront probablement.",
        ],
    ];

    public function key(): string
    {
        return 'praxibiais-cognitif';
    }

    public function score(TestAttempt $attempt): array
    {
        // Récolte des réponses indexées par idx (0-39)
        $byIdx = [];
        foreach ($attempt->answers()->with('question')->get() as $answer) {
            $idx = $answer->question->scoring['idx'] ?? null;
            if ($idx !== null) {
                $byIdx[(int) $idx] = max(1, min(5, (int) $answer->value));
            }
        }

        // Calcul des scores par biais
        $scores = $this->computeScores($byIdx);

        // Profil dominant
        $profile = $this->detectProfile($scores);

        // Top 3 biais
        $sorted = $scores;
        usort($sorted, fn ($a, $b) => $b['score'] <=> $a['score']);
        $top3 = array_slice($sorted, 0, 3);

        // Enrichissement avec détails
        $scoresWithDetails = [];
        foreach ($scores as $slug => $data) {
            $scoresWithDetails[$slug] = array_merge($data, [
                'details' => self::BIAIS_DETAILS[$slug] ?? [],
            ]);
        }

        return [
            'engine'      => $this->key(),
            'scores'      => $scoresWithDetails,
            'top3'        => $top3,
            'profile'     => $profile,
            'computed_at' => now()->toIso8601String(),
        ];
    }

    // ── Calcul des scores (adapté fidèlement depuis BiaisPro_Scoring) ─────────
    private function computeScores(array $answers): array
    {
        // Mapping questions → biais (idx 0-based = numéro question 1-based − 1)
        $mapping = [
            // statu_quo : q1(direct), q11(direct), q21(inverse), q31(scenario)
            ['idx' => 0,  'biais' => 'statu_quo',           'type' => 'direct'],
            ['idx' => 10, 'biais' => 'statu_quo',           'type' => 'direct'],
            ['idx' => 20, 'biais' => 'statu_quo',           'type' => 'inverse'],
            ['idx' => 30, 'biais' => 'statu_quo',           'type' => 'scenario'],
            // aversion_perte : q4, q14, q24, q34
            ['idx' => 3,  'biais' => 'aversion_perte',      'type' => 'direct'],
            ['idx' => 13, 'biais' => 'aversion_perte',      'type' => 'direct'],
            ['idx' => 23, 'biais' => 'aversion_perte',      'type' => 'inverse'],
            ['idx' => 33, 'biais' => 'aversion_perte',      'type' => 'scenario'],
            // cout_irrecuperable : q7, q17, q27, q37
            ['idx' => 6,  'biais' => 'cout_irrecuperable',  'type' => 'direct'],
            ['idx' => 16, 'biais' => 'cout_irrecuperable',  'type' => 'direct'],
            ['idx' => 26, 'biais' => 'cout_irrecuperable',  'type' => 'inverse'],
            ['idx' => 36, 'biais' => 'cout_irrecuperable',  'type' => 'scenario'],
            // conformite_familiale : q2, q12, q22, q32
            ['idx' => 1,  'biais' => 'conformite_familiale','type' => 'direct'],
            ['idx' => 11, 'biais' => 'conformite_familiale','type' => 'direct'],
            ['idx' => 21, 'biais' => 'conformite_familiale','type' => 'inverse'],
            ['idx' => 31, 'biais' => 'conformite_familiale','type' => 'scenario'],
            // biais_autorite : q5, q15, q25, q35
            ['idx' => 4,  'biais' => 'biais_autorite',      'type' => 'direct'],
            ['idx' => 14, 'biais' => 'biais_autorite',      'type' => 'direct'],
            ['idx' => 24, 'biais' => 'biais_autorite',      'type' => 'inverse'],
            ['idx' => 34, 'biais' => 'biais_autorite',      'type' => 'scenario'],
            // identite_metier : q8, q18, q28, q38
            ['idx' => 7,  'biais' => 'identite_metier',     'type' => 'direct'],
            ['idx' => 17, 'biais' => 'identite_metier',     'type' => 'direct'],
            ['idx' => 27, 'biais' => 'identite_metier',     'type' => 'inverse'],
            ['idx' => 37, 'biais' => 'identite_metier',     'type' => 'scenario'],
            // biais_confirmation : q3, q13, q23, q33
            ['idx' => 2,  'biais' => 'biais_confirmation',  'type' => 'direct'],
            ['idx' => 12, 'biais' => 'biais_confirmation',  'type' => 'direct'],
            ['idx' => 22, 'biais' => 'biais_confirmation',  'type' => 'inverse'],
            ['idx' => 32, 'biais' => 'biais_confirmation',  'type' => 'scenario'],
            // disponibilite : q6, q16, q26, q36
            ['idx' => 5,  'biais' => 'disponibilite',       'type' => 'direct'],
            ['idx' => 15, 'biais' => 'disponibilite',       'type' => 'direct'],
            ['idx' => 25, 'biais' => 'disponibilite',       'type' => 'inverse'],
            ['idx' => 35, 'biais' => 'disponibilite',       'type' => 'scenario'],
            // surconfiance : q9, q19, q29(direct!), q39
            ['idx' => 8,  'biais' => 'surconfiance',        'type' => 'direct'],
            ['idx' => 18, 'biais' => 'surconfiance',        'type' => 'direct'],
            ['idx' => 28, 'biais' => 'surconfiance',        'type' => 'direct'],
            ['idx' => 38, 'biais' => 'surconfiance',        'type' => 'scenario'],
            // sous_estimation : q10, q20, q30, q40
            ['idx' => 9,  'biais' => 'sous_estimation',     'type' => 'direct'],
            ['idx' => 19, 'biais' => 'sous_estimation',     'type' => 'direct'],
            ['idx' => 29, 'biais' => 'sous_estimation',     'type' => 'inverse'],
            ['idx' => 39, 'biais' => 'sous_estimation',     'type' => 'scenario'],
        ];

        // Accumulateurs
        $brut = [];
        $max  = [];
        foreach (array_keys(self::BIAIS) as $slug) {
            $brut[$slug] = 0.0;
            $max[$slug]  = 0.0;
        }

        foreach ($mapping as $item) {
            $val  = (float) ($answers[$item['idx']] ?? 3);
            $slug = $item['biais'];

            match ($item['type']) {
                'direct'   => [$brut[$slug] += $val,       $max[$slug] += 5.0],
                'inverse'  => [$brut[$slug] += (6.0 - $val), $max[$slug] += 5.0],
                'scenario' => [$brut[$slug] += $val * 1.5, $max[$slug] += 7.5],
                default    => null,
            };
        }

        $scores = [];
        foreach (self::BIAIS as $slug => $info) {
            $normalized = $max[$slug] > 0
                ? (int) round(($brut[$slug] / $max[$slug]) * 100)
                : 0;

            $scores[$slug] = [
                'slug'   => $slug,
                'label'  => $info['label'],
                'teaser' => $info['teaser'],
                'score'  => $normalized,
                'level'  => $this->level($normalized),
            ];
        }

        return $scores;
    }

    private function level(int $score): array
    {
        if ($score >= 80) return ['slug' => 'critical',  'label' => 'Blocage majeur'];
        if ($score >= 65) return ['slug' => 'high',      'label' => 'Frein significatif'];
        if ($score >= 35) return ['slug' => 'moderate',  'label' => 'Tendance active'];
        return                   ['slug' => 'low',       'label' => 'Angle mort discret'];
    }

    private function detectProfile(array $scores): array
    {
        $candidates = [];

        foreach (self::PROFILES as $pSlug => $profile) {
            if ($pSlug === 'mixte' || empty($profile['biais'])) continue;

            $allAbove = true;
            $total    = 0;
            foreach ($profile['biais'] as $b) {
                $s = $scores[$b]['score'] ?? 0;
                if ($s < $profile['seuil']) {
                    $allAbove = false;
                    break;
                }
                $total += $s;
            }
            if ($allAbove) {
                $candidates[$pSlug] = $total;
            }
        }

        if (empty($candidates)) {
            return array_merge(['slug' => 'mixte'], self::PROFILES['mixte']);
        }

        arsort($candidates);
        $winner = array_key_first($candidates);
        return array_merge(['slug' => $winner], self::PROFILES[$winner]);
    }
}
