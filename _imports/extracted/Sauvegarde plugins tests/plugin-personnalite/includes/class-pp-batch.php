<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Mode batch : envoi d'invitations à une liste d'emails.
 * Chaque invité reçoit un lien unique /test/[token_invite]
 * qui pré-remplit son email et suit la source.
 */
class PP_Batch {

    const TABLE = 'pp_batch';

    public static function install() {
        global $wpdb;
        $t  = $wpdb->prefix . self::TABLE;
        $cs = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS {$t} (
            id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            token        VARCHAR(64)  NOT NULL DEFAULT '',
            campagne     VARCHAR(200) NOT NULL DEFAULT '',
            email        VARCHAR(200) NOT NULL DEFAULT '',
            prenom       VARCHAR(100) NOT NULL DEFAULT '',
            statut       VARCHAR(20) NOT NULL DEFAULT 'envoye' COMMENT 'envoye|ouvert|commence|complete',
            date_envoi   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            date_complete DATETIME    NULL,
            resultat_id  BIGINT UNSIGNED NULL,
            PRIMARY KEY (id),
            UNIQUE KEY token (token),
            KEY email (email),
            KEY campagne (campagne)
        ) {$cs};";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        // Migration ENUM → VARCHAR (upgrade depuis versions précédentes)
        global $wpdb;
        $t = $wpdb->prefix . self::TABLE;
        $col_info = $wpdb->get_row("SHOW COLUMNS FROM {$t} LIKE 'statut'");
        if ($col_info && stripos($col_info->Type, 'enum') !== false) {
            $wpdb->query(
                "ALTER TABLE {$t} MODIFY COLUMN statut VARCHAR(20) NOT NULL DEFAULT 'envoye'"
            );
        }
    }

    public static function init() {
        add_action( 'wp_ajax_pp_batch_send',   array(__CLASS__, 'handle_send') );
        add_action( 'init',                    array(__CLASS__, 'handle_invite_redirect') );
    }

    /** Génère et envoie les invitations depuis l'admin. */
    public static function handle_send() {
        if ( ! current_user_can('manage_options') ) wp_die('Accès refusé.');
        check_ajax_referer('pp_batch_nonce', 'nonce');

        $campagne = sanitize_text_field( $_POST['campagne'] ?? 'Campagne' );
        $raw_list = sanitize_textarea_field( $_POST['liste'] ?? '' );
        $message  = wp_kses_post( $_POST['message_perso'] ?? '' );

        if ( empty($raw_list) ) {
            wp_send_json_error(array('message'=>'Liste vide.'));
        }

        $lines   = array_filter( array_map('trim', explode("\n", $raw_list)) );
        $sent    = 0; $errors = array();

        foreach ( $lines as $line ) {
            // Format accepté : "email" OU "email, Prénom" OU "email;Prénom"
            $parts  = preg_split('/[,;]\s*/', $line);
            $email  = sanitize_email( trim($parts[0]) );
            $prenom = isset($parts[1]) ? sanitize_text_field(trim($parts[1])) : '';

            if ( ! is_email($email) ) {
                $errors[] = $line; continue;
            }

            $token = self::create_invite($campagne, $email, $prenom);
            self::send_invite($email, $prenom, $token, $campagne, $message);
            $sent++;
        }

        wp_send_json_success(array(
            'sent'   => $sent,
            'errors' => count($errors),
            'err_list'=> implode(', ', array_slice($errors,0,5)),
        ));
    }

    /** Crée une entrée batch et retourne le token. */
    public static function create_invite($campagne, $email, $prenom) {
        global $wpdb;
        $token = bin2hex(random_bytes(16));
        $wpdb->insert(
            $wpdb->prefix . self::TABLE,
            array('token'=>$token,'campagne'=>$campagne,
                  'email'=>$email,'prenom'=>$prenom,'statut'=>'envoye'),
        );
        return $token;
    }

    /** Redirige /test/[token] vers le formulaire avec pré-remplissage. */
    public static function handle_invite_redirect() {
        if ( ! isset($_GET['pp_invite']) ) return;
        $token = sanitize_text_field($_GET['pp_invite']);
        if ( ! preg_match('/^[a-f0-9]{32}$/', $token) ) return;

        global $wpdb;
        $t   = $wpdb->prefix . self::TABLE;
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$t} WHERE token = %s", $token));
        if ( ! $row ) return;

        // Marquer comme ouvert
        if ( $row->statut === 'envoye' ) {
            $wpdb->update($wpdb->prefix.self::TABLE,
                array('statut'=>'ouvert'), array('id'=>$row->id));
        }

        // Trouver la page avec le shortcode
        $test_url = get_option('pp_test_url', home_url('/'));
        $redirect = add_query_arg(array(
            'pp_email'    => urlencode($row->email),
            'pp_prenom'   => urlencode($row->prenom),
            'pp_source'   => urlencode('batch:' . $row->campagne),
            'pp_invite_tk'=> $token,
        ), $test_url);

