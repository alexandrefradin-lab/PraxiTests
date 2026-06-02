<?php
/**
 * Plugin Name:       PraxiMum
 * Plugin URI:        https://praximum.fr
 * Description:       Test Big Five — 128 questions, 30 facettes, archétypes, rapport PDF, page profil publique, mode batch, dashboard admin.
 * Version:           1.4.1
 * Author:            Alexandre Fradin
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       plugin-personnalite
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Tested up to:      6.7
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PP_VERSION',    '1.4.1' );
define( 'PP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// ── Chargement des classes ────────────────────────────────────────────────────
require_once PP_PLUGIN_DIR . 'includes/class-pp-logger.php';
require_once PP_PLUGIN_DIR . 'includes/class-pp-security.php';
require_once PP_PLUGIN_DIR . 'includes/class-pp-db.php';
require_once PP_PLUGIN_DIR . 'includes/class-pp-questions.php';
require_once PP_PLUGIN_DIR . 'includes/class-pp-calculator.php';
require_once PP_PLUGIN_DIR . 'includes/class-pp-archetypes.php';
require_once PP_PLUGIN_DIR . 'includes/class-pp-mailer.php';
require_once PP_PLUGIN_DIR . 'includes/class-pp-shortcode.php';
require_once PP_PLUGIN_DIR . 'includes/class-pp-relances.php';
require_once PP_PLUGIN_DIR . 'includes/class-pp-public-profil.php';
require_once PP_PLUGIN_DIR . 'includes/class-pp-pdf.php';
require_once PP_PLUGIN_DIR . 'includes/class-pp-batch.php';
require_once PP_PLUGIN_DIR . 'includes/class-pp-codes.php';
require_once PP_PLUGIN_DIR . 'includes/class-pp-rgpd.php';
require_once PP_PLUGIN_DIR . 'includes/class-pp-health.php';
require_once PP_PLUGIN_DIR . 'admin/class-pp-admin.php';

// ── Bootstrap ─────────────────────────────────────────────────────────────────
final class Plugin_Personnalite {

    private static bool $booted = false;

    public static function boot(): void {
        if ( self::$booted ) return;
        self::$booted = true;

        register_activation_hook(   __FILE__, [ self::class, 'activate' ] );
        register_deactivation_hook( __FILE__, [ self::class, 'deactivate' ] );

        add_action( 'plugins_loaded',      [ self::class, 'init' ] );
        add_action( 'wp_enqueue_scripts',  [ self::class, 'enqueue_front' ] );
        add_action( 'wp_head',             [ self::class, 'inject_brand_css' ] );
    }

    // ── Activation ────────────────────────────────────────────────────────────
    public static function activate(): void {
        PP_Logger::install();
        PP_DB::install();
        PP_Batch::install();
        PP_Codes::install();
        PP_Public_Profil::add_rewrite();
        PP_RGPD::add_rewrite();
        flush_rewrite_rules();
        PP_Logger::info( 'plugin', 'Activation v' . PP_VERSION );
    }

    // ── Désactivation ─────────────────────────────────────────────────────────
    public static function deactivate(): void {
        flush_rewrite_rules();
        // Dé-planifier via les méthodes des classes concernées
        PP_Relances::unschedule();
        $purge_ts = wp_next_scheduled( 'pp_purge_expired' );
        if ( $purge_ts ) wp_unschedule_event( $purge_ts, 'pp_purge_expired' );
    }

    // ── Initialisation ────────────────────────────────────────────────────────
    public static function init(): void {
        // Migration BDD silencieuse si la version a changé
        if ( get_option( 'pp_db_version' ) !== PP_DB::VERSION ) {
            PP_DB::install();
            PP_Batch::install();
            PP_Logger::info( 'plugin', 'Migration BDD vers ' . PP_DB::VERSION );
        }

        PP_Shortcode::init();
        PP_Admin::init();
        PP_Mailer::init();
        PP_Relances::init();
        PP_Public_Profil::init();
        PP_Batch::init();
        PP_Security::init();
        PP_Codes::init();
        PP_RGPD::init();
    }

    // ── Assets front — chargés UNIQUEMENT sur les pages du plugin ─────────────
    public static function enqueue_front(): void {
        if ( ! self::is_pp_page() ) return;

        wp_enqueue_style(
            'pp-style',
            PP_PLUGIN_URL . 'assets/css/front.css',
            [],
            PP_VERSION
        );

        // html2canvas — CDN externe, chargé seulement si page profil ou test
        wp_register_script(
            'html2canvas',
            'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js',
            [],
            '1.4.1',
            true
        );

        wp_enqueue_script(
            'pp-script',
            PP_PLUGIN_URL . 'assets/js/front.js',
            [ 'jquery', 'html2canvas' ],
            PP_VERSION,
            true
        );

        wp_enqueue_script(
            'pp-pdf-client',
            PP_PLUGIN_URL . 'assets/js/pp-pdf-client.js',
            [],
            PP_VERSION . '.' . filemtime( PP_PLUGIN_DIR . 'assets/js/pp-pdf-client.js' ),
            true
        );

        // Nonce généré une seule fois par chargement de page
        wp_localize_script( 'pp-script', 'PP_AJAX', [
            'url'   => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'pp_nonce' ),
        ] );
    }

    // ── CSS variables marque blanche — conditionnel ────────────────────────────
    public static function inject_brand_css(): void {
        if ( ! self::is_pp_page() ) return;

        $c1 = sanitize_hex_color( get_option( 'pp_color_primary',   '#E8541A' ) ) ?: '#E8541A';
        $c2 = sanitize_hex_color( get_option( 'pp_color_secondary', '#1E2A3A' ) ) ?: '#1E2A3A';

        // Aucun echo sans escape — les valeurs sont déjà validées par sanitize_hex_color
        printf( "<style>:root{--pp-c1:%s;--pp-c2:%s;}</style>\n",
            esc_attr( $c1 ),
            esc_attr( $c2 )
        );
    }

    /**
     * Détecte si la page courante est une page du plugin.
     * Utilisé pour le chargement conditionnel des assets.
     */
    public static function is_pp_page(): bool {
        // Rewrite rules du plugin actives
        if ( get_query_var( 'pp_token',        '' ) ) return true;
        if ( get_query_var( 'pp_equipe',       '' ) ) return true;
        if ( get_query_var( 'pp_delete_token', '' ) ) return true;
        if ( get_query_var( 'pp_export_token', '' ) ) return true;

        // Page contenant un shortcode du plugin
        global $post;
        if ( $post instanceof WP_Post ) {
            if ( has_shortcode( $post->post_content, 'test_personnalite' ) )      return true;
            if ( has_shortcode( $post->post_content, 'test_personnalite_solo' ) ) return true;
            if ( has_shortcode( $post->post_content, 'pp_profil' ) )              return true;
            if ( has_shortcode( $post->post_content, 'pp_politique' ) )           return true;
        }

        return false;
    }
}

Plugin_Personnalite::boot();
