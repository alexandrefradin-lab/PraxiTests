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
