<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gestion de l'historique des passages — permet de détecter les "reprises" de test
 * par un même email et de calculer l'évolution des scores.
 */
class PE_History {

    /**
     * Récupère le dernier résultat complété pour un email donné,
     * en excluant la session en cours.
     *
     * @param string $email
     * @param int    $exclude_session_id
     * @return object|null
     */
    public static function get_previous( $email, $exclude_session_id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT s.id, s.prenom, s.completed_at,
                    r.score_global, r.dim_1,  r.dim_2,  r.dim_3,  r.dim_4,
                    r.dim_5,  r.dim_6,  r.dim_7,  r.dim_8,
                    r.dim_9,  r.dim_10, r.dim_11, r.dim_12,
                    r.dim_13, r.dim_14, r.dim_15, r.dim_16
             FROM {$wpdb->prefix}pemo_sessions s
             INNER JOIN {$wpdb->prefix}pemo_results r ON r.session_id = s.id
             WHERE s.email = %s
               AND s.completed_at IS NOT NULL
               AND s.id != %d
             ORDER BY s.completed_at DESC
             LIMIT 1",
            sanitize_email( $email ),
            absint( $exclude_session_id )
        ) );
    }

    /**
     * Compte le nombre de passages complétés pour un email (session actuelle incluse).
     *
     * @param string $email
     * @return int
     */
    public static function count_passages( $email ) {
        global $wpdb;
        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pemo_sessions
             WHERE email = %s AND completed_at IS NOT NULL",
            sanitize_email( $email )
        ) );
    }

    /**
     * Calcule la comparaison entre le résultat actuel et le précédent.
     *
     * @param array  $current_dim_scores  array( dim_id => score )
     * @param int    $current_global
     * @param object $previous            Ligne DB du passage précédent
     * @return array {
     *   global_diff: int,
     *   global_prev: int,
     *   dims: array( dim_id => array(prev: int, diff: int, arrow: string) )
     *   date_prev: string
     * }
     */
    public static function build_comparison( $current_dim_scores, $current_global, $previous ) {
        $dims = PE_Calculator::get_dimensions();

        $prev_global = intval( $previous->score_global ?? 0 );
        $global_diff = $current_global - $prev_global;

        $dim_comparisons = array();
        foreach ( $dims as $dim_id => $dim ) {
            $col      = 'dim_' . $dim_id;
            $prev_val = intval( $previous->$col ?? 0 );
            $curr_val = intval( $current_dim_scores[ $dim_id ] ?? 0 );
            $diff     = $curr_val - $prev_val;
            $arrow    = $diff > 0 ? '↑' : ( $diff < 0 ? '↓' : '→' );

            $dim_comparisons[ $dim_id ] = array(
                'label'  => $dim['label'],
                'prev'   => $prev_val,
                'curr'   => $curr_val,
                'diff'   => $diff,
                'arrow'  => $arrow,
            );
        }

        return array(
            'global_prev'  => $prev_global,
            'global_diff'  => $global_diff,
            'global_arrow' => $global_diff > 0 ? '↑' : ( $global_diff < 0 ? '↓' : '→' ),
            'dims'         => $dim_comparisons,
            'date_prev'    => wp_date( 'd/m/Y', strtotime( $previous->completed_at ) ),
            'passage_prev' => intval( $previous->id ),
        );
    }
}
