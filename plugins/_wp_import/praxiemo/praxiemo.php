<?php
/**
 * Plugin Name:  PraxiEmo — Test Intelligence Émotionnelle
 * Plugin URI:   https://www.praxis-accompagnement.com
 * Description:  Évaluation de l'Intelligence Émotionnelle en 16 dimensions (80 questions, Likert 4 points). Inspiré des modèles Bar-On EQ-i et Goleman.
 * Version:      1.1.9
 * Author:       Praxis Accompagnement — Alexandre Fradin
 * Author URI:   https://www.praxis-accompagnement.com
 * License:      GPL-2.0+
 * Text Domain:  praxis-ie
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PEMO_VERSION',    '1.1.9' );
define( 'PEMO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PEMO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PEMO_SLUG',       'praxis-ie' );

require_once PEMO_PLUGIN_DIR . 'includes/class-pe-logger.php';
require_once PEMO_PLUGIN_DIR . 'includes/class-pe-db.php';
require_once PEMO_PLUGIN_DIR . 'includes/class-pe-calculator.php';
require_once PEMO_PLUGIN_DIR . 'includes/class-pe-mailer.php';
require_once PEMO_PLUGIN_DIR . 'includes/class-pe-relances.php';
require_once PEMO_PLUGIN_DIR . 'includes/class-pe-history.php';
require_once PEMO_PLUGIN_DIR . 'includes/class-pe-pdf.php';
require_once PEMO_PLUGIN_DIR . 'includes/class-pe-ajax.php';
require_once PEMO_PLUGIN_DIR . 'includes/class-pe-admin.php';
require_once PEMO_PLUGIN_DIR . 'includes/class-pe-shortcode.php';
require_once PEMO_PLUGIN_DIR . 'includes/class-pe-privacy.php';

register_activation_hook( __FILE__, 'pemo_on_activation' );
register_deactivation_hook( __FILE__, array( 'PE_Relances', 'unschedule' ) );

/**
 * Appelé à l'activation : crée les tables ET la page de confidentialité.
 */
function pemo_on_activation() {
    PE_DB::install();
    PE_Privacy::create_or_update_page();
}

/**
 * Vérifie et crée/met à jour les tables à chaque chargement du plugin.
 * Couvre les mises à jour par upload de ZIP sans désactivation/réactivation.
 */
add_action( 'plugins_loaded', array( 'PE_DB', 'maybe_upgrade' ) );

add_action( 'init', array( 'PE_Mailer', 'init' ) );
add_action( 'init', array( 'PE_Relances', 'init' ) );
add_action( 'init', array( 'PE_Ajax', 'init' ) );
add_action( 'init', array( 'PE_PDF', 'init' ) );
add_action( 'init', array( 'PE_Shortcode', 'init' ) );

if ( is_admin() ) {
    add_action( 'init', array( 'PE_Admin', 'init' ) );
}
