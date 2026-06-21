<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Praxisens {

    /**
     * Définition des 18 items (6 par sous-dimension).
     * dim : EOE = Saturation/sur-stimulation, AES = Sensibilité esthétique & profondeur,
     *       LST = Seuil sensoriel bas.
     * Tous les items sont cotés positivement (1..5, 5 = plus sensible).
     */
    public static function questions() {
        return array(
            // --- EOE : Facilité de saturation / sur-stimulation ---
            array( 'id' => 1,  'dim' => 'EOE', 'text' => "Les humeurs des personnes autour de moi déteignent fortement sur la mienne." ),
            array( 'id' => 2,  'dim' => 'EOE', 'text' => "Quand j'ai beaucoup à faire en peu de temps, je me sens vite débordé(e)." ),
            array( 'id' => 3,  'dim' => 'EOE', 'text' => "Après une journée chargée, j'ai besoin de me retirer au calme pour récupérer." ),
            array( 'id' => 4,  'dim' => 'EOE', 'text' => "Mon système nerveux se sent parfois tellement saturé que je dois m'isoler." ),
            array( 'id' => 5,  'dim' => 'EOE', 'text' => "Je sursaute facilement." ),
            array( 'id' => 6,  'dim' => 'EOE', 'text' => "Cela me gêne quand on me demande de faire trop de choses à la fois." ),

            // --- AES : Sensibilité esthétique & profondeur de traitement ---
            array( 'id' => 7,  'dim' => 'AES', 'text' => "Je perçois dans mon environnement des subtilités que beaucoup de gens ne remarquent pas." ),
            array( 'id' => 8,  'dim' => 'AES', 'text' => "J'ai une vie intérieure riche et complexe." ),
            array( 'id' => 9,  'dim' => 'AES', 'text' => "Je suis profondément ému(e) par les arts ou la musique." ),
            array( 'id' => 10, 'dim' => 'AES', 'text' => "Je remarque et savoure les parfums, les saveurs ou les sons délicats." ),
            array( 'id' => 11, 'dim' => 'AES', 'text' => "Quand quelqu'un est mal à l'aise dans un lieu, je sens souvent ce qu'il faudrait changer." ),
            array( 'id' => 12, 'dim' => 'AES', 'text' => "Je réfléchis longuement et en profondeur aux choses qui me touchent." ),

            // --- LST : Seuil sensoriel bas ---
            array( 'id' => 13, 'dim' => 'LST', 'text' => "Je suis facilement submergé(e) par des stimulations fortes (lumières vives, odeurs puissantes, bruits)." ),
            array( 'id' => 14, 'dim' => 'LST', 'text' => "Les bruits forts me mettent mal à l'aise." ),
            array( 'id' => 15, 'dim' => 'LST', 'text' => "Je suis sensible à la douleur." ),
            array( 'id' => 16, 'dim' => 'LST', 'text' => "Je suis particulièrement sensible aux effets de la caféine." ),
            array( 'id' => 17, 'dim' => 'LST', 'text' => "Les textures rugueuses, les étiquettes ou certains tissus sur la peau me dérangent." ),
            array( 'id' => 18, 'dim' => 'LST', 'text' => "Une faim intense provoque chez moi une forte réaction (humeur ou concentration perturbées)." ),
        );
    }

    public static function dimensions() {
        return array(
            'EOE' => array(
                'label' => 'Sur-stimulation',
                'full'  => "Facilité de saturation",
                'desc'  => "Tendance à être vite débordé(e) par les sollicitations internes ou externes, et besoin de se retirer pour récupérer.",
                'color' => '#7c3aed',
            ),
            'AES' => array(
                'label' => 'Sensibilité esthétique',
                'full'  => "Sensibilité esthétique & profondeur",
                'desc'  => "Perception fine des subtilités, richesse de la vie intérieure et émotion profonde face au beau.",
                'color' => '#0ea5e9',
            ),
            'LST' => array(
                'label' => 'Seuil sensoriel',
                'full'  => "Seuil sensoriel bas",
                'desc'  => "Réactivité intense aux stimulations sensorielles : bruit, lumière, textures, douleur, substances.",
                'color' => '#10b981',
            ),
        );
    }

    /** 5 niveaux de réponse — émet 1..5 (jamais 0), conforme au contrat d'échelle. */
    public static function options() {
        return array(
            array( 'value' => 1, 'label' => "Pas du tout d'accord" ),
            array( 'value' => 2, 'label' => "Plutôt pas d'accord" ),
            array( 'value' => 3, 'label' => "Neutre" ),
            array( 'value' => 4, 'label' => "Plutôt d'accord" ),
            array( 'value' => 5, 'label' => "Tout à fait d'accord" ),
        );
    }

    public static function init() {
        add_shortcode( 'praxisens', array( __CLASS__, 'render_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
    }

    public static function activate() {
        global $wpdb;
        $table   = $wpdb->prefix . 'praxisens_results';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at DATETIME NOT NULL,
            first_name VARCHAR(120) NOT NULL DEFAULT '',
            email VARCHAR(190) NOT NULL DEFAULT '',
            score_global TINYINT UNSIGNED NOT NULL DEFAULT 0,
            score_eoe TINYINT UNSIGNED NOT NULL DEFAULT 0,
            score_aes TINYINT UNSIGNED NOT NULL DEFAULT 0,
            score_lst TINYINT UNSIGNED NOT NULL DEFAULT 0,
            profile_label VARCHAR(120) NOT NULL DEFAULT '',
            answers TEXT NULL,
            PRIMARY KEY (id),
            KEY email (email)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    public static function enqueue_assets() {
        wp_register_style( 'praxisens', PRAXISENS_URL . 'assets/css/style.css', array(), PRAXISENS_VERSION );
        wp_register_script( 'praxisens', PRAXISENS_URL . 'assets/js/main.js', array(), PRAXISENS_VERSION, true );
        wp_localize_script( 'praxisens', 'PraxiSensData', array(
            'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
            'nonce'      => wp_create_nonce( 'praxisens_nonce' ),
            'questions'  => self::questions(),
            'options'    => self::options(),
            'dimensions' => self::dimensions(),
        ) );
    }

    public static function render_shortcode() {
        wp_enqueue_style( 'praxisens' );
        wp_enqueue_script( 'praxisens' );
        ob_start();
        include PRAXISENS_PATH . 'templates/page-intro.php';
        include PRAXISENS_PATH . 'templates/page-questions.php';
        include PRAXISENS_PATH . 'templates/page-results.php';
        return ob_get_clean();
    }

    /**
     * Calcule les scores à partir des réponses {id:value}.
     * Renvoie un tableau de % (0..100) global + par dimension, + palier + profil.
     */
    public static function compute( $answers ) {
        $dims  = array( 'EOE' => array(), 'AES' => array(), 'LST' => array() );
        $all   = array();

        foreach ( self::questions() as $q ) {
            $v = isset( $answers[ $q['id'] ] ) ? (int) $answers[ $q['id'] ] : 0;
            if ( $v < 1 ) { $v = 1; }
            if ( $v > 5 ) { $v = 5; }
            $dims[ $q['dim'] ][] = $v;
            $all[] = $v;
        }

        $pct = function( $values ) {
            $n = count( $values );
            if ( $n === 0 ) { return 0; }
            $sum = array_sum( $values );
            // chaque item 1..5 -> min n, max 5n. Normalisation 0..100.
            return (int) round( ( ( $sum - $n ) / ( 4 * $n ) ) * 100 );
        };

        $score_eoe = $pct( $dims['EOE'] );
        $score_aes = $pct( $dims['AES'] );
        $score_lst = $pct( $dims['LST'] );
        $global    = $pct( $all );

        return array(
            'global'  => $global,
            'EOE'     => $score_eoe,
            'AES'     => $score_aes,
            'LST'     => $score_lst,
            'band'    => self::band( $global ),
            'profile' => self::profile_label( $global ),
        );
    }

    /** Paliers (sur le score global %) : faible <40 · modérée 40-59 · élevée 60-77 · haute ≥78. */
    public static function band( $pct ) {
        if ( $pct >= 78 ) { return 'haute'; }
        if ( $pct >= 60 ) { return 'elevee'; }
        if ( $pct >= 40 ) { return 'moderee'; }
        return 'faible';
    }

    public static function profile_label( $pct ) {
        if ( $pct >= 78 ) { return 'Haute sensibilité marquée'; }
        if ( $pct >= 60 ) { return 'Sensibilité élevée'; }
        if ( $pct >= 40 ) { return 'Sensibilité modérée'; }
        return 'Sensibilité faible';
    }

    public static function band_text( $pct ) {
        if ( $pct >= 78 ) {
            return "Votre profil correspond à une <strong>haute sensibilité marquée</strong>. Vous traitez l'information de façon profonde et percevez finement votre environnement — une richesse qui demande aussi de protéger vos temps de récupération.";
        }
        if ( $pct >= 60 ) {
            return "Une <strong>sensibilité élevée</strong> ressort de vos réponses. Vous êtes nettement réceptif(ve) aux ambiances et aux subtilités, tout en gardant des moments où les stimulations ne vous débordent pas.";
        }
        if ( $pct >= 40 ) {
            return "Votre profil indique une <strong>sensibilité modérée</strong>, équilibrée. Vous percevez les nuances de votre environnement sans en être facilement submergé(e) : ni filtre systématique, ni saturation fréquente.";
        }
        return "Votre profil indique une <strong>sensibilité plutôt faible</strong>. Vous filtrez naturellement les stimulations et restez à l'aise dans des environnements intenses.";
    }
}
