<?php
/**
 * Référentiel Praxis 360 : 6 dimensions x 6 items, échelle, questions ouvertes.
 * Stocké en code (versionnable), pas en base.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Praxis360_Items {

    /** Échelle de fréquence à 5 points. */
    public static function scale() {
        return array(
            1 => 'Presque jamais',
            2 => 'Rarement',
            3 => 'Parfois',
            4 => 'Souvent',
            5 => 'Presque toujours',
        );
    }

    /** Catégories de relation. */
    public static function relations() {
        return array(
            'self'    => 'Auto-évaluation',
            'manager' => 'Manager',
            'peer'    => 'Pairs / collègues',
            'report'  => 'Collaborateurs',
            'client'  => 'Clients / partenaires',
        );
    }

    /** Dimensions et leurs items. Chaque item : [self, other]. */
    public static function dimensions() {
        return array(
            'communication' => array(
                'label' => 'Communication',
                'items' => array(
                    'communication_a' => array( "J'exprime mes idées de façon claire et structurée", "exprime ses idées de façon claire et structurée" ),
                    'communication_b' => array( "J'écoute réellement avant de répondre", "écoute réellement avant de répondre" ),
                    'communication_c' => array( "Je transmets l'information utile au bon moment", "transmet l'information utile au bon moment" ),
                    'communication_d' => array( "Je donne un feedback constructif et respectueux", "donne un feedback constructif et respectueux" ),
                    'communication_e' => array( "J'adapte mon discours à mon interlocuteur", "adapte son discours à son interlocuteur" ),
                    'communication_f' => array( "Je m'assure d'avoir été bien compris", "s'assure d'avoir été bien compris" ),
                ),
            ),
            'collaboration' => array(
                'label' => "Collaboration & esprit d'équipe",
                'items' => array(
                    'collaboration_a' => array( "Je coopère volontiers avec les autres", "coopère volontiers avec les autres" ),
                    'collaboration_b' => array( "Je partage mes connaissances et ressources", "partage ses connaissances et ressources" ),
                    'collaboration_c' => array( "Je propose mon aide quand un collègue en a besoin", "propose son aide quand un collègue en a besoin" ),
                    'collaboration_d' => array( "Je gère les désaccords de manière constructive", "gère les désaccords de manière constructive" ),
                    'collaboration_e' => array( "Je valorise les contributions des autres", "valorise les contributions des autres" ),
                    'collaboration_f' => array( "Je privilégie la réussite collective à la réussite individuelle", "privilégie la réussite collective à la réussite individuelle" ),
                ),
            ),
            'adaptabilite' => array(
                'label' => 'Adaptabilité',
                'items' => array(
                    'adaptabilite_a' => array( "Je m'ajuste sereinement aux imprévus", "s'ajuste sereinement aux imprévus" ),
                    'adaptabilite_b' => array( "Je reste efficace en situation de changement", "reste efficace en situation de changement" ),
                    'adaptabilite_c' => array( "J'accueille les idées nouvelles", "accueille les idées nouvelles" ),
                    'adaptabilite_d' => array( "Je tire des apprentissages de mes erreurs", "tire des apprentissages de ses erreurs" ),
                    'adaptabilite_e' => array( "Je propose des solutions concrètes face aux obstacles", "propose des solutions concrètes face aux obstacles" ),
                    'adaptabilite_f' => array( "Je sors de ma zone de confort quand la situation l'exige", "sort de sa zone de confort quand la situation l'exige" ),
                ),
            ),
            'relation' => array(
                'label' => 'Intelligence relationnelle / empathie',
                'items' => array(
                    'relation_a' => array( "Je prends en compte le point de vue des autres", "prend en compte le point de vue des autres" ),
                    'relation_b' => array( "Je traite chacun avec respect", "traite chacun avec respect" ),
                    'relation_c' => array( "Je reste posé(e) dans les situations tendues", "reste posé(e) dans les situations tendues" ),
                    'relation_d' => array( "Je perçois l'état émotionnel de mon entourage", "perçoit l'état émotionnel de son entourage" ),
                    'relation_e' => array( "Je crée un climat de confiance autour de moi", "crée un climat de confiance autour de lui/elle" ),
                    'relation_f' => array( "Je sais désamorcer les tensions entre personnes", "sait désamorcer les tensions entre personnes" ),
                ),
            ),
            'fiabilite' => array(
                'label' => 'Fiabilité & sens des responsabilités',
                'items' => array(
                    'fiabilite_a' => array( "Je tiens mes engagements dans les délais", "tient ses engagements dans les délais" ),
                    'fiabilite_b' => array( "J'assume mes responsabilités, y compris en cas d'erreur", "assume ses responsabilités, y compris en cas d'erreur" ),
                    'fiabilite_c' => array( "Je travaille avec rigueur et fiabilité", "travaille avec rigueur et fiabilité" ),
                    'fiabilite_d' => array( "Je fais preuve de transparence", "fait preuve de transparence" ),
                    'fiabilite_e' => array( "Je respecte mes engagements même sous pression", "respecte ses engagements même sous pression" ),
                    'fiabilite_f' => array( "Je préviens en amont en cas de difficulté ou de retard", "prévient en amont en cas de difficulté ou de retard" ),
                ),
            ),
            'leadership' => array(
                'label' => "Leadership d'influence",
                'items' => array(
                    'leadership_a' => array( "Je prends des initiatives sans attendre qu'on me le demande", "prend des initiatives sans attendre qu'on le lui demande" ),
                    'leadership_b' => array( "Je mobilise les autres autour d'un objectif", "mobilise les autres autour d'un objectif" ),
                    'leadership_c' => array( "Je prends des décisions et je les assume", "prend des décisions et les assume" ),
                    'leadership_d' => array( "J'inspire confiance par l'exemple", "inspire confiance par l'exemple" ),
                    'leadership_e' => array( "Je reconnais et encourage les efforts des autres", "reconnaît et encourage les efforts des autres" ),
                    'leadership_f' => array( "Je défends mes idées avec conviction tout en restant ouvert(e)", "défend ses idées avec conviction tout en restant ouvert(e)" ),
                ),
            ),
        );
    }

    /** Questions ouvertes : [self, other]. */
    public static function open_questions() {
        return array(
            'open_1' => array( "Quels sont vos points forts à conserver ?", "Quels sont les points forts de %s à conserver ?" ),
            'open_2' => array( "Sur quels comportements pourriez-vous progresser en priorité ?", "Sur quels comportements %s pourrait-il/elle progresser en priorité ?" ),
            'open_3' => array( "Un conseil ou message libre pour vous-même ?", "Un conseil ou message libre pour aider %s à progresser ?" ),
        );
    }

    /** Liste plate des clés d'items (pour validation). */
    public static function all_item_keys() {
        $keys = array();
        foreach ( self::dimensions() as $dim ) {
            foreach ( $dim['items'] as $key => $v ) {
                $keys[] = $key;
            }
        }
        return $keys;
    }

    /**
     * Construit la liste ordonnée des questions à présenter selon la relation.
     * @param string $relation     self|manager|peer|report|client
     * @param string $subject_name Prénom du sujet (pour la formulation hétéro).
     */
    public static function build_questionnaire( $relation, $subject_name ) {
        $is_self = ( 'self' === $relation );
        $out     = array();
        foreach ( self::dimensions() as $dim_key => $dim ) {
            foreach ( $dim['items'] as $item_key => $phr ) {
                if ( $is_self ) {
                    $text = $phr[0];
                } else {
                    $text = $subject_name . ' ' . $phr[1];
                }
                $out[] = array(
                    'key'       => $item_key,
                    'dimension' => $dim['label'],
                    'text'      => $text,
                );
            }
        }
        return $out;
    }

    public static function build_open_questions( $relation, $subject_name ) {
        $is_self = ( 'self' === $relation );
        $out     = array();
        foreach ( self::open_questions() as $key => $phr ) {
            $text = $is_self ? $phr[0] : sprintf( $phr[1], $subject_name );
            $out[] = array( 'key' => $key, 'text' => $text );
        }
        return $out;
    }
}
