<?php

namespace Praxis\Plugins\Praxis360\Data;

/**
 * Données statiques du test Praxis 360 — version AUTO-ÉVALUATION (mono-candidat).
 *
 * Source : plugin WordPress « Praxis 360 » (class-praxis360-items.php).
 * 6 dimensions × 6 items = 36 items, échelle de fréquence à 5 points.
 * Aucun item inversé dans le référentiel d'origine (tous formulés positivement).
 *
 * NB : le WP était multi-évaluateurs (self + manager + pairs + collaborateurs +
 * clients). Seule la formulation « self » (1re personne) est reprise ici, car le
 * moteur PraxiQuest est mono-répondant. Voir README pour le caveat multi-rater.
 */
class Questions
{
    /** Échelle de fréquence à 5 points, identique au WP. */
    private const SCALE = [
        'min'       => 1,
        'max'       => 5,
        'min_label' => 'Presque jamais',
        'max_label' => 'Presque toujours',
    ];

    /**
     * Retourne toutes les questions du test (36 items d'auto-évaluation).
     *
     * Champ 'scoring' : { "dimension": "<dim_key>", "weight": 1 }.
     * Aucun 'reversed' : tous les items sont formulés positivement.
     */
    public static function all(): array
    {
        $items = [
            // ── Communication ─────────────────────────────────────────────
            'communication' => [
                "J'exprime mes idées de façon claire et structurée",
                "J'écoute réellement avant de répondre",
                "Je transmets l'information utile au bon moment",
                "Je donne un feedback constructif et respectueux",
                "J'adapte mon discours à mon interlocuteur",
                "Je m'assure d'avoir été bien compris",
            ],
            // ── Collaboration & esprit d'équipe ───────────────────────────
            'collaboration' => [
                "Je coopère volontiers avec les autres",
                "Je partage mes connaissances et ressources",
                "Je propose mon aide quand un collègue en a besoin",
                "Je gère les désaccords de manière constructive",
                "Je valorise les contributions des autres",
                "Je privilégie la réussite collective à la réussite individuelle",
            ],
            // ── Adaptabilité ──────────────────────────────────────────────
            'adaptabilite' => [
                "Je m'ajuste sereinement aux imprévus",
                "Je reste efficace en situation de changement",
                "J'accueille les idées nouvelles",
                "Je tire des apprentissages de mes erreurs",
                "Je propose des solutions concrètes face aux obstacles",
                "Je sors de ma zone de confort quand la situation l'exige",
            ],
            // ── Intelligence relationnelle / empathie ─────────────────────
            'relation' => [
                "Je prends en compte le point de vue des autres",
                "Je traite chacun avec respect",
                "Je reste posé(e) dans les situations tendues",
                "Je perçois l'état émotionnel de mon entourage",
                "Je crée un climat de confiance autour de moi",
                "Je sais désamorcer les tensions entre personnes",
            ],
            // ── Fiabilité & sens des responsabilités ──────────────────────
            'fiabilite' => [
                "Je tiens mes engagements dans les délais",
                "J'assume mes responsabilités, y compris en cas d'erreur",
                "Je travaille avec rigueur et fiabilité",
                "Je fais preuve de transparence",
                "Je respecte mes engagements même sous pression",
                "Je préviens en amont en cas de difficulté ou de retard",
            ],
            // ── Leadership d'influence ────────────────────────────────────
            'leadership' => [
                "Je prends des initiatives sans attendre qu'on me le demande",
                "Je mobilise les autres autour d'un objectif",
                "Je prends des décisions et je les assume",
                "J'inspire confiance par l'exemple",
                "Je reconnais et encourage les efforts des autres",
                "Je défends mes idées avec conviction tout en restant ouvert(e)",
            ],
        ];

        $dims = self::dimensions();
        $out  = [];

        foreach ($items as $dimKey => $prompts) {
            $section = $dims[$dimKey]['label'];
            foreach ($prompts as $prompt) {
                $out[] = [
                    'section' => $section,
                    'prompt'  => $prompt,
                    'type'    => 'scale',
                    'options' => self::SCALE,
                    'scoring' => ['dimension' => $dimKey, 'weight' => 1],
                ];
            }
        }

        return $out;
    }

