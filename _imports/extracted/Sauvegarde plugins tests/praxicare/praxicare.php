<?php
/**
 * Plugin Name: PraxiCare
 * Plugin URI:  https://www.praxis-accompagnement.com
 * Description: Mesure de la souffrance au travail (Karasek + MBI) — Praxis Accompagnement
 * Version:     1.3.7
 * Author:      Praxis Accompagnement
 * License:     GPL-2.0+
 * Text Domain: praxicare
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'PRAXICARE_VERSION' ) ) define( 'PRAXICARE_VERSION', '1.3.7' );
if ( ! defined( 'PRAXICARE_PATH' ) )    define( 'PRAXICARE_PATH', plugin_dir_path( __FILE__ ) );
if ( ! defined( 'PRAXICARE_URL' ) )     define( 'PRAXICARE_URL',  plugin_dir_url( __FILE__ ) );

require_once PRAXICARE_PATH . 'includes/class-praxicare.php';
require_once PRAXICARE_PATH . 'includes/ajax-handlers.php';
require_once PRAXICARE_PATH . 'includes/email-functions.php';
require_once PRAXICARE_PATH . 'includes/relance-functions.php';

register_activation_hook( __FILE__, array( 'PraxiCare', 'activate' ) );
register_deactivation_hook( __FILE__, 'praxicare_unschedule_cron' );

$praxicare = new PraxiCare();
$praxicare->init();
