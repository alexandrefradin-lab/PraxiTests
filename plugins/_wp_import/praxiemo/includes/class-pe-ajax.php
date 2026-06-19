<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PE_Ajax {

    public static function init() {
        add_action( 'wp_ajax_nopriv_pemo_start',  array( __CLASS__, 'handle_start' ) );
        add_action( 'wp_ajax_pemo_start',         array( __CLASS__, 'handle_start' ) );
        add_action( 'wp_ajax_nopriv_pemo_submit', array( __CLASS__, 'handle_submit' ) );
        add_action( 'wp_ajax_pemo_submit',        array( __CLASS__, 'handle_submit' ) );
    }

    /**
     * Crée la session et retourne le token.
     */
    public static function handle_start() {
        check_ajax_referer( 'pemo_nonce', 'nonce' );

        $prenom = sanitize_text_field( wp_unslash( $_POST['prenom'] ?? '' ) );
        $email  = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );

        if ( empty( $prenom ) || empty( $email ) || ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => 'Prénom et email valides requis.' ), 400 );
        }

        $ip      = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' );
        $session = PE_DB::create_session( $prenom, $email, $ip );

        PE_Logger::info( 'ajax', 'Session créée', array( 'email' => $email, 'token' => $session['token'] ) );

        wp_send_json_success( array(
            'token'  => $session['token'],
            'prenom' => $prenom,
        ) );
    }

    /**
     * Reçoit les 80 réponses, calcule les scores, sauvegarde, envoie l'email.
     */
    public static function handle_submit() {
        check_ajax_referer( 'pemo_nonce', 'nonce' );

        $token   = sanitize_text_field( wp_unslash( $_POST['token'] ?? '' ) );
        $answers = $_POST['answers'] ?? array(); // phpcs:ignore

        if ( empty( $token ) ) {
            wp_send_json_error( array( 'message' => 'Token manquant.' ), 400 );
        }

        $session = PE_DB::get_session_by_token( $token );
        if ( ! $session ) {
            wp_send_json_error( array( 'message' => 'Session introuvable.' ), 404 );
        }

        if ( $session->completed_at ) {
            wp_send_json_error( array( 'message' => 'Session déjà complétée.' ), 409 );
        }

        // Nettoyage des réponses (80 IE + 6 désirabilité = 86 au total)
        $clean_answers = array();
        for ( $i = 0; $i < 86; $i++ ) {
            $val = isset( $answers[ $i ] ) ? absint( $answers[ $i ] ) : 1;
            $clean_answers[ $i ] = max( 1, min( 4, $val ) );
        }

        // Calcul IE (indices 0-79)
        $results = PE_Calculator::compute( $clean_answers );

        // Calcul désirabilité sociale (indices 80-85)
        $score_desirabilite = PE_Calculator::compute_desirabilite( $clean_answers );

        // Sauvegarde
        PE_DB::save_results( $session->id, $results['dim_scores'], $results['score_global'], $score_desirabilite );
        PE_DB::complete_session( $session->id );

        // Email
        $sent = PE_Mailer::envoyer_resultats( $session->prenom, $session->email, $results );
        if ( ! $sent ) {
            PE_Logger::error( 'ajax', 'Échec email résultats', array(
                'session_id' => $session->id,
                'email'      => $session->email,
            ) );
        }

        // Préparer la réponse JSON pour le JS
        $dims_data = array();
        $dims      = PE_Calculator::get_dimensions();
        $familles  = PE_Calculator::get_familles();

        foreach ( $dims as $dim_id => $dim ) {
            $score          = $results['dim_scores'][ $dim_id ];
            $dims_data[]    = array(
                'id'          => $dim_id,
                'label'       => $dim['label'],
                'famille_id'  => $dim['famille'],
                'famille'     => $familles[ $dim['famille'] ]['label'],
                'emoji'       => $familles[ $dim['famille'] ]['emoji'],
                'score'          => $score,
                'max'            => 20,
                'pct'            => round( ( max( 0, $score - 5 ) / 15 ) * 100 ),
                'interpretation' => PE_Calculator::interpret_dim( $score ),
                'description'    => $dim['description'] ?? '',
            );
        }

        // Top forces & développement labels
        $top_forces_labels = array();
        $top_dev_labels    = array();
        foreach ( $results['top_forces'] as $dim_id ) {
            $top_forces_labels[] = $dims[ $dim_id ]['label'];
        }
        foreach ( $results['top_dev'] as $dim_id ) {
            $top_dev_labels[] = $dims[ $dim_id ]['label'];
        }

        // Recommandations par dimension
        foreach ( $dims_data as &$d ) {
            $reco = PE_Calculator::get_recommendations( $d['id'], $d['score'] );
            $d['reco_niveau']  = $reco['niveau'];
            $d['reco_actions'] = $reco['actions'];
        }
        unset( $d );

        // Historique — détecter un passage précédent
        $history = null;
        if ( ! empty( $session->email ) ) {
            $previous = PE_History::get_previous( $session->email, $session->id );
            if ( $previous ) {
                $history = PE_History::build_comparison(
                    $results['dim_scores'],
                    $results['score_global'],
                    $previous
                );
            }
        }

        // Lien PDF — endpoint front-end (pas de wp-admin)
        $pdf_url = PE_PDF::get_url( $session->token, wp_create_nonce( 'pemo_nonce' ) );

        $desirabilite_interp = PE_Calculator::interpret_desirabilite( $score_desirabilite );

        wp_send_json_success( array(
            'score_global'        => $results['score_global'],
            'score_max'           => 320,
            'niveau_qe'           => $results['niveau_qe'],
            'phrase_qe'           => $results['phrase_qe'],
            'dims'                => $dims_data,
            'top_forces'          => $top_forces_labels,
            'top_dev'             => $top_dev_labels,
            'rdv_url'             => get_option( 'pemo_rdv_url', home_url( '/contact' ) ),
            'email_sent'          => $sent,
            'pdf_url'             => $pdf_url,
            'history'             => $history,
            'desirabilite_score'  => $score_desirabilite,
            'desirabilite_alerte' => $desirabilite_interp['alerte'],
            'desirabilite_msg'    => $desirabilite_interp['message'],
        ) );
    }
}
