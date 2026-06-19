<?php
/**
 * Plugin Name: Praxis 360
 * Plugin URI:  https://www.praxis-accompagnement.com/
 * Description: Évaluation 360° multi-évaluateurs sur les soft skills (auto-évaluation + manager, pairs, collaborateurs). Invitations par email, agrégation anonyme et restitution comparative. Conçu pour OVH mutualisé et portable vers PraxiQuest.
 * Version:     1.0.0
 * Author:      Alexandre Fradin — Praxis Accompagnement
 * Author URI:  https://www.praxis-accompagnement.com/
 * License:     GPL-2.0-or-later
 * Text Domain: praxis-360
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Pas d'accès direct.
}

// --- Constantes ---------------------------------------------------------------
define( 'PRAXIS360_VERSION', '1.0.0' );
define( 'PRAXIS360_FILE', __FILE__ );
define( 'PRAXIS360_DIR', plugin_dir_path( __FILE__ ) );
define( 'PRAXIS360_URL', plugin_dir_url( __FILE__ ) );

/**
 * Préfixe des tables — isolé ici pour faciliter un futur portage vers PraxiQuest.
 * Sur une installation WordPress standard : {wp_prefix}p360_
 * Pour PraxiQuest : il suffira de redéfinir ce filtre.
 */
function praxis360_table_prefix() {
    global $wpdb;
    return apply_filters( 'praxis360_table_prefix', $wpdb->prefix . 'p360_' );
}

// --- Includes -----------------------------------------------------------------
require_once PRAXIS360_DIR . 'includes/class-praxis360-db.php';
require_once PRAXIS360_DIR . 'includes/class-praxis360-items.php';
require_once PRAXIS360_DIR . 'includes/class-praxis360-scoring.php';
require_once PRAXIS360_DIR . 'includes/email-functions.php';
require_once PRAXIS360_DIR . 'includes/ajax-handlers.php';
require_once PRAXIS360_DIR . 'includes/class-praxis360-admin.php';
require_once PRAXIS360_DIR . 'includes/class-praxis360.php';

// --- Activation : création des tables -----------------------------------------
register_activation_hook( __FILE__, array( 'Praxis360_DB', 'install' ) );

// --- Boot ---------------------------------------------------------------------
add_action( 'plugins_loaded', array( 'Praxis360', 'instance' ) );
