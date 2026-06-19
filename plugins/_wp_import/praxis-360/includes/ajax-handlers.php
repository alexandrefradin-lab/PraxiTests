<?php
/**
 * Gestionnaires AJAX Praxis 360 (frontend passation + actions admin).
 * Tous protégés par nonce + sanitization.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =========================================================================
 *  FRONTEND — passation (accessible aux invités via token, donc nopriv)
 * ========================================================================= */

add_action( 'wp_ajax_p360_save_answer', 'praxis360_ajax_save_answer' );
add_action( 'wp_ajax_nopriv_p360_save_answer', 'praxis360_ajax_save_answer' );
function praxis360_ajax_save_answer() {
    check_ajax_referer( 'p360_passation', 'nonce' );

    $token    = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';
    $item_key = isset( $_POST['item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['item_key'] ) ) : '';
    $raw      = isset( $_POST['value'] ) ? sanitize_text_field( wp_unslash( $_POST['value'] ) ) : '';

    $resp = Praxis360_DB::get_respondent_by_token( $token );
    if ( ! $resp ) {
        wp_send_json_error( array( 'message' => 'Lien invalide.' ), 403 );
    }
    if ( ! in_array( $item_key, Praxis360_Items::all_item_keys(), true ) ) {
        wp_send_json_error( array( 'message' => 'Item inconnu.' ), 400 );
    }

    // "Non observé" => valeur NULL. Sinon entier 1..5.
    if ( 'na' === $raw || '' === $raw ) {
        $value = null;
    } else {
        $value = absint( $raw );
        if ( $value < 1 || $value > 5 ) {
            wp_send_json_error( array( 'message' => 'Valeur invalide.' ), 400 );
        }
    }

    if ( 'invited' === $resp->status ) {
        Praxis360_DB::set_respondent_status( $resp->id, 'in_progress' );
    }
    Praxis360_DB::save_response( $resp->id, $item_key, $value );
    wp_send_json_success();
}

add_action( 'wp_ajax_p360_save_open', 'praxis360_ajax_save_open' );
add_action( 'wp_ajax_nopriv_p360_save_open', 'praxis360_ajax_save_open' );
function praxis360_ajax_save_open() {
    check_ajax_referer( 'p360_passation', 'nonce' );

    $token = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';
    $qkey  = isset( $_POST['question_key'] ) ? sanitize_text_field( wp_unslash( $_POST['question_key'] ) ) : '';
    $text  = isset( $_POST['text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['text'] ) ) : '';

    $resp = Praxis360_DB::get_respondent_by_token( $token );
    if ( ! $resp ) {
        wp_send_json_error( array( 'message' => 'Lien invalide.' ), 403 );
    }
    if ( ! array_key_exists( $qkey, Praxis360_Items::open_questions() ) ) {
        wp_send_json_error( array( 'message' => 'Question inconnue.' ), 400 );
    }
    Praxis360_DB::save_open_answer( $resp->id, $qkey, $text );
    wp_send_json_success();
}

add_action( 'wp_ajax_p360_submit', 'praxis360_ajax_submit' );
add_action( 'wp_ajax_nopriv_p360_submit', 'praxis360_ajax_submit' );
function praxis360_ajax_submit() {
    check_ajax_referer( 'p360_passation', 'nonce' );

    $token = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';
    $resp  = Praxis360_DB::get_respondent_by_token( $token );
    if ( ! $resp ) {
        wp_send_json_error( array( 'message' => 'Lien invalide.' ), 403 );
    }
    Praxis360_DB::set_respondent_status( $resp->id, 'completed' );

    // Clôture automatique si tous les répondants ont terminé.
    praxis360_maybe_close_campaign( (int) $resp->campaign_id );

    wp_send_json_success();
}

/** Clôt la campagne et envoie le rapport si tous les répondants ont répondu. */
function praxis360_maybe_close_campaign( $campaign_id ) {
    $campaign = Praxis360_DB::get_campaign( $campaign_id );
    if ( ! $campaign || 'closed' === $campaign->status ) {
        return;
    }
    $respondents = Praxis360_DB::get_respondents( $campaign_id );
    $all_done    = ! empty( $respondents );
    foreach ( $respondents as $r ) {
        if ( 'completed' !== $r->status ) {
            $all_done = false;
            break;
        }
    }
    if ( $all_done ) {
        Praxis360_DB::update_campaign_status( $campaign_id, 'closed' );
        $report = Praxis360_Scoring::compute( $campaign_id );
        Praxis360_Mailer::send_results( $campaign );
        Praxis360_Mailer::notify_admin_closed( $campaign, $report['counts'] );
    }
}

/* =========================================================================
 *  ADMIN — création de campagne, invitations, relances, clôture manuelle
 * ========================================================================= */

add_action( 'wp_ajax_p360_create_campaign', 'praxis360_ajax_create_campaign' );
function praxis360_ajax_create_campaign() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Accès refusé.' ), 403 );
    }
    check_ajax_referer( 'p360_admin', 'nonce' );

    $subject_name  = isset( $_POST['subject_name'] ) ? sanitize_text_field( wp_unslash( $_POST['subject_name'] ) ) : '';
    $subject_email = isset( $_POST['subject_email'] ) ? sanitize_email( wp_unslash( $_POST['subject_email'] ) ) : '';
    $deadline      = isset( $_POST['deadline'] ) ? sanitize_text_field( wp_unslash( $_POST['deadline'] ) ) : '';
    $evaluators    = isset( $_POST['evaluators'] ) ? (array) $_POST['evaluators'] : array();

    if ( '' === $subject_name || ! is_email( $subject_email ) ) {
        wp_send_json_error( array( 'message' => 'Nom et email valide du sujet requis.' ), 400 );
    }
    $deadline = ( $deadline && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $deadline ) ) ? $deadline : null;

    $campaign_id = Praxis360_DB::create_campaign( $subject_name, $subject_email, $deadline );
    $campaign    = Praxis360_DB::get_campaign( $campaign_id );

    // Auto-évaluation (le sujet est aussi un répondant "self").
    $self_id = Praxis360_DB::add_respondent( $campaign_id, $subject_name, $subject_email, 'self' );
    $self    = praxis360_get_respondent_by_id( $self_id );
    if ( $self ) {
        Praxis360_Mailer::invite_self( $self, $campaign );
    }

    // Évaluateurs.
    $valid_relations = array( 'manager', 'peer', 'report', 'client' );
    $sent = 0;
    foreach ( $evaluators as $ev ) {
        $name  = isset( $ev['name'] ) ? sanitize_text_field( wp_unslash( $ev['name'] ) ) : '';
        $email = isset( $ev['email'] ) ? sanitize_email( wp_unslash( $ev['email'] ) ) : '';
        $rel   = isset( $ev['relation'] ) ? sanitize_text_field( wp_unslash( $ev['relation'] ) ) : 'peer';
        if ( '' === $name || ! is_email( $email ) || ! in_array( $rel, $valid_relations, true ) ) {
            continue;
        }
        $rid  = Praxis360_DB::add_respondent( $campaign_id, $name, $email, $rel );
        $resp = praxis360_get_respondent_by_id( $rid );
        if ( $resp ) {
            Praxis360_Mailer::invite_evaluator( $resp, $campaign );
            $sent++;
        }
    }

    wp_send_json_success( array(
        'campaign_id' => $campaign_id,
        'invited'     => $sent,
        'message'     => sprintf( 'Campagne créée. %d invitation(s) envoyée(s) + auto-évaluation.', $sent ),
    ) );
}

add_action( 'wp_ajax_p360_send_reminders', 'praxis360_ajax_send_reminders' );
function praxis360_ajax_send_reminders() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Accès refusé.' ), 403 );
    }
    check_ajax_referer( 'p360_admin', 'nonce' );

    $campaign_id = isset( $_POST['campaign_id'] ) ? absint( $_POST['campaign_id'] ) : 0;
    $campaign    = Praxis360_DB::get_campaign( $campaign_id );
    if ( ! $campaign ) {
        wp_send_json_error( array( 'message' => 'Campagne introuvable.' ), 404 );
    }
    $sent = 0;
    foreach ( Praxis360_DB::get_respondents( $campaign_id ) as $r ) {
        if ( 'completed' !== $r->status && 'self' !== $r->relation ) {
            Praxis360_Mailer::reminder( $r, $campaign );
            $sent++;
        }
    }
    wp_send_json_success( array( 'message' => sprintf( '%d relance(s) envoyée(s).', $sent ) ) );
}

