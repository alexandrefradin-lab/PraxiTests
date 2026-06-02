<?php
/**
 * Plugin Name:  PraxiMet
 * Plugin URI:   https://praximet.fr
 * Description:  Test RIASEC interactif pour générer des leads qualifiés en bilan de compétences.
 * Version:      2.1.7
 * Author:       Alexandre Fradin
 * Text Domain:  praximet
 * Requires PHP: 7.4
 * Requires at least: 6.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PRAXIMET_VERSION', '2.1.7' );
define( 'PRAXIMET_PATH',    plugin_dir_path( __FILE__ ) );
define( 'PRAXIMET_URL',     plugin_dir_url(  __FILE__ ) );

// ── Activation ───────────────────────────────────────────────────────
register_activation_hook( __FILE__, 'praximet_activate' );
function praximet_activate() {
    // Définir le chemin localement au cas où la constante ne serait pas encore disponible
    $path = plugin_dir_path( __FILE__ );
    require_once $path . 'includes/class-lead-manager.php';
    PraxiMet_Lead_Manager::creer_table();
    add_option( 'praximet_calendly_url',     '' );
    add_option( 'praximet_email_conseiller', get_option('admin_email') );
    add_option( 'praximet_delai_relance',    48 );
}

// ── Désactivation ────────────────────────────────────────────────────
register_deactivation_hook( __FILE__, 'praximet_deactivate' );
function praximet_deactivate() {
    wp_clear_scheduled_hook( 'praximet_relance_cron' );
}

// ── Chargement ───────────────────────────────────────────────────────
add_action( 'plugins_loaded', 'praximet_load' );
function praximet_load() {

    // Core
    require_once PRAXIMET_PATH . 'includes/class-riasec-engine.php';
    require_once PRAXIMET_PATH . 'includes/class-lead-manager.php';
    require_once PRAXIMET_PATH . 'includes/class-email-manager.php';
    require_once PRAXIMET_PATH . 'includes/class-ajax-controller.php';
    require_once PRAXIMET_PATH . 'includes/class-cron-manager.php';

    // Public (shortcode + assets) — chargé en priorité
    require_once PRAXIMET_PATH . 'public/class-public.php';
    PraxiMet_Public::init();

    // AJAX
    PraxiMet_Ajax_Controller::init();
    PraxiMet_Email_Manager::init();

    // Cron
    PraxiMet_Cron_Manager::init();

    // PDF
    require_once PRAXIMET_PATH . 'includes/class-pdf-generator.php';
    PraxiMet_PDF_Generator::init();

    // Admin uniquement
    if ( is_admin() ) {
        require_once PRAXIMET_PATH . 'admin/class-admin.php';
        PraxiMet_Admin::init();
    }
}