    /**
     * Mêmes 36 items, reformulés à la 3e personne pour les ÉVALUATEURS (360°).
     * L'ordre est strictement identique à all() : le i-ème prompt évaluateur
     * correspond au i-ème item d'auto-évaluation (mapping par position sur les
     * question_id seedés). Introduction suggérée : « À quelle fréquence cette
     * personne… ».
     */
    public static function forRater(): array
    {
        $items = [
            'communication' => [
                "Exprime ses idées de façon claire et structurée",
                "Écoute réellement avant de répondre",
                "Transmet l'information utile au bon moment",
                "Donne un feedback constructif et respectueux",
                "Adapte son discours à son interlocuteur",
                "S'assure d'avoir été bien compris(e)",
            ],
            'collaboration' => [
                "Coopère volontiers avec les autres",
                "Partage ses connaissances et ses ressources",
                "Propose son aide quand un collègue en a besoin",
                "Gère les désaccords de manière constructive",
                "Valorise les contributions des autres",
                "Privilégie la réussite collective à la réussite individuelle",
            ],
            'adaptabilite' => [
                "S'ajuste sereinement aux imprévus",
                "Reste efficace en situation de changement",
                "Accueille les idées nouvelles",
                "Tire des apprentissages de ses erreurs",
                "Propose des solutions concrètes face aux obstacles",
                "Sort de sa zone de confort quand la situation l'exige",
            ],
            'relation' => [
                "Prend en compte le point de vue des autres",
                "Traite chacun avec respect",
                "Reste posé(e) dans les situations tendues",
                "Perçoit l'état émotionnel de son entourage",
                "Crée un climat de confiance autour d'elle / de lui",
                "Sait désamorcer les tensions entre personnes",
            ],
            'fiabilite' => [
                "Tient ses engagements dans les délais",
                "Assume ses responsabilités, y compris en cas d'erreur",
                "Travaille avec rigueur et fiabilité",
                "Fait preuve de transparence",
                "Respecte ses engagements même sous pression",
                "Prévient en amont en cas de difficulté ou de retard",
            ],
            'leadership' => [
                "Prend des initiatives sans attendre qu'on le lui demande",
                "Mobilise les autres autour d'un objectif",
                "Prend des décisions et les assume",
                "Inspire confiance par l'exemple",
                "Reconnaît et encourage les efforts des autres",
                "Défend ses idées avec conviction tout en restant ouvert(e)",
            ],
        ];

        $dims = self::dimensions();
        $out  = [];
        foreach ($items as $dimKey => $prompts) {
            foreach ($prompts as $prompt) {
                $out[] = [
                    'section' => $dims[$dimKey]['label'],
                    'prompt'  => $prompt,
                    'type'    => 'scale',
                    'options' => self::SCALE,
                    'scoring' => ['dimension' => $dimKey, 'weight' => 1],
                ];
            }
        }
        return $out;
    }

    /** Échelle de fréquence exposée aux pages. */
    public static function scale(): array
    {
        return self::SCALE;
    }

    /**
     * Questions ouvertes (verbatims) posées aux évaluateurs. Clés stables
     * réutilisées pour le stockage (evaluation_invitations.verbatims) et la
     * synthèse IA.
     */
    public static function verbatims(): array
    {
        return [
            'strength' => "Selon vous, quel est le principal point fort de cette personne dans sa manière de fonctionner ?",
            'growth'   => "Quel serait, selon vous, son principal axe de progrès ?",
            'advice'   => "Un conseil ou un encouragement que vous aimeriez lui transmettre ?",
        ];
    }

    /**
     * Métadonnées des 6 dimensions soft skills (libellés repris du WP).
     */
    public static function dimensions(): array
    {
        return [
            'communication' => [
                'label'       => 'Communication',
                'description' => "Clarté, écoute, feedback et adaptation du discours à l'interlocuteur.",
                'color'       => '#1B2B3A',
            ],
            'collaboration' => [
                'label'       => "Collaboration & esprit d'équipe",
                'description' => "Coopération, partage, gestion des désaccords et primauté du collectif.",
                'color'       => '#B8913A',
            ],
            'adaptabilite' => [
                'label'       => 'Adaptabilité',
                'description' => "Souplesse face aux imprévus, ouverture au changement et résolution de problèmes.",
                'color'       => '#1B2B3A',
            ],
            'relation' => [
                'label'       => 'Intelligence relationnelle / empathie',
                'description' => "Prise en compte d'autrui, respect, gestion des émotions et climat de confiance.",
                'color'       => '#B8913A',
            ],
            'fiabilite' => [
                'label'       => 'Fiabilité & sens des responsabilités',
                'description' => "Tenue des engagements, rigueur, transparence et anticipation des difficultés.",
                'color'       => '#1B2B3A',
            ],
            'leadership' => [
                'label'       => "Leadership d'influence",
                'description' => "Initiative, mobilisation, décision, exemplarité et reconnaissance des efforts.",
                'color'       => '#B8913A',
            ],
        ];
    }
}
