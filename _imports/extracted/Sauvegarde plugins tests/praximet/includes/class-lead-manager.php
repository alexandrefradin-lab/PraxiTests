<?php
/**
 * PraxiMet – Gestionnaire de leads
 * Sauvegarde et récupération des leads en base de données
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class PraxiMet_Lead_Manager {

    const TABLE = 'praximet_leads';

    /**
     * Crée la table en base lors de l'activation du plugin.
     */
    public static function creer_table() {
        global $wpdb;

        $table      = $wpdb->prefix . self::TABLE;
        $charset    = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            nom             VARCHAR(100) NOT NULL,
            prenom          VARCHAR(100) NOT NULL,
            email           VARCHAR(255) NOT NULL,
            telephone       VARCHAR(20) DEFAULT '',
            rgpd_consent    TINYINT UNSIGNED NOT NULL DEFAULT 0,
            score_r         TINYINT UNSIGNED NOT NULL DEFAULT 0,
            score_i         TINYINT UNSIGNED NOT NULL DEFAULT 0,
            score_a         TINYINT UNSIGNED NOT NULL DEFAULT 0,
            score_s         TINYINT UNSIGNED NOT NULL DEFAULT 0,
            score_e         TINYINT UNSIGNED NOT NULL DEFAULT 0,
            score_c         TINYINT UNSIGNED NOT NULL DEFAULT 0,
            code_riasec     VARCHAR(3) NOT NULL DEFAULT '',
            statut          VARCHAR(20) NOT NULL DEFAULT 'nouveau',
            source_page     VARCHAR(500) DEFAULT '',
            rdv_pris        TINYINT UNSIGNED NOT NULL DEFAULT 0,
            relance_envoyee TINYINT UNSIGNED NOT NULL DEFAULT 0,
            created_at      DATETIME NOT NULL,
            updated_at      DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY email (email),
            KEY statut (statut),
            KEY created_at (created_at)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Supprime un lead par ID.
     */
    public static function supprimer( int $id ) {
        global $wpdb;
        return $wpdb->delete(
            $wpdb->prefix . self::TABLE,
            [ 'id' => $id ],
            [ '%d' ]
        );
    }

    /**
     * Sauvegarde un lead en base de données.
     *
     * @param array $data  Données validées du lead
     * @return int|false   ID du lead inséré, false en cas d'erreur
     */
    public static function sauvegarder( array $data ) {
        global $wpdb;

        $table = $wpdb->prefix . self::TABLE;
        $now   = current_time( 'mysql' );

        $insert = [
            'nom'             => $data['nom'],
            'prenom'          => $data['prenom'],
            'email'           => $data['email'],
            'telephone'       => '',
            'rgpd_consent'    => 1,
            'score_r'         => $data['scores']['R'],
            'score_i'         => $data['scores']['I'],
            'score_a'         => $data['scores']['A'],
            'score_s'         => $data['scores']['S'],
            'score_e'         => $data['scores']['E'],
            'score_c'         => $data['scores']['C'],
            'code_riasec'     => $data['code'],
            'statut'          => 'nouveau',
            'source_page'     => $data['source_page'],
            'rdv_pris'        => 0,
            'relance_envoyee' => 0,
            'created_at'      => $now,
            'updated_at'      => $now,
        ];

        $formats = [
            '%s', '%s', '%s', '%s', '%d',
            '%d', '%d', '%d', '%d', '%d', '%d',
            '%s', '%s', '%s',
            '%d', '%d',
            '%s', '%s',
        ];

        $result = $wpdb->insert( $table, $insert, $formats );

        if ( false === $result ) {
            error_log( 'PraxiMet – Erreur insertion lead : ' . $wpdb->last_error );
            return false;
        }

        return (int) $wpdb->insert_id;
    }

    /**
     * Vérifie si un email existe déjà en base.
     *
     * @param string $email
     * @return bool
     */
    public static function email_existe( string $email ) {
        global $wpdb;

        $table = $wpdb->prefix . self::TABLE;

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE email = %s",
                $email
            )
        );

        return (int) $count > 0;
    }

    /**
     * Récupère tous les leads (pour le dashboard).
     *
     * @param array $args  Filtres optionnels
     * @return array
     */
    public static function get_leads( array $args = [] ) {
        global $wpdb;

        $table   = $wpdb->prefix . self::TABLE;
        $where   = '1=1';
        $values  = [];

        if ( ! empty( $args['statut'] ) ) {
            $where   .= ' AND statut = %s';
            $values[] = $args['statut'];
        }

        if ( ! empty( $args['search'] ) ) {
            $where   .= ' AND (nom LIKE %s OR prenom LIKE %s OR email LIKE %s)';
            $like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $values[] = $like;
            $values[] = $like;
            $values[] = $like;
        }

        $limit  = isset( $args['limit'] ) ? (int) $args['limit'] : 50;
        $offset = isset( $args['offset'] ) ? (int) $args['offset'] : 0;
        $order  = 'DESC';

        $sql = "SELECT * FROM {$table} WHERE {$where}
                ORDER BY created_at {$order}
                LIMIT %d OFFSET %d";

        $values[] = $limit;
        $values[] = $offset;

        if ( ! empty( $values ) ) {
            $sql = $wpdb->prepare( $sql, ...$values );
        }

        return $wpdb->get_results( $sql, ARRAY_A );
    }

    /**
     * Met à jour le statut d'un lead.
     *
     * @param int    $id
     * @param string $statut
     * @return bool
     */
    public static function mettre_a_jour_statut( int $id, string $statut ) {
        global $wpdb;

        $statuts_valides = ['nouveau','contacte','rdv_pris','converti','archive'];
        if ( ! in_array( $statut, $statuts_valides, true ) ) {
            return false;
        }

        $table  = $wpdb->prefix . self::TABLE;
        $result = $wpdb->update(
            $table,
            [
                'statut'     => $statut,
                'updated_at' => current_time( 'mysql' ),
            ],
            [ 'id' => $id ],
            [ '%s', '%s' ],
            [ '%d' ]
        );

        return false !== $result;
    }
}
