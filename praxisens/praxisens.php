<?php
/**
 * Plugin Name: PraxiSens — Test d'Hypersensibilité
 * Description: Questionnaire d'hypersensibilité (Sensory Processing Sensitivity, d'après Aron & Aron 1997 / Smolewska 2006). UX Cali : une question par écran, auto-avancement, restitution + email. Compatible PraxiQuest.
 * Version: 1.0.0
 * Author: Praxis Accompagnement
 * Text Domain: praxisens
 * Requires PHP: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PRAXISENS_VERSION', '1.0.0' );
define( 'PRAXISENS_PATH', plugin_dir_path( __FILE__ ) );
define( 'PRAXISENS_URL', plugin_dir_url( __FILE__ ) );

require_once PRAXISENS_PATH . 'includes/class-praxisens.php';
require_once PRAXISENS_PATH . 'includes/ajax-handlers.php';
require_once PRAXISENS_PATH . 'includes/email-functions.php';

register_activation_hook( __FILE__, array( 'Praxisens', 'activate' ) );

add_action( 'plugins_loaded', array( 'Praxisens', 'init' ) );