add_action( 'wp_ajax_p360_close_campaign', 'praxis360_ajax_close_campaign' );
function praxis360_ajax_close_campaign() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Accès refusé.' ), 403 );
    }
    check_ajax_referer( 'p360_admin', 'nonce' );

    $campaign_id = isset( $_POST['campaign_id'] ) ? absint( $_POST['campaign_id'] ) : 0;
    $campaign    = Praxis360_DB::get_campaign( $campaign_id );
    if ( ! $campaign ) {
        wp_send_json_error( array( 'message' => 'Campagne introuvable.' ), 404 );
    }
    Praxis360_DB::update_campaign_status( $campaign_id, 'closed' );
    $report = Praxis360_Scoring::compute( $campaign_id );
    Praxis360_Mailer::send_results( $campaign );
    Praxis360_Mailer::notify_admin_closed( $campaign, $report['counts'] );
    wp_send_json_success( array( 'message' => 'Campagne clôturée, rapport envoyé au sujet.' ) );
}

/** Helper : récupère un répondant par id. */
function praxis360_get_respondent_by_id( $id ) {
    global $wpdb;
    return $wpdb->get_row( $wpdb->prepare(
        'SELECT * FROM ' . Praxis360_DB::table( 'respondents' ) . ' WHERE id = %d',
        $id
    ) );
}
