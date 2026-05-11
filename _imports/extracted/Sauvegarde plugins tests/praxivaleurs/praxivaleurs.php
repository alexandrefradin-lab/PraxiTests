<?php
/**
 * Plugin Name: PraxiValeurs
 * Plugin URI:  https://praxis-accompagnement.fr
 * Description: Évaluation des valeurs professionnelles (modèle Schwartz) dans le cadre d'un bilan de compétences.
 * Version:     3.10.0
 * Author:      Praxis Accompagnement
 * Author URI:  https://praxis-accompagnement.fr
 * License:     GPL-2.0+
 * Text Domain: praxivaleurs
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PRAXIVALEURS_VERSION', '3.10.0' );
define( 'PRAXIVALEURS_PATH', plugin_dir_path( __FILE__ ) );
define( 'PRAXIVALEURS_URL', plugin_dir_url( __FILE__ ) );

// Includes
require_once PRAXIVALEURS_PATH . 'includes/class-praxivaleurs.php';
require_once PRAXIVALEURS_PATH . 'includes/class-admin.php';
require_once PRAXIVALEURS_PATH . 'includes/ajax-handlers.php';
require_once PRAXIVALEURS_PATH . 'includes/email-functions.php';

// Activation
register_activation_hook( __FILE__, array( 'PraxiValeurs', 'activate' ) );

// Init front
add_action( 'plugins_loaded', array( 'PraxiValeurs', 'get_instance' ) );

// Init admin
if ( is_admin() ) {
    add_action( 'plugins_loaded', array( 'PraxiValeurs_Admin', 'get_instance' ) );
}
