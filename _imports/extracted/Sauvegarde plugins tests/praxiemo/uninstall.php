<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

global $wpdb;

$tables = array(
    $wpdb->prefix . 'pemo_sessions',
    $wpdb->prefix . 'pemo_results',
    $wpdb->prefix . 'pemo_logs',
);

foreach ( $tables as $table ) {
    $wpdb->query( "DROP TABLE IF EXISTS `{$table}`" ); // phpcs:ignore
}

$options = array(
    'pemo_smtp_host', 'pemo_smtp_user', 'pemo_smtp_pass',
    'pemo_smtp_port', 'pemo_smtp_secure', 'pemo_admin_email',
    'pemo_rdv_url', 'pemo_site_name', 'pemo_color_primary',
    'pemo_relances_actives',
);
foreach ( $options as $opt ) {
    delete_option( $opt );
}
