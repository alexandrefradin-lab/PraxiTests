<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PE_Shortcode {

    public static function init() {
        add_shortcode( 'praxiemo', array( __CLASS__, 'render' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
    }

    public static function enqueue() {
        global $post;
        if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'praxiemo' ) ) return;

        wp_enqueue_style(
            'praxiemo-style',
            PEMO_PLUGIN_URL . 'assets/css/style.css',
            array(),
            PEMO_VERSION
        );

        wp_enqueue_script(
            'praxiemo-main',
            PEMO_PLUGIN_URL . 'assets/js/main.js',
            array(),
            PEMO_VERSION,
            true
        );

        // Données injectées pour le JS
        $questions = PE_Calculator::get_questions_json();
        $familles  = PE_Calculator::get_familles();

        // Construire les transitions de familles
        $transitions = array(
            2 => array(
                'label' => '⚡ Régulation émotionnelle',
                'text'  => 'Vous avez exploré votre conscience intérieure. Place maintenant à la façon dont vous gérez vos émotions au quotidien — stress, colère, élan, équilibre.',
            ),
            3 => array(
                'label' => '🤝 Relations & Communication',
                'text'  => 'Vos émotions ne vivent pas en silo. Cette partie explore la qualité de votre présence aux autres : empathie, tact, assertivité, diversité.',
            ),
            4 => array(
                'label' => '🎯 Leadership émotionnel',
                'text'  => 'Avant-dernière étape. Comment mobilisez-vous les autres ? Comment traversez-vous les conflits ? C\'est ce que nous allons mesurer maintenant.',
            ),
            5 => array(
                'label' => '🪞 Quelques dernières questions',
                'text'  => 'Presque terminé ! Ces dernières questions n\'ont pas de bonne ou mauvaise réponse — répondez le plus spontanément possible.',
            ),
        );

        wp_localize_script( 'praxiemo-main', 'PEMO_DATA', array(
            'ajax_url'    => admin_url( 'admin-ajax.php' ),
            'nonce'       => wp_create_nonce( 'pemo_nonce' ),
            'questions'   => $questions,
            'familles'    => $familles,
            'transitions' => $transitions,
            'labels'      => array(
                'jamais'   => 'Jamais',
                'rarement' => 'Rarement',
                'souvent'  => 'Souvent',
                'toujours' => 'Toujours',
                'retour'   => '← Question précédente',
                'continuer'=> 'Continuer →',
            ),
        ) );
    }

    public static function render( $atts ) {
        ob_start();
        include PEMO_PLUGIN_DIR . 'templates/page-intro.php';
        return ob_get_clean();
    }

    /**
     * Retourne l'URL de la politique de confidentialité dans l'ordre de priorité :
     *  1. Page générée automatiquement par le plugin (PE_Privacy)
     *  2. Option plugin `pemo_privacy_url` (saisie manuelle dans Réglages)
     *  3. Page de confidentialité WordPress native (Réglages → Confidentialité)
     *  4. Fallback : slug par défaut /praxiemo-politique-confidentialite
     *
     * @return string URL absolue
     */
    public static function get_privacy_url() {
        // 1. Page auto-générée par le plugin (source la plus fiable)
        $plugin_page_url = PE_Privacy::get_url();
        if ( ! empty( $plugin_page_url ) ) {
            return esc_url( $plugin_page_url );
        }

        // 2. URL saisie manuellement dans Réglages
        $manual_url = get_option( 'pemo_privacy_url', '' );
        if ( ! empty( $manual_url ) ) {
            return esc_url( $manual_url );
        }

        // 3. Page de confidentialité WordPress native
        $wp_url = get_privacy_policy_url();
        if ( ! empty( $wp_url ) ) {
            return esc_url( $wp_url );
        }

        // 4. Fallback slug
        return esc_url( home_url( '/' . PE_Privacy::PAGE_SLUG ) );
    }
}
