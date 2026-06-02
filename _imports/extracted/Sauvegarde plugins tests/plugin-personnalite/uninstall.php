<?php
/**
 * Désinstallation propre du plugin.
 * Exécuté quand l'admin clique "Supprimer" dans la liste des plugins.
 * Supprime : tables BDD, options WP, fichiers cache PDF, crons.
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

global $wpdb;

// ── 1. Supprimer les tables ────────────────────────────────────────────────
$tables = [
    $wpdb->prefix . 'pp_resultats',
    $wpdb->prefix . 'pp_batch',
    $wpdb->prefix . 'pp_codes',
    $wpdb->prefix . 'pp_logs',
];
foreach ( $tables as $table ) {
    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $wpdb->query( "DROP TABLE IF EXISTS {$table}" );
}

// ── 2. Supprimer toutes les options ───────────────────────────────────────
$options = [
    'pp_db_version', 'pp_admin_email', 'pp_rdv_url', 'pp_politique_url',
    'pp_merci_url', 'pp_test_url', 'pp_site_name_override',
    'pp_color_primary', 'pp_color_secondary', 'pp_logo_url',
    'pp_texte_intro', 'pp_texte_merci', 'pp_texte_rdv_cta',
    'pp_texte_email_intro', 'pp_relances_actives',
    'pp_texte_relance_3j', 'pp_texte_relance_8j',
    'pp_ga_event', 'pp_gtm_event', 'pp_meta_pixel_id', 'pp_meta_event',
    'pp_retention_mois',
];
foreach ( $options as $opt ) {
    delete_option( $opt );
}

// ── 3. Supprimer les transients du plugin ─────────────────────────────────
$wpdb->query(
    "DELETE FROM {$wpdb->options}
     WHERE option_name LIKE '_transient_pp_%'
        OR option_name LIKE '_transient_timeout_pp_%'"
);

// ── 4. Supprimer le dossier cache PDF ────────────────────────────────────
$upload  = wp_upload_dir();
$pdf_dir = trailingslashit( $upload['basedir'] ) . 'pp-rapports/';
if ( is_dir( $pdf_dir ) ) {
    array_map( 'unlink', glob( $pdf_dir . '*.pdf' ) ?: [] );
    @rmdir( $pdf_dir );
}

// ── 5. Dé-planifier les crons ────────────────────────────────────────────
foreach ( [ 'pp_send_relances', 'pp_purge_expired' ] as $hook ) {
    $ts = wp_next_scheduled( $hook );
    if ( $ts ) wp_unschedule_event( $ts, $hook );
}
