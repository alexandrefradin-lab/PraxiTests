<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Couche sécurité centralisée.
 * — Rate limiting sur les soumissions
 * — Protection fichiers PDF (serve via PHP, pas via URL directe)
 * — Headers de sécurité sur les pages du plugin
 * — Audit log des accès admin sensibles
 */
class PP_Security {

    const RATE_LIMIT_KEY    = 'pp_rate_';
    const RATE_LIMIT_MAX    = 5;   // soumissions par fenêtre
    const RATE_LIMIT_WINDOW = 3600; // 1 heure

    public static function init() {
        // Headers sécurité sur les pages publiques plugin
        add_action('template_redirect', array(__CLASS__, 'security_headers'), 1);
        // Intercepter les téléchargements PDF directs
        add_action('init', array(__CLASS__, 'protect_pdf_directory'));
        // Log accès admin sensibles
        add_action('admin_init', array(__CLASS__, 'audit_admin_actions'));
    }

    // ── Rate limiting ─────────────────────────────────────────────────────────

    /**
     * Vérifie le rate limit pour une IP/action.
     * Retourne true si l'action est permise, false si bloquée.
     */
    public static function check_rate_limit($action, $identifier = null) {
        $ip  = self::get_ip();
        $key = self::RATE_LIMIT_KEY . $action . '_' . md5(($identifier ?: $ip));
        $current = (int) get_transient($key);
        if ($current >= self::RATE_LIMIT_MAX) {
            PP_Logger::warning('security', "Rate limit atteint : {$action}",
                array('ip'=>$ip,'count'=>$current));
            return false;
        }
        set_transient($key, $current + 1, self::RATE_LIMIT_WINDOW);
        return true;
    }

    /** Réinitialise le compteur (après succès). */
    public static function reset_rate_limit($action, $identifier = null) {
        $ip  = self::get_ip();
        $key = self::RATE_LIMIT_KEY . $action . '_' . md5(($identifier ?: $ip));
        delete_transient($key);
    }

    // ── Headers de sécurité ───────────────────────────────────────────────────

    public static function security_headers() {
        if (!is_singular()) return;
        $is_pp_page = get_query_var('pp_token','') || get_query_var('pp_equipe','')
                   || get_query_var('pp_delete_token','') || get_query_var('pp_export_token','');
        if (!$is_pp_page) return;

        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            // Ne pas envoyer HSTS ici — c'est au niveau serveur
        }
    }

    // ── Protection répertoire PDF ─────────────────────────────────────────────

    public static function protect_pdf_directory() {
        // S'assurer que le .htaccess du dossier PDF est présent
        $upload = wp_upload_dir();
        $pdf_dir = trailingslashit($upload['basedir']) . 'pp-rapports/';
        if (is_dir($pdf_dir)) {
            $htaccess = $pdf_dir . '.htaccess';
            if (!file_exists($htaccess)) {
                file_put_contents($htaccess,
                    "Options -Indexes\n<FilesMatch \"\\.pdf$\">\n  Order allow,deny\n  Deny from all\n</FilesMatch>\n"
                );
            }
        }
    }

    // ── Audit admin ───────────────────────────────────────────────────────────

    public static function audit_admin_actions() {
        if (!current_user_can('manage_options')) return;
        $page = sanitize_key($_GET['page'] ?? '');
        if (!in_array($page, array('pp-resultats','pp-batch','pp-codes'))) return;

        $user = wp_get_current_user();
        $action = sanitize_text_field($_GET['action'] ?? 'view');

        // Logger seulement les exports CSV (pas chaque vue de liste)
        if (isset($_GET['pp_export_csv'])) {
            PP_Logger::info('audit', "Export CSV par {$user->user_login}",
                array('page'=>$page,'filters'=>$_GET));
        }
        if (isset($_GET['detail'])) {
            PP_Logger::info('audit', "Accès fiche résultat #{$_GET['detail']} par {$user->user_login}");
        }
    }

    // ── Validation token sécurisée ────────────────────────────────────────────

    public static function validate_token($token) {
        if (!is_string($token)) return false;
        if (!preg_match('/^[a-f0-9]{32}$/', $token)) return false;
        return true;
    }

    public static function validate_campagne_token($token) {
        if (!is_string($token)) return false;
        if (!preg_match('/^[a-f0-9]{16}$/', $token)) return false;
        return true;
    }

    // ── Nettoyage output ──────────────────────────────────────────────────────

    /** Sanitise une couleur hex pour usage CSS direct (pas via esc_attr). */
    public static function safe_color($hex, $fallback = '#E8541A') {
        $hex = trim($hex);
        if (preg_match('/^#[0-9A-Fa-f]{3,6}$/', $hex)) return $hex;
        return $fallback;
    }

    /** IP cliente — compatible Cloudflare / reverse proxy. */
    public static function get_ip() {
        $keys = array('HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR');
        foreach ($keys as $k) {
            if (!empty($_SERVER[$k])) {
                $ip = trim(explode(',', $_SERVER[$k])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
        return '0.0.0.0';
    }

    // ── Vérification accès profil ─────────────────────────────────────────────

    /**
     * Vérifie qu'un token de profil est valide et que le profil existe.
     * Retourne le row ou null.
     */
    public static function get_profile_or_die($token) {
        if (!self::validate_token($token)) {
            PP_Logger::warning('security', 'Token malformé', array('token'=>substr($token,0,10)));
            status_header(404);
            wp_die('Profil introuvable.', 404);
        }
        $row = PP_DB::get_by_token($token);
        if (!$row) {
            PP_Logger::info('security', 'Token inexistant', array('token'=>$token));
            status_header(404);
            wp_die('Ce profil n\'existe pas ou a été supprimé.', 404);
        }
        return $row;
    }
}
