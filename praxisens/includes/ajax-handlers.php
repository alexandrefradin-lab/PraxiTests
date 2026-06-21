<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_ajax_praxisens_submit', 'praxisens_submit' );
add_action( 'wp_ajax_nopriv_praxisens_submit', 'praxisens_submit' );

function praxisens_submit() {
    check_ajax_referer( 'praxisens_nonce', 'nonce' );

    $first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
    $email      = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
    $raw        = isset( $_POST['answers'] ) ? wp_unslash( $_POST['answers'] ) : '';

    if ( empty( $email ) || ! is_email( $email ) ) {
        wp_send_json_error( array( 'message' => 'Adresse e-mail invalide.' ) );
    }

    $decoded = json_decode( $raw, true );
    if ( ! is_array( $decoded ) ) {
        wp_send_json_error( array( 'message' => 'Réponses invalides.' ) );
    }

    // Sanitization des réponses : {id => value 1..5}
    $answers = array();
    foreach ( $decoded as $id => $value ) {
        $answers[ absint( $id ) ] = max( 1, min( 5, absint( $value ) ) );
    }

    $scores = Praxisens::compute( $answers );

    // Persistance
    global $wpdb;
    $table = $wpdb->prefix . 'praxisens_results';
    $wpdb->insert(
        $table,
        array(
            'created_at'    => current_time( 'mysql' ),
            'first_name'    => $first_name,
            'email'         => $email,
            'score_global'  => $scores['global'],
            'score_eoe'     => $scores['EOE'],
            'score_aes'     => $scores['AES'],
            'score_lst'     => $scores['LST'],
            'profile_label' => $scores['profile'],
            'answers'       => wp_json_encode( $answers ),
        ),
        array( '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%s' )
    );

    // Email de restitution (échec SMTP non bloquant)
    praxisens_send_results_email( $first_name, $email, $scores );

    wp_send_json_success( array(
        'scores'  => $scores,
        'message' => 'ok',
    ) );
}
