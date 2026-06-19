<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PE_DB {

    /** Version de schéma — à incrémenter à chaque modification de table */
    const DB_VERSION = '1.3';

    /**
     * Appelée à l'activation ET lors de chaque mise à jour du plugin.
     * dbDelta() est idempotent : elle crée ou met à jour les tables sans perte de données.
     */
    public static function install() {
        global $wpdb;
        $cs = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Sessions
        $t_sessions = $wpdb->prefix . 'pemo_sessions';
        dbDelta( "CREATE TABLE {$t_sessions} (
            id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            token        VARCHAR(64)     NOT NULL DEFAULT '',
            prenom       VARCHAR(100)    NOT NULL DEFAULT '',
            email        VARCHAR(255)    NOT NULL DEFAULT '',
            ip_address   VARCHAR(45)     NOT NULL DEFAULT '',
            started_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            completed_at DATETIME        NULL,
            relance_3j   TINYINT(1)      NOT NULL DEFAULT 0,
            relance_8j   TINYINT(1)      NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY token (token),
            KEY email (email),
            KEY completed_at (completed_at)
        ) {$cs};" );

        // Résultats
        $t_results = $wpdb->prefix . 'pemo_results';
        dbDelta( "CREATE TABLE {$t_results} (
            id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            session_id   BIGINT UNSIGNED NOT NULL,
            dim_1        TINYINT UNSIGNED NOT NULL DEFAULT 0,
            dim_2        TINYINT UNSIGNED NOT NULL DEFAULT 0,
            dim_3        TINYINT UNSIGNED NOT NULL DEFAULT 0,
            dim_4        TINYINT UNSIGNED NOT NULL DEFAULT 0,
            dim_5        TINYINT UNSIGNED NOT NULL DEFAULT 0,
            dim_6        TINYINT UNSIGNED NOT NULL DEFAULT 0,
            dim_7        TINYINT UNSIGNED NOT NULL DEFAULT 0,
            dim_8        TINYINT UNSIGNED NOT NULL DEFAULT 0,
            dim_9        TINYINT UNSIGNED NOT NULL DEFAULT 0,
            dim_10       TINYINT UNSIGNED NOT NULL DEFAULT 0,
            dim_11       TINYINT UNSIGNED NOT NULL DEFAULT 0,
            dim_12       TINYINT UNSIGNED NOT NULL DEFAULT 0,
            dim_13       TINYINT UNSIGNED NOT NULL DEFAULT 0,
            dim_14       TINYINT UNSIGNED NOT NULL DEFAULT 0,
            dim_15       TINYINT UNSIGNED NOT NULL DEFAULT 0,
            dim_16              TINYINT UNSIGNED NOT NULL DEFAULT 0,
            score_global        SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            score_desirabilite  TINYINT UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            KEY session_id (session_id)
        ) {$cs};" );

        // Logs
        PE_Logger::install();

        // Stocker la version de schéma installée
        update_option( 'pemo_db_version', self::DB_VERSION );
    }

    /**
     * Vérifie si une mise à jour de schéma est nécessaire et la lance si c'est le cas.
     * À appeler sur 'plugins_loaded' pour couvrir les mises à jour par upload de ZIP.
     */
    public static function maybe_upgrade() {
        if ( get_option( 'pemo_db_version' ) !== self::DB_VERSION ) {
            self::install();
        }
    }

    // ── Sessions ──────────────────────────────────────────────────────────────

    public static function create_session( $prenom, $email, $ip = '' ) {
        global $wpdb;
        $token = wp_generate_password( 32, false );
        $wpdb->insert(
            $wpdb->prefix . 'pemo_sessions',
            array(
                'token'      => $token,
                'prenom'     => sanitize_text_field( $prenom ),
                'email'      => sanitize_email( $email ),
                'ip_address' => sanitize_text_field( substr( $ip, 0, 45 ) ),
                'started_at' => current_time( 'mysql' ),
            )
        );
        return array( 'id' => $wpdb->insert_id, 'token' => $token );
    }

    public static function get_session_by_token( $token ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pemo_sessions WHERE token = %s LIMIT 1",
            sanitize_text_field( $token )
        ) );
    }

    public static function complete_session( $session_id ) {
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'pemo_sessions',
            array( 'completed_at' => current_time( 'mysql' ) ),
            array( 'id' => absint( $session_id ) )
        );
    }

    // ── Résultats ─────────────────────────────────────────────────────────────

    public static function save_results( $session_id, array $dim_scores, $score_global, $score_desirabilite = 0 ) {
        global $wpdb;
        $data = array(
            'session_id'         => absint( $session_id ),
            'score_global'       => absint( $score_global ),
            'score_desirabilite' => absint( $score_desirabilite ),
        );
        for ( $i = 1; $i <= 16; $i++ ) {
            $data[ "dim_{$i}" ] = absint( $dim_scores[ $i ] ?? 0 );
        }
        $wpdb->insert( $wpdb->prefix . 'pemo_results', $data );
        return $wpdb->insert_id;
    }

    public static function get_results_by_session( $session_id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pemo_results WHERE session_id = %d LIMIT 1",
            absint( $session_id )
        ) );
    }

    // ── Relances ──────────────────────────────────────────────────────────────

    public static function get_pending_relances( $jours ) {
        global $wpdb;
        $col    = $jours === 3 ? 'relance_3j' : 'relance_8j';
        $offset = absint( $jours );
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT s.*, r.score_global, r.dim_1, r.dim_2, r.dim_3, r.dim_4, r.dim_5,
                    r.dim_6, r.dim_7, r.dim_8, r.dim_9, r.dim_10, r.dim_11, r.dim_12,
                    r.dim_13, r.dim_14, r.dim_15, r.dim_16
             FROM {$wpdb->prefix}pemo_sessions s
             LEFT JOIN {$wpdb->prefix}pemo_results r ON r.session_id = s.id
             WHERE s.completed_at IS NOT NULL
               AND s.{$col} = 0
               AND s.completed_at <= %s
               AND s.email != ''",
            gmdate( 'Y-m-d H:i:s', strtotime( "-{$offset} days" ) )
        ) );
    }

    public static function mark_relance( $session_id, $jours ) {
        global $wpdb;
        $col = $jours === 3 ? 'relance_3j' : 'relance_8j';
        $wpdb->update(
            $wpdb->prefix . 'pemo_sessions',
            array( $col => 1 ),
            array( 'id' => absint( $session_id ) )
        );
    }

    // ── Suppression ───────────────────────────────────────────────────────────

    /**
     * Supprime une session et ses résultats associés.
     * @param int $session_id
     * @return bool  true si la session existait et a été supprimée.
     */
    public static function delete_session( $session_id ) {
        global $wpdb;
        $id = absint( $session_id );
        if ( ! $id ) return false;

        $wpdb->delete( $wpdb->prefix . 'pemo_results',  array( 'session_id' => $id ), array( '%d' ) );
        $deleted = $wpdb->delete( $wpdb->prefix . 'pemo_sessions', array( 'id' => $id ), array( '%d' ) );
        return (bool) $deleted;
    }

    /**
     * Supprime plusieurs sessions et leurs résultats.
     * @param int[] $ids
     * @return int  Nombre de sessions supprimées.
     */
    public static function delete_sessions( array $ids ) {
        $count = 0;
        foreach ( $ids as $id ) {
            if ( self::delete_session( $id ) ) $count++;
        }
        return $count;
    }

    // ── Admin listing ─────────────────────────────────────────────────────────

    public static function get_all_results( $limit = 50, $offset = 0 ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT s.id, s.prenom, s.email, s.started_at, s.completed_at,
                    r.score_global, r.score_desirabilite
             FROM {$wpdb->prefix}pemo_sessions s
             LEFT JOIN {$wpdb->prefix}pemo_results r ON r.session_id = s.id
             ORDER BY s.id DESC
             LIMIT %d OFFSET %d",
            absint( $limit ), absint( $offset )
        ) );
    }

    public static function count_sessions() {
        global $wpdb;
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}pemo_sessions" ); // phpcs:ignore
    }

    public static function count_completed() {
        global $wpdb;
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}pemo_sessions WHERE completed_at IS NOT NULL" ); // phpcs:ignore
    }

    // ── Analytics (dashboard enrichi) ────────────────────────────────────────

    /**
     * Retourne la moyenne de chaque dimension sur l'ensemble des sessions complétées.
     * @return array { dim_1: float, ..., dim_16: float }
     */
    public static function get_dim_averages() {
        global $wpdb;
        $row = $wpdb->get_row( // phpcs:ignore
            "SELECT
                AVG(r.dim_1)  AS dim_1,  AVG(r.dim_2)  AS dim_2,
                AVG(r.dim_3)  AS dim_3,  AVG(r.dim_4)  AS dim_4,
                AVG(r.dim_5)  AS dim_5,  AVG(r.dim_6)  AS dim_6,
                AVG(r.dim_7)  AS dim_7,  AVG(r.dim_8)  AS dim_8,
                AVG(r.dim_9)  AS dim_9,  AVG(r.dim_10) AS dim_10,
                AVG(r.dim_11) AS dim_11, AVG(r.dim_12) AS dim_12,
                AVG(r.dim_13) AS dim_13, AVG(r.dim_14) AS dim_14,
                AVG(r.dim_15) AS dim_15, AVG(r.dim_16) AS dim_16,
                AVG(r.score_global) AS score_global_avg
             FROM {$wpdb->prefix}pemo_results r
             INNER JOIN {$wpdb->prefix}pemo_sessions s ON s.id = r.session_id
             WHERE s.completed_at IS NOT NULL",
            ARRAY_A
        );
        return $row ? array_map( 'floatval', $row ) : array();
    }

    /**
     * Retourne la distribution des niveaux QE parmi les sessions complétées.
     * @return array { faible: int, modere: int, eleve: int, tres_eleve: int }
     */
    public static function get_qe_distribution() {
        global $wpdb;
        $rows = $wpdb->get_results( // phpcs:ignore
            "SELECT r.score_global
             FROM {$wpdb->prefix}pemo_results r
             INNER JOIN {$wpdb->prefix}pemo_sessions s ON s.id = r.session_id
             WHERE s.completed_at IS NOT NULL"
        );

        $dist = array( 'faible' => 0, 'modere' => 0, 'eleve' => 0, 'tres_eleve' => 0 );
        foreach ( $rows as $row ) {
            $s = intval( $row->score_global );
            if ( $s <= 120 )      $dist['faible']++;
            elseif ( $s <= 200 )  $dist['modere']++;
            elseif ( $s <= 280 )  $dist['eleve']++;
            else                  $dist['tres_eleve']++;
        }
        return $dist;
    }

    /**
     * Retourne le score global moyen des tests complétés.
     * @return float
     */
    public static function get_avg_score() {
        global $wpdb;
        return floatval( $wpdb->get_var( // phpcs:ignore
            "SELECT AVG(r.score_global)
             FROM {$wpdb->prefix}pemo_results r
             INNER JOIN {$wpdb->prefix}pemo_sessions s ON s.id = r.session_id
             WHERE s.completed_at IS NOT NULL"
        ) );
    }

    /**
     * Retourne les N derniers résultats pour l'export CSV.
     * @return array
     */
    public static function get_all_results_for_export( $limit = 500 ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare( // phpcs:ignore
            "SELECT s.id, s.prenom, s.email, s.started_at, s.completed_at,
                    r.score_global,
                    r.dim_1,  r.dim_2,  r.dim_3,  r.dim_4,
                    r.dim_5,  r.dim_6,  r.dim_7,  r.dim_8,
                    r.dim_9,  r.dim_10, r.dim_11, r.dim_12,
                    r.dim_13, r.dim_14, r.dim_15, r.dim_16
             FROM {$wpdb->prefix}pemo_sessions s
             LEFT JOIN {$wpdb->prefix}pemo_results r ON r.session_id = s.id
             WHERE s.completed_at IS NOT NULL
             ORDER BY s.completed_at DESC
             LIMIT %d",
            absint( $limit )
        ) );
    }

    /**
     * Récupère les tests des 30 derniers jours groupés par semaine pour le graphique d'activité.
     * @return array
     */
    public static function get_weekly_counts( $weeks = 8 ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare( // phpcs:ignore
            "SELECT DATE_FORMAT(completed_at, '%%Y-%%u') AS week_key,
                    COUNT(*) AS cnt
             FROM {$wpdb->prefix}pemo_sessions
             WHERE completed_at IS NOT NULL
               AND completed_at >= DATE_SUB(NOW(), INTERVAL %d WEEK)
             GROUP BY week_key
             ORDER BY week_key ASC",
            absint( $weeks )
        ) );
    }
}
