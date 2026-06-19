<?php
/**
 * PraxiMet – Contrôleur public
 * Enregistre le shortcode [praximet_quiz] et charge les assets
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class PraxiMet_Public {

    public static function init() {
        add_shortcode( 'praximet_quiz', [ __CLASS__, 'render_quiz' ] );
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
    }

    public static function render_quiz() {
        ob_start();
        require PRAXIMET_PATH . 'public/views/quiz.php';
        return ob_get_clean();
    }

    public static function enqueue_assets() {
        global $post;
        if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'praximet_quiz' ) ) {
            return;
        }

        $ver = PRAXIMET_VERSION;

        wp_enqueue_style( 'praximet-quiz', PRAXIMET_URL . 'public/assets/quiz.css', [], $ver );
        wp_enqueue_script( 'praximet-quiz', PRAXIMET_URL . 'public/assets/quiz.js', [], $ver, true );
        wp_enqueue_script( 'praximet-ajax-form', PRAXIMET_URL . 'public/assets/ajax-form.js', [ 'praximet-quiz' ], $ver, true );

        wp_localize_script( 'praximet-ajax-form', 'praximet_ajax', [
            'url'   => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'praximet_submit' ),
        ]);
    }
}
