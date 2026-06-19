<?php
/**
 * Couche d'accès aux données Praxis 360.
 * Toute requête SQL passe par ici → portage PraxiQuest facilité.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Praxis360_DB {

    /** Noms de tables (sans préfixe). */
    public static function tables() {
        return array(
            'campaigns'    => 'campaigns',
            'respondents'  => 'respondents',
            'responses'    => 'responses',
            'open_answers' => 'open_answers',
        );
    }

    /** Nom complet d'une table. */
    public static function table( $key ) {
        return praxis360_table_prefix() . $key;
    }

    /** Création des tables à l'activation. */
    public static function install() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        $c   = self::table( 'campaigns' );
        $r   = self::table( 'respondents' );
        $rep = self::table( 'responses' );
        $oa  = self::table( 'open_answers' );

        $sql = array();

        $sql[] = "CREATE TABLE {$c} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            subject_name VARCHAR(190) NOT NULL DEFAULT '',
            subject_email VARCHAR(190) NOT NULL DEFAULT '',
            subject_token VARCHAR(64) NOT NULL DEFAULT '',
            status VARCHAR(20) NOT NULL DEFAULT 'draft',
            deadline DATE NULL DEFAULT NULL,
            settings LONGTEXT NULL,
            created_at DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
            PRIMARY KEY  (id),
            KEY subject_token (subject_token)
        ) {$charset_collate};";

        $sql[] = "CREATE TABLE {$r} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            campaign_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            name VARCHAR(190) NOT NULL DEFAULT '',
            email VARCHAR(190) NOT NULL DEFAULT '',
            relation VARCHAR(20) NOT NULL DEFAULT 'peer',
            token VARCHAR(64) NOT NULL DEFAULT '',
            status VARCHAR(20) NOT NULL DEFAULT 'invited',
            invited_at DATETIME NULL DEFAULT NULL,
            completed_at DATETIME NULL DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY campaign_id (campaign_id),
            KEY token (token)
        ) {$charset_collate};";

        $sql[] = "CREATE TABLE {$rep} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            respondent_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            item_key VARCHAR(40) NOT NULL DEFAULT '',
            value TINYINT NULL DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY respondent_id (respondent_id),
            KEY item_key (item_key)
        ) {$charset_collate};";

        $sql[] = "CREATE TABLE {$oa} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            respondent_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            question_key VARCHAR(40) NOT NULL DEFAULT '',
            answer_text TEXT NULL,
            PRIMARY KEY  (id),
            KEY respondent_id (respondent_id)
        ) {$charset_collate};";

        foreach ( $sql as $stmt ) {
            dbDelta( $stmt );
        }

        add_option( 'praxis360_version', PRAXIS360_VERSION );
    }

    /** Génère un token unique URL-safe. */
    public static function generate_token() {
        return substr( str_replace( array( '+', '/', '=' ), '', base64_encode( random_bytes( 24 ) ) ), 0, 32 );
    }

    // --- Campagnes ------------------------------------------------------------

    public static function create_campaign( $subject_name, $subject_email, $deadline = null, $settings = array() ) {
        global $wpdb;
        $wpdb->insert(
            self::table( 'campaigns' ),
            array(
                'subject_name'  => $subject_name,
                'subject_email' => $subject_email,
                'subject_token' => self::generate_token(),
                'status'        => 'active',
                'deadline'      => $deadline ? $deadline : null,
                'settings'      => wp_json_encode( $settings ),
                'created_at'    => current_time( 'mysql' ),
            ),
            array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
        );
        return (int) $wpdb->insert_id;
    }

    public static function get_campaign( $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table( 'campaigns' ) . ' WHERE id = %d', $id ) );
    }

    public static function get_campaign_by_token( $token ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table( 'campaigns' ) . ' WHERE subject_token = %s', $token ) );
    }

    public static function get_campaigns() {
        global $wpdb;
        return $wpdb->get_results( 'SELECT * FROM ' . self::table( 'campaigns' ) . ' ORDER BY created_at DESC' );
    }

    public static function update_campaign_status( $id, $status ) {
        global $wpdb;
        $wpdb->update( self::table( 'campaigns' ), array( 'status' => $status ), array( 'id' => $id ), array( '%s' ), array( '%d' ) );
    }

    // --- Répondants -----------------------------------------------------------

    public static function add_respondent( $campaign_id, $name, $email, $relation ) {
        global $wpdb;
        $wpdb->insert(
            self::table( 'respondents' ),
            array(
                'campaign_id' => $campaign_id,
                'name'        => $name,
                'email'       => $email,
                'relation'    => $relation,
                'token'       => self::generate_token(),
                'status'      => 'invited',
                'invited_at'  => current_time( 'mysql' ),
            ),
            array( '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
        );
        return (int) $wpdb->insert_id;
    }

    public static function get_respondent_by_token( $token ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table( 'respondents' ) . ' WHERE token = %s', $token ) );
    }

    public static function get_respondents( $campaign_id ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . self::table( 'respondents' ) . ' WHERE campaign_id = %d ORDER BY id ASC', $campaign_id ) );
    }

    public static function set_respondent_status( $id, $status ) {
        global $wpdb;
        $data = array( 'status' => $status );
        $fmt  = array( '%s' );
        if ( 'completed' === $status ) {
            $data['completed_at'] = current_time( 'mysql' );
            $fmt[]                = '%s';
        }
        $wpdb->update( self::table( 'respondents' ), $data, array( 'id' => $id ), $fmt, array( '%d' ) );
    }

    // --- Réponses -------------------------------------------------------------

    /** Enregistre / met à jour une réponse à un item (upsert manuel). */
    public static function save_response( $respondent_id, $item_key, $value ) {
        global $wpdb;
        $table = self::table( 'responses' );
        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$table} WHERE respondent_id = %d AND item_key = %s",
            $respondent_id, $item_key
        ) );

        $val_fmt = ( null === $value ) ? null : (int) $value;

        if ( $existing ) {
            $wpdb->update(
                $table,
                array( 'value' => $val_fmt ),
                array( 'id' => $existing ),
                array( ( null === $val_fmt ) ? null : '%d' ),
                array( '%d' )
            );
        } else {
            $wpdb->insert(
                $table,
                array( 'respondent_id' => $respondent_id, 'item_key' => $item_key, 'value' => $val_fmt ),
                array( '%d', '%s', ( null === $val_fmt ) ? null : '%d' )
            );
        }
    }

    public static function get_responses( $respondent_id ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            'SELECT item_key, value FROM ' . self::table( 'responses' ) . ' WHERE respondent_id = %d',
            $respondent_id
        ) );
    }

    public static function save_open_answer( $respondent_id, $question_key, $text ) {
        global $wpdb;
        $table = self::table( 'open_answers' );
        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$table} WHERE respondent_id = %d AND question_key = %s",
            $respondent_id, $question_key
        ) );
        if ( $existing ) {
            $wpdb->update( $table, array( 'answer_text' => $text ), array( 'id' => $existing ), array( '%s' ), array( '%d' ) );
        } else {
            $wpdb->insert( $table, array( 'respondent_id' => $respondent_id, 'question_key' => $question_key, 'answer_text' => $text ), array( '%d', '%s', '%s' ) );
        }
    }

    public static function get_open_answers_for_campaign( $campaign_id ) {
        global $wpdb;
        $r  = self::table( 'respondents' );
        $oa = self::table( 'open_answers' );
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT o.question_key, o.answer_text, r.relation
             FROM {$oa} o
             INNER JOIN {$r} r ON r.id = o.respondent_id
             WHERE r.campaign_id = %d AND o.answer_text <> ''",
            $campaign_id
        ) );
    }
}