        wp_safe_redirect( $redirect, 302 );
        exit;
    }

    /** Envoie l'email d'invitation. */
    private static function send_invite($email, $prenom, $token, $campagne, $message_perso) {
        $site      = get_bloginfo('name');
        $admin_em  = get_option('pp_admin_email', get_option('admin_email'));
        $invite_url = add_query_arg('pp_invite', $token, home_url('/'));
        $c1_raw    = get_option('pp_color_primary', '#4F46E5');
        $c1        = ( preg_match('/^#[0-9A-Fa-f]{3,6}$/', $c1_raw) ) ? $c1_raw : '#4F46E5';

        $salut = $prenom ? "Bonjour <strong>" . esc_html($prenom) . "</strong>," : "Bonjour,";

        $body = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
        <body style="margin:0;padding:0;background:#f1f5f9;font-family:\'Segoe UI\',Arial,sans-serif;">
        <table width="100%" cellpadding="0" cellspacing="0" style="padding:32px 16px;">
        <tr><td align="center"><table width="100%" style="max-width:560px;">
        <tr><td style="background:' . $c1 . ';border-radius:16px 16px 0 0;padding:32px;text-align:center;color:#fff;">
          <h1 style="margin:0;font-size:22px;font-weight:800;">Invitation — PraxiMum</h1>
          <p style="margin:8px 0 0;opacity:.85;font-size:14px;">' . esc_html($campagne) . '</p>
        </td></tr>
        <tr><td style="background:#fff;padding:32px;border-radius:0 0 16px 16px;">
          <p style="margin:0 0 16px;font-size:15px;color:#334155;line-height:1.7;">' . $salut . '</p>
          <p style="margin:0 0 16px;font-size:15px;color:#334155;line-height:1.7;">
            Vous êtes invité(e) à réaliser un PraxiMum Big Five dans le cadre de
            <strong>' . esc_html($campagne) . '</strong>.<br>
            Le test prend environ 12 minutes et vous recevrez vos résultats immédiatement.
          </p>
          ' . ($message_perso ? '<div style="background:#f8fafc;border-left:4px solid '.$c1.';padding:12px 16px;border-radius:0 8px 8px 0;margin:0 0 20px;font-size:14px;color:#475569;">' . $message_perso . '</div>' : '') . '
          <div style="text-align:center;margin:28px 0;">
            <a href="' . esc_url($invite_url) . '"
               style="display:inline-block;background:' . $c1 . ';color:#fff;text-decoration:none;padding:16px 36px;border-radius:999px;font-size:15px;font-weight:700;">
              Démarrer mon test →
            </a>
          </div>
          <p style="font-size:12px;color:#94a3b8;margin:0;">
            Ce lien vous est personnel. Vos résultats restent confidentiels.<br>
            Envoyé par ' . esc_html($site) . '
          </p>
        </td></tr>
        </table></td></tr></table></body></html>';

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site . ' <' . $admin_em . '>',
        );
        wp_mail($email, "Invitation au PraxiMum — " . $campagne, $body, $headers);
    }

    /** Génère un token de campagne partageable pour la vue équipe.
     *  Déterministe : même nom → même token. Utilise HMAC-SHA256 tronqué.
     */
    public static function get_campagne_token( $campagne ) {
        // AUTH_KEY peut être absent sur des installations non standard — fallback sur NONCE_KEY ou ABSPATH
        $secret = defined('AUTH_KEY')  ? AUTH_KEY
                : ( defined('NONCE_KEY') ? NONCE_KEY : md5(ABSPATH) );
        return substr( hash_hmac( 'sha256', $campagne, $secret ), 0, 16 );
    }

    /** Retourne les profils complets d'une campagne pour la vue équipe. */
    public static function get_team_profils($campagne_token) {
        global $wpdb;
        $tb = $wpdb->prefix . self::TABLE;
        $tr = $wpdb->prefix . PP_DB::TABLE;

        // Trouver le nom de campagne depuis le token
        $rows = $wpdb->get_results(
            "SELECT DISTINCT campagne FROM {$tb}"
        );
        $campagne = null;
        foreach ($rows as $r) {
            if (self::get_campagne_token($r->campagne) === $campagne_token) {
                $campagne = $r->campagne; break;
            }
        }
        if (!$campagne) return null;

        // Récupérer les invités complétés avec leurs résultats
        return $wpdb->get_results($wpdb->prepare(
            "SELECT b.prenom as inv_prenom, b.email, b.statut,
                    r.id as result_id, r.prenom, r.token as result_token,
                    r.archetype_nom, r.archetype_data,
                    r.score_O, r.score_C, r.score_E, r.score_A, r.score_N,
                    r.score_O_T, r.score_C_T, r.score_E_T, r.score_A_T, r.score_N_T
             FROM {$tb} b
             LEFT JOIN {$tr} r ON r.email = b.email
             WHERE b.campagne = %s AND b.statut = 'complete' AND r.id IS NOT NULL
             ORDER BY r.date_soumis ASC LIMIT 50",
            $campagne
        ));
    }

    public static function get_campagnes() {
        global $wpdb;
        $t = $wpdb->prefix . self::TABLE;
        return $wpdb->get_results(
            "SELECT campagne,
                    COUNT(*) AS total,
                    SUM(statut='ouvert') AS ouverts,
                    SUM(statut='complete') AS complets,
                    MIN(date_envoi) AS date_debut
             FROM {$t} GROUP BY campagne ORDER BY date_debut DESC LIMIT 20"
        );
    }

    public static function get_invites($campagne) {
        global $wpdb;
        $t = $wpdb->prefix . self::TABLE;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$t} WHERE campagne = %s ORDER BY date_envoi DESC", $campagne
        ));
    }
}
