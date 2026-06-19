<?php
if ( ! defined('ABSPATH') ) exit;
if ( ! current_user_can('manage_options') ) wp_die('Accès refusé.');
if ( ! isset($_GET['pp_export_csv']) || ! wp_verify_nonce($_GET['_wpnonce'], 'pp_export_csv') ) return;

$filters = array(
    'archetype' => sanitize_text_field( $_GET['archetype'] ?? '' ),
    'ds_eleve'  => ! empty( $_GET['ds_eleve'] ),
    'search'    => sanitize_text_field( $_GET['search'] ?? '' ),
);

$rows = PP_DB::export_csv( $filters );

$filename = 'profils-personnalite-' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
echo "\xEF\xBB\xBF"; // BOM UTF-8 pour Excel

$out = fopen('php://output', 'w');

// En-têtes CSV
fputcsv($out, array(
    'ID','Token','Prénom','Email','Archétype',
    'O%','C%','E%','A%','N%','DS%',
    'O_T','C_T','E_T','A_T','N_T',
    'Date','Consentement','Source',
    'Relance J3','Relance J8','RDV cliqué'
), ';');

foreach ( $rows as $r ) {
    fputcsv($out, array(
        $r['id'], $r['token'], $r['prenom'], $r['email'], $r['archetype_nom'],
        $r['score_O'], $r['score_C'], $r['score_E'], $r['score_A'], $r['score_N'], $r['score_DS'],
        $r['score_O_T'], $r['score_C_T'], $r['score_E_T'], $r['score_A_T'], $r['score_N_T'],
        $r['date_soumis'], $r['consentement'] ? 'Oui' : 'Non', $r['source'],
        $r['relance_3j'] ? 'Envoyée' : 'Non', $r['relance_8j'] ? 'Envoyée' : 'Non',
        $r['rdv_clique'] ? 'Oui' : 'Non'
    ), ';');
}

fclose($out);
exit;
