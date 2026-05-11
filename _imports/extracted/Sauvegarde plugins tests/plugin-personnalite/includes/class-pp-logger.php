<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Logger centralisé — tous les événements importants passent ici.
 * Stocke dans la table pp_logs + option WP pour les erreurs critiques.
 */
class PP_Logger {

    const TABLE    = 'pp_logs';
    const LEVELS    = array('debug', 'info', 'warning', 'error', 'critical');
    const MIN_LEVEL = 'info'; // Ne pas stocker debug en BDD par défaut
    const MAX_ROWS = 2000; // rotation automatique

    public static function install() {
        global $wpdb;
        $t  = $wpdb->prefix . self::TABLE;
        $cs = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS {$t} (
            id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            level      VARCHAR(10)  NOT NULL DEFAULT 'info',
            context    VARCHAR(60)  NOT NULL DEFAULT '',
            message    TEXT         NOT NULL,
            data       TEXT         NULL,
            created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY level (level),
            KEY created_at (created_at)
        ) {$cs};";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function debug($ctx, $msg, $data = null)    { self::write('debug',    $ctx, $msg, $data); }
    public static function info($ctx, $msg, $data = null)     { self::write('info',     $ctx, $msg, $data); }
    public static function warning($ctx, $msg, $data = null)  { self::write('warning',  $ctx, $msg, $data); }
    public static function error($ctx, $msg, $data = null)    { self::write('error',    $ctx, $msg, $data); }
    public static function critical($ctx, $msg, $data = null) {
        self::write('critical', $ctx, $msg, $data);
        // Alerte email admin sur critique
        $admin = get_option('pp_admin_email', get_option('admin_email'));
        $site  = get_bloginfo('name');
        wp_mail(
            $admin,
            "[{$site}] ⚠️ Erreur critique — {$ctx}",
            "Message : {$msg}\n\nDonnées : " . wp_json_encode( $data, JSON_UNESCAPED_UNICODE ) . "\n\nDate : " . current_time('mysql')
        );
    }

    private static function write($level, $ctx, $msg, $data) {
        global $wpdb;
        // Toujours écrire dans error_log PHP
        error_log("[PP:{$level}][{$ctx}] {$msg}" . ($data ? ' | ' . wp_json_encode($data) : ''));

        // Écrire en BDD seulement info+
        if ( $level === 'debug' ) return;
        $wpdb->insert(
            $wpdb->prefix . self::TABLE,
            array(
                'level'   => $level,
                'context' => substr($ctx, 0, 60),
                'message' => substr($msg, 0, 500),
                'data'    => $data ? wp_json_encode($data, JSON_UNESCAPED_UNICODE) : null,
            )
        );
        // Rotation légère : ne vérifier qu'une fois sur 50 inserts (économie de requête)
        if ( mt_rand(1, 50) === 1 ) {
            $table = $wpdb->prefix . self::TABLE;
            $count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore
            if ( $count > self::MAX_ROWS ) {
                $keep_from = $count - self::MAX_ROWS + 100;
                $del_id    = (int) $wpdb->get_var( // phpcs:ignore
                    "SELECT id FROM {$table} ORDER BY id ASC LIMIT 1 OFFSET {$keep_from}"
                );
                if ( $del_id ) {
                    $wpdb->query( "DELETE FROM {$table} WHERE id <= {$del_id}" ); // phpcs:ignore
                }
            }
        }
    }

    public static function get_recent($level = '', $limit = 100) {
        global $wpdb;
        $t = $wpdb->prefix . self::TABLE;
        if ($level) {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$t} WHERE level = %s ORDER BY id DESC LIMIT %d", $level, $limit
            ));
        }
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$t} ORDER BY id DESC LIMIT %d", $limit
        ));
    }

    public static function clear() {
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}" . self::TABLE);
    }
}
