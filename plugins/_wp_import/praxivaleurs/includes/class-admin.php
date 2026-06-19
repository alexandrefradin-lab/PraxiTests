<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PraxiValeurs_Admin {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu',            array( $this, 'register_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'wp_ajax_pv_admin_get_sessions',   array( $this, 'ajax_get_sessions' ) );
        add_action( 'wp_ajax_pv_admin_delete_session', array( $this, 'ajax_delete_session' ) );
        add_action( 'wp_ajax_pv_admin_export_csv',     array( $this, 'ajax_export_csv' ) );
        add_action( 'wp_ajax_pv_admin_save_settings',  array( $this, 'ajax_save_settings' ) );
        add_action( 'wp_ajax_pv_admin_get_stats',      array( $this, 'ajax_get_stats' ) );
        add_action( 'wp_ajax_pv_admin_resend_email',   array( $this, 'ajax_resend_email' ) );
    }

    public function register_menu() {
        add_menu_page(
            'PraxiValeurs',
            'PraxiValeurs',
            'manage_options',
            'praxivaleurs',
            array( $this, 'render_page' ),
            'dashicons-chart-bar',
            30
        );
    }

    public function enqueue_admin_assets( $hook ) {
        // Charger sur toutes les pages du plugin (toplevel_page_praxivaleurs ou similaire)
        if ( strpos( $hook, 'praxivaleurs' ) === false && $hook !== 'toplevel_page_praxivaleurs' ) return;
        wp_enqueue_style( 'praxivaleurs-admin', PRAXIVALEURS_URL . 'assets/css/admin.css', array(), PRAXIVALEURS_VERSION );
        wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', array(), '4.4.0', true );
        wp_enqueue_script( 'praxivaleurs-admin-js', PRAXIVALEURS_URL . 'assets/js/admin.js', array('chart-js'), PRAXIVALEURS_VERSION, true );
        wp_localize_script( 'praxivaleurs-admin-js', 'pvAdmin', array(
            'ajax_url'   => admin_url( 'admin-ajax.php' ),
            'nonce'      => wp_create_nonce( 'pv_admin_nonce' ),
            'dimensions' => PraxiValeurs::get_dimensions(),
            'mapping'    => PraxiValeurs::get_mapping(),
            'settings'   => array(
                'consultant_email' => get_option( 'praxivaleurs_consultant_email', get_option('admin_email') ),
                'smtp_host'        => get_option( 'praxivaleurs_smtp_host', 'ssl0.ovh.net' ),
                'smtp_user'        => get_option( 'praxivaleurs_smtp_user', '' ),
            ),
        ));
    }

    public function render_page() {
        include PRAXIVALEURS_PATH . 'templates/admin/page-admin.php';
    }

    public function ajax_get_sessions() {
        check_ajax_referer( 'pv_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        global $wpdb;
        $table  = $wpdb->prefix . 'praxivaleurs_sessions';
        $search = sanitize_text_field( $_POST['search'] ?? '' );
        $page   = max( 1, absint( $_POST['page'] ?? 1 ) );
        $per    = 20;
        $offset = ( $page - 1 ) * $per;

        if ( $search ) {
            $like   = '%' . $wpdb->esc_like( $search ) . '%';
            $total  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE prenom LIKE %s OR email LIKE %s", $like, $like ) );
            $rows   = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE prenom LIKE %s OR email LIKE %s ORDER BY created_at DESC LIMIT %d OFFSET %d", $like, $like, $per, $offset ) );
        } else {
            $total  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
            $rows   = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table ORDER BY created_at DESC LIMIT %d OFFSET %d", $per, $offset ) );
        }

        $sessions = array();
        $dims     = PraxiValeurs::get_dimensions();
        foreach ( $rows as $row ) {
            $top5_raw    = json_decode( $row->top5, true ) ?: array();
            $scores_raw  = json_decode( $row->scores, true ) ?: array();
            // On renvoie les clés de dimensions (pas les labels) pour que le JS puisse les utiliser directement
            $top5_keys   = array_keys( $top5_raw );
            $sessions[] = array(
                'id'         => (int) $row->id,
                'prenom'     => $row->prenom,
                'email'      => $row->email,
                'top5_keys'  => $top5_keys,   // clés brutes pour la modale
                'top5_chips' => array_map( function($k) use ($dims) {  // labels pour les chips
                    return isset($dims[$k]) ? $dims[$k]['icon'] . ' ' . $dims[$k]['label'] : $k;
                }, $top5_keys ),
                'scores'     => $scores_raw,
                'created_at' => date_i18n( 'd/m/Y H:i', strtotime( $row->created_at ) ),
            );
        }

        wp_send_json_success( array(
            'sessions' => $sessions,
            'total'    => $total,
            'pages'    => (int) ceil( $total / $per ),
            'page'     => $page,
        ));
    }

    public function ajax_delete_session() {
        check_ajax_referer( 'pv_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        global $wpdb;
        $id = absint( $_POST['id'] ?? 0 );
        if ( ! $id ) wp_send_json_error();
        $wpdb->delete( $wpdb->prefix . 'praxivaleurs_sessions', array( 'id' => $id ), array( '%d' ) );
        wp_send_json_success();
    }

    public function ajax_export_csv() {
        if ( ! isset($_GET['nonce']) || ! wp_verify_nonce( $_GET['nonce'], 'pv_admin_nonce' ) ) wp_die('Accès refusé');
        if ( ! current_user_can( 'manage_options' ) ) wp_die();

        global $wpdb;
        $rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}praxivaleurs_sessions ORDER BY created_at DESC" );
        $dims = PraxiValeurs::get_dimensions();

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=praxivaleurs-' . date('Y-m-d') . '.csv' );
        $out = fopen( 'php://output', 'w' );
        fprintf( $out, chr(0xEF).chr(0xBB).chr(0xBF) );

        $headers = array( 'ID', 'Prénom', 'Email', 'Date' );
        foreach ( $dims as $dim ) $headers[] = $dim['label'] . ' (%)';
        foreach ( range(1,5) as $i ) $headers[] = 'Top ' . $i;
        fputcsv( $out, $headers, ';' );

        foreach ( $rows as $row ) {
            $scores = json_decode( $row->scores, true ) ?: array();
            $top5   = array_keys( json_decode( $row->top5, true ) ?: array() );
            $line   = array( $row->id, $row->prenom, $row->email, $row->created_at );
            foreach ( $dims as $key => $dim ) $line[] = isset($scores[$key]) ? round(($scores[$key]/6)*100) : 0;
            for ( $i = 0; $i < 5; $i++ ) {
                $k = $top5[$i] ?? '';
                $line[] = isset($dims[$k]) ? $dims[$k]['label'] : '';
            }
            fputcsv( $out, $line, ';' );
        }
        fclose( $out );
        exit;
    }

    public function ajax_get_stats() {
        check_ajax_referer( 'pv_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        global $wpdb;
        $table = $wpdb->prefix . 'praxivaleurs_sessions';

        $total      = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
        $this_week  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)" );
        $this_month = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)" );

        // Fréquence d'apparition dans le Top 5 (% des participants)
        // Beaucoup plus lisible que les scores moyens absolus
        $dims      = PraxiValeurs::get_dimensions();
        $rows_top5 = $wpdb->get_results( "SELECT top5 FROM $table" );
        $freq      = array_fill_keys( array_keys($dims), 0 );

        foreach ( $rows_top5 as $row ) {
            $top5_keys = array_keys( json_decode( $row->top5, true ) ?: array() );
            foreach ( $top5_keys as $k ) {
                if ( isset($freq[$k]) ) $freq[$k]++;
            }
        }

        // Convertir en % de participants (combien de % des gens ont cette valeur dans leur Top 5)
        $total_sessions = $total > 0 ? $total : 1;
        foreach ( $freq as $k => $v ) {
            $freq[$k] = round(($v / $total_sessions) * 100);
        }
        arsort($freq);

        // Valeur dominante = celle qui apparaît le plus souvent dans les Top 5
        $top_dim = array_key_first($freq);

        $evolution = $wpdb->get_results(
            "SELECT DATE(created_at) as jour, COUNT(*) as nb FROM $table
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY DATE(created_at) ORDER BY jour ASC"
        );

        wp_send_json_success( array(
            'total'      => $total,
            'this_week'  => $this_week,
            'this_month' => $this_month,
            'freq'       => $freq,
            'top_dim'    => $top_dim,
            'evolution'  => $evolution,
        ));
    }

    public function ajax_save_settings() {
        check_ajax_referer( 'pv_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        $fields = array( 'consultant_email', 'smtp_host', 'smtp_user', 'smtp_pass' );
        foreach ( $fields as $f ) {
            $val = sanitize_text_field( $_POST[$f] ?? '' );
            if ( $f === 'consultant_email' ) $val = sanitize_email( $_POST[$f] ?? '' );
            if ( $val ) update_option( 'praxivaleurs_' . $f, $val );
        }
        wp_send_json_success();
    }

    public function ajax_resend_email() {
        check_ajax_referer( 'pv_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $id = absint( $_POST['session_id'] ?? 0 );
        if ( ! $id ) wp_send_json_error( array('message' => 'ID invalide.') );

        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}praxivaleurs_sessions WHERE id = %d", $id
        ));

        if ( ! $row ) wp_send_json_error( array('message' => 'Participant introuvable.') );

        $top5   = json_decode( $row->top5,   true ) ?: array();
        $scores = json_decode( $row->scores, true ) ?: array();

        praxivaleurs_send_user_email( $row->prenom, $row->email, $top5, $scores );

        wp_send_json_success( array('message' => 'Email renvoyé à ' . $row->email) );
    }
}
