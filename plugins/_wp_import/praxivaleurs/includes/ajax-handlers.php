<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_ajax_praxivaleurs_submit',        'praxivaleurs_handle_submit' );
add_action( 'wp_ajax_nopriv_praxivaleurs_submit', 'praxivaleurs_handle_submit' );

function praxivaleurs_handle_submit() {

    if ( ! check_ajax_referer( 'praxivaleurs_nonce', 'nonce', false ) ) {
        wp_send_json_error( array('message' => 'Sécurité : nonce invalide.') );
    }

    $prenom      = sanitize_text_field( wp_unslash( $_POST['prenom']  ?? '' ) );
    $email       = sanitize_email( wp_unslash( $_POST['email']         ?? '' ) );
    $reponses    = $_POST['reponses']      ?? array();
    $comparaisons= $_POST['comparaisons']  ?? array();
    $scores_js   = $_POST['scores_finaux'] ?? array();

    if ( empty($prenom) || ! is_email($email) ) {
        wp_send_json_error( array('message' => 'Prénom ou email invalide.') );
    }

    // ── Validation des 40 réponses Likert ─────────────────────────────────────
    $questions_ids = array_column( PraxiValeurs::get_questions(), 'id' );
    $nb_repondues  = 0;
    foreach ( $questions_ids as $qid ) {
        if ( isset( $reponses[$qid] ) && $reponses[$qid] !== '' ) $nb_repondues++;
    }
    if ( $nb_repondues < 40 ) {
        wp_send_json_error( array('message' => 'Toutes les questions doivent être répondues (' . $nb_repondues . '/40).') );
    }

    // ── Sanitization réponses Likert ──────────────────────────────────────────
    $valeurs_valides = array(1, 2, 3, 4, 5, 6);
    $reponses_clean  = array();
    foreach ( $reponses as $key => $val ) {
        $k = absint($key);
        $v = absint($val);
        if ( ! in_array($v, $valeurs_valides, true) ) $v = 1;
        $reponses_clean[$k] = $v;
    }

    // ── Score Likert par dimension ─────────────────────────────────────────────
    $questions  = PraxiValeurs::get_questions();
    $dimensions = PraxiValeurs::get_dimensions();
    $sommes     = array_fill_keys( array_keys($dimensions), 0 );
    $counts     = array_fill_keys( array_keys($dimensions), 0 );

    foreach ( $questions as $q ) {
        $qid = $q['id'];
        $dim = $q['dim'];
        if ( isset($reponses_clean[$qid]) ) {
            $sommes[$dim] += $reponses_clean[$qid];
            $counts[$dim]++;
        }
    }

    $likert_norm = array();
    foreach ( $dimensions as $dim_key => $dim ) {
        $moy = $counts[$dim_key] > 0 ? ($sommes[$dim_key] / $counts[$dim_key]) : 0;
        $likert_norm[$dim_key] = round(($moy / 6) * 100);
    }

    // ── Score tournoi par dimension ────────────────────────────────────────────
    $dim_keys    = array_keys($dimensions);
    $victoires   = array_fill_keys($dim_keys, 0);
    $comparaisons_clean = array();

    foreach ( $comparaisons as $comp ) {
        $a      = sanitize_text_field($comp['a']      ?? '');
        $b      = sanitize_text_field($comp['b']      ?? '');
        $winner = sanitize_text_field($comp['winner'] ?? '');
        if ( isset($victoires[$a]) && isset($victoires[$b]) && in_array($winner, array($a, $b), true) ) {
            $victoires[$winner]++;
            $comparaisons_clean[] = array('a' => $a, 'b' => $b, 'winner' => $winner);
        }
    }

    $max_victoires = max( array_values($victoires) );
    $max_victoires = $max_victoires > 0 ? $max_victoires : 1;
    $tournoi_norm  = array();
    foreach ( $dim_keys as $k ) {
        $tournoi_norm[$k] = round(($victoires[$k] / $max_victoires) * 100);
    }

    // ── Score final pondéré : 60% Likert + 40% tournoi — cappé à 100 ──────────
    $scores_finaux = array();
    foreach ( $dim_keys as $k ) {
        $val = round($likert_norm[$k] * 0.60 + $tournoi_norm[$k] * 0.40);
        $scores_finaux[$k] = min(100, max(0, $val)); // strictement entre 0 et 100
    }

    // ── Top 5 ─────────────────────────────────────────────────────────────────
    arsort($scores_finaux);
    $top5_keys = array_slice(array_keys($scores_finaux), 0, 5);
    $top5      = array();
    foreach ($top5_keys as $k) {
        $top5[$k] = $scores_finaux[$k];
    }

    // ── Sauvegarde en base ────────────────────────────────────────────────────
    global $wpdb;
    $table = $wpdb->prefix . 'praxivaleurs_sessions';
    $wpdb->insert($table, array(
        'prenom'   => $prenom,
        'email'    => $email,
        'reponses' => wp_json_encode($reponses_clean),
        'scores'   => wp_json_encode($scores_finaux),
        'top5'     => wp_json_encode($top5),
    ), array('%s','%s','%s','%s','%s'));

    // ── Emails ────────────────────────────────────────────────────────────────
    praxivaleurs_send_user_email($prenom, $email, $top5, $scores_finaux);
    praxivaleurs_send_consultant_email($prenom, $email, $top5);

    wp_send_json_success( array(
        'scores'     => $scores_finaux,
        'top5'       => $top5,
        'prenom'     => $prenom,
        'mapping'    => PraxiValeurs::get_mapping(),
        'dimensions' => PraxiValeurs::get_dimensions(),
    ));
}
