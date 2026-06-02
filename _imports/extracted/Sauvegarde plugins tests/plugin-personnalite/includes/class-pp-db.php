<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PP_DB {

    const TABLE   = 'pp_resultats';
    const VERSION = '1.3'; // v1.4.1 plugin — colonne relance_bloquee

    public static function install() {
        global $wpdb;
        $t   = $wpdb->prefix . self::TABLE;
        $cs  = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS {$t} (
            id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            token           VARCHAR(64)     NOT NULL DEFAULT '',
            prenom          VARCHAR(100)    NOT NULL DEFAULT '',
            email           VARCHAR(200)    NOT NULL DEFAULT '',
            reponses        LONGTEXT        NOT NULL,
            scores          LONGTEXT        NOT NULL,
            score_O         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            score_C         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            score_E         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            score_A         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            score_N         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            score_DS        SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            score_O_T       SMALLINT UNSIGNED NOT NULL DEFAULT 50,
            score_C_T       SMALLINT UNSIGNED NOT NULL DEFAULT 50,
            score_E_T       SMALLINT UNSIGNED NOT NULL DEFAULT 50,
            score_A_T       SMALLINT UNSIGNED NOT NULL DEFAULT 50,
            score_N_T       SMALLINT UNSIGNED NOT NULL DEFAULT 50,
            scores_facette  LONGTEXT        NOT NULL,
            archetype_nom   VARCHAR(100)    NOT NULL DEFAULT '',
            archetype_data  LONGTEXT        NOT NULL DEFAULT '',
            date_soumis     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            consentement    TINYINT(1)      NOT NULL DEFAULT 0,
            source          VARCHAR(200)    NOT NULL DEFAULT '',
            relance_3j      TINYINT(1)      NOT NULL DEFAULT 0,
            relance_8j      TINYINT(1)      NOT NULL DEFAULT 0,
            relance_bloquee TINYINT(1)      NOT NULL DEFAULT 0,
            rdv_clique      TINYINT(1)      NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY token (token),
            KEY email (email),
            KEY date_soumis (date_soumis)
        ) {$cs};";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        // Migration : recharger les colonnes à chaque étape pour fiabilité
        $cols = $wpdb->get_col( "DESCRIBE {$t}", 0 );

        if ( ! in_array( 'token', $cols ) ) {
            $wpdb->query( "ALTER TABLE {$t} ADD COLUMN token VARCHAR(64) NOT NULL DEFAULT '' AFTER id" );
            $wpdb->query( "ALTER TABLE {$t} ADD UNIQUE KEY token (token)" );
            $cols = $wpdb->get_col( "DESCRIBE {$t}", 0 );
        }
        // Migration v1.2 — SMALLINT pour les scores
        if ( ! empty($cols) ) {
            $score_cols = array('score_O','score_C','score_E','score_A','score_N','score_DS',
                                'score_O_T','score_C_T','score_E_T','score_A_T','score_N_T');
            foreach ($score_cols as $col) {
                $col_info = $wpdb->get_row("SHOW COLUMNS FROM {$t} LIKE '{$col}'");
                if ($col_info && stripos($col_info->Type, 'tinyint') !== false) {
                    $wpdb->query("ALTER TABLE {$t} MODIFY COLUMN {$col} SMALLINT UNSIGNED NOT NULL DEFAULT 0");
                }
            }
        }
        if ( ! in_array( 'archetype_nom', $cols ) ) {
            $wpdb->query( "ALTER TABLE {$t} ADD COLUMN archetype_nom VARCHAR(100) NOT NULL DEFAULT '' AFTER scores_facette" );
            $wpdb->query( "ALTER TABLE {$t} ADD COLUMN archetype_data LONGTEXT NOT NULL DEFAULT '' AFTER archetype_nom" );
            $cols = $wpdb->get_col( "DESCRIBE {$t}", 0 );
        }
        if ( ! in_array( 'relance_bloquee', $cols ) ) {
            $wpdb->query( "ALTER TABLE {$t} ADD COLUMN relance_bloquee TINYINT(1) NOT NULL DEFAULT 0 AFTER relance_8j" );
        }

        update_option( 'pp_db_version', self::VERSION );
    }

    /** Génère un token URL-safe unique. */
    public static function generate_token() {
        return bin2hex( random_bytes( 16 ) ); // 32 chars hex
    }

    public static function insert( $data ) {
        global $wpdb;
        $token = self::generate_token();
        $arch  = $data['archetype'] ?? array();

        $result = $wpdb->insert(
            $wpdb->prefix . self::TABLE,
            array(
                'token'          => $token,
                'prenom'         => sanitize_text_field( $data['prenom'] ),
                'email'          => sanitize_email( $data['email'] ),
                'reponses'       => wp_json_encode( $data['reponses'] ),
                'scores'         => wp_json_encode( $data['scores'] ),
                'score_O'        => intval( $data['scores']['scores_dim']['O']['pct']  ?? 0 ),
                'score_C'        => intval( $data['scores']['scores_dim']['C']['pct']  ?? 0 ),
                'score_E'        => intval( $data['scores']['scores_dim']['E']['pct']  ?? 0 ),
                'score_A'        => intval( $data['scores']['scores_dim']['A']['pct']  ?? 0 ),
                'score_N'        => intval( $data['scores']['scores_dim']['N']['pct']  ?? 0 ),
                'score_DS'       => intval( $data['scores']['score_DS']['pct']         ?? 0 ),
                'score_O_T'      => intval( $data['scores']['scores_dim']['O']['T']    ?? 50 ),
                'score_C_T'      => intval( $data['scores']['scores_dim']['C']['T']    ?? 50 ),
                'score_E_T'      => intval( $data['scores']['scores_dim']['E']['T']    ?? 50 ),
                'score_A_T'      => intval( $data['scores']['scores_dim']['A']['T']    ?? 50 ),
                'score_N_T'      => intval( $data['scores']['scores_dim']['N']['T']    ?? 50 ),
                'scores_facette' => wp_json_encode( $data['scores']['scores_facette'] ?? array() ),
                'archetype_nom'  => sanitize_text_field( $arch['nom'] ?? '' ),
                'archetype_data' => wp_json_encode( $arch ),
                'consentement'   => intval( $data['consentement'] ),
                'source'         => sanitize_text_field( $data['source'] ?? '' ),
            )
        );
        if ( false === $result || 0 === $wpdb->insert_id ) {
            $last_error = $wpdb->last_error;
            $last_query = $wpdb->last_query;
            if ( class_exists('PP_Logger') ) {
                PP_Logger::error( 'db', 'wpdb->insert() a échoué', array(
                    'last_error' => $last_error,
                    'last_query' => substr( $last_query, 0, 500 ),
                ) );
            }
            // Relancer une exception pour que le retry dans le shortcode soit utile
            throw new \RuntimeException( 'INSERT échoué : ' . $last_error );
        }
        return array( 'id' => $wpdb->insert_id, 'token' => $token );
    }

    public static function get_by_token( $token ) {
        global $wpdb;
        $t = $wpdb->prefix . self::TABLE;
        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$t} WHERE token = %s", sanitize_text_field( $token ) )
        );
    }

    public static function get_all( $page = 1, $per_page = 20, $filters = array() ) {
        global $wpdb;
        $t      = $wpdb->prefix . self::TABLE;
        $where  = array( '1=1' );
        $args   = array();

        if ( ! empty( $filters['archetype'] ) ) {
            $where[] = 'archetype_nom = %s';
            $args[]  = $filters['archetype'];
        }
        if ( ! empty( $filters['ds_eleve'] ) ) {
            $where[] = 'score_DS >= 70';
        }
        if ( ! empty( $filters['search'] ) ) {
            $where[] = '(prenom LIKE %s OR email LIKE %s)';
            $s = '%' . $wpdb->esc_like( $filters['search'] ) . '%';
            $args[] = $s; $args[] = $s;
        }

        $where_sql = implode( ' AND ', $where );
        $offset    = ( $page - 1 ) * $per_page;
        $args[]    = $per_page;
        $args[]    = $offset;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$t} WHERE {$where_sql} ORDER BY date_soumis DESC LIMIT %d OFFSET %d",
                ...$args
            )
        );
    }

    public static function count( $filters = array() ) {
        global $wpdb;
        $t     = $wpdb->prefix . self::TABLE;
        $where = array( '1=1' );
        $args  = array();

        if ( ! empty( $filters['archetype'] ) ) {
            $where[] = 'archetype_nom = %s';
            $args[]  = $filters['archetype'];
        }
        if ( ! empty( $filters['ds_eleve'] ) ) {
            $where[] = 'score_DS >= 70';
        }
        if ( ! empty( $filters['search'] ) ) {
            $where[] = '(prenom LIKE %s OR email LIKE %s)';
            $s = '%' . $wpdb->esc_like( $filters['search'] ) . '%';
            $args[] = $s; $args[] = $s;
        }

        $where_sql = implode( ' AND ', $where );
        if ( count( $args ) ) {
            return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$t} WHERE {$where_sql}", ...$args ) );
        }
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t} WHERE {$where_sql}" );
    }

    public static function get_one( $id ) {
        global $wpdb;
        $t = $wpdb->prefix . self::TABLE;
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t} WHERE id = %d", $id ) );
    }

    public static function get_archetypes_list() {
        global $wpdb;
        $t = $wpdb->prefix . self::TABLE;
        return $wpdb->get_col( "SELECT DISTINCT archetype_nom FROM {$t} WHERE archetype_nom != '' ORDER BY archetype_nom" );
    }

    public static function get_pending_relances( $jours ) {
        global $wpdb;
        $t   = $wpdb->prefix . self::TABLE;
        $col = $jours === 3 ? 'relance_3j' : 'relance_8j';
        $dt  = wp_date( 'Y-m-d H:i:s', strtotime( "-{$jours} days" ) );
        return $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM {$t} WHERE {$col} = 0 AND relance_bloquee = 0 AND date_soumis <= %s", $dt )
        );
    }

    public static function set_relance_bloquee( $id, $valeur ) {
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . self::TABLE,
            array( 'relance_bloquee' => $valeur ? 1 : 0 ),
            array( 'id' => intval( $id ) )
        );
    }

    public static function mark_relance( $id, $jours ) {
        global $wpdb;
        $col = $jours === 3 ? 'relance_3j' : 'relance_8j';
        $wpdb->update( $wpdb->prefix . self::TABLE, array( $col => 1 ), array( 'id' => $id ) );
    }

    /**
     * Export CSV — utilise une pagination interne pour éviter les pics mémoire.
     * Retourne un itérateur de lignes (via générateur PHP 8.0+).
     * Pour les appels historiques qui attendent un array, passer $as_array = true.
     *
     * @param array $filters
     * @param bool  $as_array true = retourne un tableau (comportement legacy), false = générateur
     * @param int   $batch_size taille des lots pour le mode générateur
     */
    public static function export_csv( $filters = array(), $as_array = true, $batch_size = 500 ) {
        global $wpdb;
        $t     = $wpdb->prefix . self::TABLE;
        $where = array( '1=1' );
        $args  = array();

        if ( ! empty( $filters['archetype'] ) ) {
            $where[] = 'archetype_nom = %s'; $args[] = $filters['archetype'];
        }
        if ( ! empty( $filters['ds_eleve'] ) ) {
            $where[] = 'score_DS >= 70';
        }
        if ( ! empty( $filters['search'] ) ) {
            $where[] = '(prenom LIKE %s OR email LIKE %s)';
            $s = '%' . $wpdb->esc_like( $filters['search'] ) . '%';
            $args[] = $s; $args[] = $s;
        }
        $where_sql = implode( ' AND ', $where );
        $cols      = 'id, token, prenom, email, archetype_nom,
                      score_O, score_C, score_E, score_A, score_N, score_DS,
                      score_O_T, score_C_T, score_E_T, score_A_T, score_N_T,
                      date_soumis, consentement, source, relance_3j, relance_8j, rdv_clique';

        if ( $as_array ) {
            // Mode legacy — pour compatibilité avec le code existant
            // LIMIT de sécurité à 5000 pour éviter les OOM
            $limit_args = array_merge( $args, array( 5000 ) );
            $query = "SELECT {$cols} FROM {$t} WHERE {$where_sql} ORDER BY date_soumis DESC LIMIT %d";
            return $wpdb->get_results( $wpdb->prepare( $query, ...$limit_args ), ARRAY_A );
        }

        // Mode streaming — générateur par lots
        return self::export_csv_generator( $t, $cols, $where_sql, $args, $batch_size );
    }

    /** Générateur interne — itère par lots de $batch_size pour limiter la mémoire. */
    private static function export_csv_generator( $t, $cols, $where_sql, $args, $batch_size ) {
        global $wpdb;
        $offset = 0;
        while ( true ) {
            $batch_args = array_merge( $args, array( $batch_size, $offset ) );
            $query      = "SELECT {$cols} FROM {$t} WHERE {$where_sql} ORDER BY id ASC LIMIT %d OFFSET %d";
            $rows       = $wpdb->get_results( $wpdb->prepare( $query, ...$batch_args ), ARRAY_A );
            if ( empty( $rows ) ) break;
            foreach ( $rows as $row ) {
                yield $row;
            }
            if ( count( $rows ) < $batch_size ) break;
            $offset += $batch_size;
        }
    }
}
