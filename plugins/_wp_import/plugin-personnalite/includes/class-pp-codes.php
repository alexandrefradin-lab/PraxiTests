<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Codes d'accès anonymes — distribuer des codes plutôt que des liens nommés.
 * Permet l'anonymat complet : l'email n'est pas collecté avant le test.
 * Table : pp_codes
 */
class PP_Codes {

    const TABLE = 'pp_codes';

    public static function install() {
        global $wpdb;
        $t  = $wpdb->prefix . self::TABLE;
        $cs = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS {$t} (
            id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            code        VARCHAR(20)  NOT NULL DEFAULT '',
            campagne    VARCHAR(200) NOT NULL DEFAULT '',
            statut      VARCHAR(20) NOT NULL DEFAULT 'disponible' COMMENT 'disponible|utilise',
            date_cree   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            date_utilise DATETIME NULL,
            resultat_id BIGINT UNSIGNED NULL,
            PRIMARY KEY (id),
            UNIQUE KEY code (code),
            KEY campagne (campagne),
            KEY statut (statut)
        ) {$cs};";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        // Migration ENUM → VARCHAR (upgrade depuis versions précédentes)
        global $wpdb;
        $t = $wpdb->prefix . self::TABLE;
        $col_info = $wpdb->get_row("SHOW COLUMNS FROM {$t} LIKE 'statut'");
        if ($col_info && stripos($col_info->Type, 'enum') !== false) {
            $wpdb->query(
                "ALTER TABLE {$t} MODIFY COLUMN statut VARCHAR(20) NOT NULL DEFAULT 'disponible'"
            );
        }
    }

    public static function init() {
        add_action( 'wp_ajax_pp_generer_codes',    array(__CLASS__, 'handle_generer') );
        add_action( 'wp_ajax_pp_valider_code',     array(__CLASS__, 'handle_valider') );
        add_action( 'wp_ajax_nopriv_pp_valider_code', array(__CLASS__, 'handle_valider') );
    }

    /** Génère N codes pour une campagne. */
    public static function handle_generer() {
        if ( ! current_user_can('manage_options') ) wp_die('Accès refusé.');
        check_ajax_referer('pp_codes_nonce','nonce');

        $campagne = sanitize_text_field( $_POST['campagne'] ?? 'Codes' );
        $n        = min(500, max(1, intval($_POST['nombre'] ?? 20)));
        $prefix   = strtoupper(preg_replace('/[^A-Z0-9]/', '', substr($campagne,0,4)));

        $codes = array();
        $tries = 0;
        while ( count($codes) < $n && $tries < $n*3 ) {
            $tries++;
            $code = $prefix . '-' . strtoupper(substr(bin2hex(random_bytes(3)),0,6));
            if ( ! self::code_exists($code) ) {
                self::insert_code($code, $campagne);
                $codes[] = $code;
            }
        }
        wp_send_json_success(array('codes'=>$codes, 'n'=>count($codes)));
    }

    /** Valide un code (endpoint public, appelé avant le test). */
    public static function handle_valider() {
        check_ajax_referer('pp_nonce','nonce');
        $code = strtoupper(sanitize_text_field($_POST['code'] ?? ''));

        if ( ! preg_match('/^[A-Z0-9]{1,4}-[A-Z0-9]{6}$/', $code) ) {
            wp_send_json_error(array('message'=>'Format de code invalide.'));
        }

        global $wpdb;
        $t   = $wpdb->prefix . self::TABLE;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$t} WHERE code = %s", $code
        ));

        if ( ! $row ) {
            wp_send_json_error(array('message'=>'Code inconnu.'));
        }
        if ( $row->statut === 'utilise' ) {
            wp_send_json_error(array('message'=>'Ce code a déjà été utilisé.'));
        }

        // Marquer comme utilisé
        $wpdb->update($wpdb->prefix.self::TABLE,
            array('statut'=>'utilise','date_utilise'=>current_time('mysql')),
            array('id'=>$row->id)
        );

        wp_send_json_success(array(
            'campagne' => $row->campagne,
            'code_id'  => $row->id,
            'source'   => 'code:' . $row->campagne,
        ));
    }

    private static function code_exists($code) {
        global $wpdb;
        return (bool) $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}" . self::TABLE . " WHERE code = %s", $code
        ));
    }

    private static function insert_code($code, $campagne) {
        global $wpdb;
        $wpdb->insert($wpdb->prefix.self::TABLE,
            array('code'=>$code,'campagne'=>$campagne));
    }

    public static function get_stats($campagne = '') {
        global $wpdb;
        $t = $wpdb->prefix . self::TABLE;
        $where = $campagne ? $wpdb->prepare("WHERE campagne = %s", $campagne) : '';
        return $wpdb->get_results(
            "SELECT campagne,
                    COUNT(*) AS total,
                    SUM(statut='disponible') AS disponibles,
                    SUM(statut='utilise') AS utilises
             FROM {$t} {$where}
             GROUP BY campagne ORDER BY date_cree DESC LIMIT 20"
        );
    }

    public static function get_codes($campagne, $limit = 200) {
        global $wpdb;
        $t = $wpdb->prefix . self::TABLE;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$t} WHERE campagne = %s ORDER BY date_cree DESC LIMIT %d",
            $campagne, $limit
        ));
    }
}
