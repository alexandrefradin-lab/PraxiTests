<?php
/**
 * PraxiMet – Contrôleur Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class PraxiMet_Admin {

    public static function init() {
        add_action( 'admin_menu',            [ __CLASS__, 'register_menus' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
        add_action( 'admin_post_praximet_update_statut',   [ __CLASS__, 'handle_update_statut' ] );
        add_action( 'admin_post_praximet_save_settings',   [ __CLASS__, 'handle_save_settings' ] );
        add_action( 'admin_post_praximet_export_csv',      [ __CLASS__, 'handle_export_csv' ] );
        add_action( 'wp_ajax_praximet_update_statut_ajax', [ __CLASS__, 'handle_statut_ajax' ] );
        add_action( 'wp_ajax_praximet_test_smtp',           [ __CLASS__, 'handle_test_smtp' ] );
        add_action( 'admin_post_praximet_supprimer_lead',   [ __CLASS__, 'handle_supprimer_lead' ] );
        add_action( 'wp_ajax_praximet_supprimer_lead',      [ __CLASS__, 'handle_supprimer_lead_ajax' ] );
    }

    public static function register_menus() {
        add_menu_page( 'PraxiMet', 'PraxiMet', 'manage_options', 'praximet-leads', [ __CLASS__, 'page_leads' ], 'dashicons-groups', 30 );
        add_submenu_page( 'praximet-leads', 'Leads', 'Leads', 'manage_options', 'praximet-leads', [ __CLASS__, 'page_leads' ] );
        add_submenu_page( 'praximet-leads', 'Paramètres', 'Paramètres', 'manage_options', 'praximet-settings', [ __CLASS__, 'page_settings' ] );
    }

    public static function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'praximet' ) === false ) return;
        wp_enqueue_style( 'praximet-admin', PRAXIMET_URL . 'admin/assets/admin.css', [], PRAXIMET_VERSION );
        wp_enqueue_script( 'praximet-admin', PRAXIMET_URL . 'admin/assets/admin.js', [], PRAXIMET_VERSION, true );
        wp_localize_script( 'praximet-admin', 'praximet_admin', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'praximet_admin_nonce' ),
        ]);
    }

    public static function page_leads() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Accès refusé.' );
        $lead_id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
        if ( $lead_id > 0 ) { require PRAXIMET_PATH . 'admin/views/lead-detail.php'; return; }
        require PRAXIMET_PATH . 'admin/views/dashboard.php';
    }

    public static function page_settings() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Accès refusé.' );
        require PRAXIMET_PATH . 'admin/views/settings.php';
    }

    public static function handle_update_statut() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Accès refusé.' );
        check_admin_referer( 'praximet_update_statut' );
        $id = isset( $_POST['lead_id'] ) ? (int) $_POST['lead_id'] : 0;
        $statut = sanitize_key( $_POST['statut'] ?? '' );
        if ( $id && $statut ) {
            require_once PRAXIMET_PATH . 'includes/class-lead-manager.php';
            PraxiMet_Lead_Manager::mettre_a_jour_statut( $id, $statut );
            if ( in_array( $statut, ['rdv_pris', 'archive'], true ) ) {
                require_once PRAXIMET_PATH . 'includes/class-cron-manager.php';
                PraxiMet_Cron_Manager::annuler_relance( $id );
            }
        }
        wp_safe_redirect( admin_url( 'admin.php?page=praximet-leads&id=' . $id . '&updated=1' ) );
        exit;
    }

    public static function handle_statut_ajax() {
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        check_ajax_referer( 'praximet_admin_nonce', 'nonce' );
        $id = isset( $_POST['lead_id'] ) ? (int) $_POST['lead_id'] : 0;
        $statut = sanitize_key( $_POST['statut'] ?? '' );
        if ( ! $id || ! $statut ) wp_send_json_error( ['message' => 'Données invalides.'] );
        require_once PRAXIMET_PATH . 'includes/class-lead-manager.php';
        $ok = PraxiMet_Lead_Manager::mettre_a_jour_statut( $id, $statut );
        if ( $ok && in_array( $statut, ['rdv_pris', 'archive'], true ) ) {
            require_once PRAXIMET_PATH . 'includes/class-cron-manager.php';
            PraxiMet_Cron_Manager::annuler_relance( $id );
        }
        $ok ? wp_send_json_success() : wp_send_json_error( ['message' => 'Erreur mise à jour.'] );
    }

    public static function handle_save_settings() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Accès refusé.' );
        check_admin_referer( 'praximet_save_settings' );
        update_option( 'praximet_calendly_url',     esc_url_raw( wp_unslash( $_POST['praximet_calendly_url'] ?? '' ) ) );
        update_option( 'praximet_email_conseiller', sanitize_email( wp_unslash( $_POST['praximet_email_conseiller'] ?? '' ) ) );
        update_option( 'praximet_delai_relance',    max( 1, (int) ( $_POST['praximet_delai_relance'] ?? 48 ) ) );

        // SMTP
        update_option( 'praximet_smtp_host',     sanitize_text_field( wp_unslash( $_POST['praximet_smtp_host']     ?? '' ) ) );
        update_option( 'praximet_smtp_port',     (int) ( $_POST['praximet_smtp_port'] ?? 465 ) );
        update_option( 'praximet_smtp_user',     sanitize_email( wp_unslash( $_POST['praximet_smtp_user']          ?? '' ) ) );
        update_option( 'praximet_smtp_from',     sanitize_email( wp_unslash( $_POST['praximet_smtp_from']          ?? '' ) ) );
        update_option( 'praximet_smtp_secure',   sanitize_key( wp_unslash( $_POST['praximet_smtp_secure']          ?? 'ssl' ) ) );
        // Mot de passe : ne sauvegarder que si renseigné (champ non vide)
        $smtp_pass = wp_unslash( $_POST['praximet_smtp_pass'] ?? '' );
        if ( ! empty( $smtp_pass ) ) {
            update_option( 'praximet_smtp_pass', $smtp_pass );
        }

        wp_safe_redirect( admin_url( 'admin.php?page=praximet-settings&saved=1' ) );
        exit;
    }

    public static function handle_export_csv() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Accès refusé.' );
        check_admin_referer( 'praximet_export_csv' );
        require_once PRAXIMET_PATH . 'includes/class-lead-manager.php';
        $leads = PraxiMet_Lead_Manager::get_leads( [ 'limit' => 9999 ] );
        header( 'Content-Type: text/csv; charset=UTF-8' );
        header( 'Content-Disposition: attachment; filename="praximet-leads-' . date('Y-m-d') . '.csv"' );
        header( 'Pragma: no-cache' );
        $out = fopen( 'php://output', 'w' );
        fputs( $out, "\xEF\xBB\xBF" );
        fputcsv( $out, ['ID','Prénom','Nom','Email','Code RIASEC','R','I','A','S','E','C','Statut','RDV pris','Relance','Source','Date'], ';' );
        foreach ( $leads as $lead ) {
            fputcsv( $out, [
                $lead['id'], $lead['prenom'], $lead['nom'], $lead['email'],
                $lead['code_riasec'], $lead['score_r'], $lead['score_i'], $lead['score_a'],
                $lead['score_s'], $lead['score_e'], $lead['score_c'], $lead['statut'],
                $lead['rdv_pris'] ? 'Oui' : 'Non', $lead['relance_envoyee'] ? 'Oui' : 'Non',
                $lead['source_page'], $lead['created_at'],
            ], ';' );
        }
        fclose( $out );
        exit;
    }

    public static function handle_test_smtp() {
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        check_ajax_referer( 'praximet_admin_nonce', 'nonce' );

        $to = sanitize_email( $_POST['to'] ?? get_option('admin_email') );

        // Forcer le SMTP PraxiMet pour ce test
        do_action( 'praximet_before_mail' );

        $sent = wp_mail(
            $to,
            '[PraxiMet] Test de configuration email',
            '<p>Bonjour,</p><p>Si vous recevez cet email, la configuration SMTP de PraxiMet fonctionne correctement.</p><p>— PraxiMet</p>',
            [ 'Content-Type: text/html; charset=UTF-8' ]
        );

        $sent
            ? wp_send_json_success( ['message' => 'Email envoyé à ' . $to] )
            : wp_send_json_error(   ['message' => 'Échec d\'envoi. Vérifiez vos paramètres SMTP.'] );
    }

    public static function handle_supprimer_lead() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Accès refusé.' );
        check_admin_referer( 'praximet_supprimer_lead' );
        $id = isset( $_POST['lead_id'] ) ? (int) $_POST['lead_id'] : 0;
        if ( $id ) {
            require_once PRAXIMET_PATH . 'includes/class-lead-manager.php';
            PraxiMet_Lead_Manager::supprimer( $id );
        }
        wp_safe_redirect( admin_url( 'admin.php?page=praximet-leads&deleted=1' ) );
        exit;
    }

    public static function handle_supprimer_lead_ajax() {
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        check_ajax_referer( 'praximet_admin_nonce', 'nonce' );
        $id = isset( $_POST['lead_id'] ) ? (int) $_POST['lead_id'] : 0;
        if ( ! $id ) wp_send_json_error( ['message' => 'ID invalide.'] );
        require_once PRAXIMET_PATH . 'includes/class-lead-manager.php';
        $ok = PraxiMet_Lead_Manager::supprimer( $id );
        $ok ? wp_send_json_success() : wp_send_json_error( ['message' => 'Erreur suppression.'] );
    }

    public static function statut_badge( string $statut ) {
        $map = [
            'nouveau'  => [ 'label' => 'Nouveau',  'color' => '#2271b1' ],
            'contacte' => [ 'label' => 'Contacté', 'color' => '#996800' ],
            'rdv_pris' => [ 'label' => 'RDV pris', 'color' => '#00834e' ],
            'converti' => [ 'label' => 'Converti', 'color' => '#1e3a5f' ],
            'archive'  => [ 'label' => 'Archivé',  'color' => '#888'    ],
        ];
        return $map[ $statut ] ?? [ 'label' => $statut, 'color' => '#888' ];
    }
}
