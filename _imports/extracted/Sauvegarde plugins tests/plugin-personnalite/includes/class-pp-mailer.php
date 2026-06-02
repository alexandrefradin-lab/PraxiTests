<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PP_Mailer {

    /**
     * À appeler une fois au init du plugin pour activer le SMTP si configuré.
     */
    public static function init() {
        add_action( 'phpmailer_init', array( __CLASS__, 'configure_smtp' ) );
    }

    /**
     * Configure PHPMailer en SMTP avec les réglages admin si un host est défini.
     */
    public static function configure_smtp( $phpmailer ) {
        $host = get_option( 'pp_smtp_host', '' );
        $user = get_option( 'pp_smtp_user', '' );
        $pass = get_option( 'pp_smtp_pass', '' );
        $port = intval( get_option( 'pp_smtp_port', 465 ) );
        $sec  = get_option( 'pp_smtp_secure', 'ssl' );

        if ( empty( $host ) || empty( $user ) || empty( $pass ) ) return;

        $phpmailer->isSMTP();
        $phpmailer->Host       = $host;
        $phpmailer->SMTPAuth   = true;
        $phpmailer->Username   = $user;
        $phpmailer->Password   = $pass;
        $phpmailer->Port       = $port;
        $phpmailer->SMTPSecure = $sec === 'none' ? '' : $sec;
    }

    /**
     * Envoie les résultats à l'utilisateur + copie admin.
     * Retourne true si au moins l'email utilisateur a été envoyé.
     */
    public static function envoyer_resultats( $prenom, $email, $scores_profil, $archetype = array(), $token = '' ) {
        $admin_email = get_option('pp_admin_email', get_option('admin_email'));
        $rdv_url     = get_option('pp_rdv_url', home_url('/contact'));
        $site_name   = get_option('pp_site_name_override','') ?: get_bloginfo('name');

        $arch_nom    = $archetype['nom']     ?? '';
        $arch_emoji  = $archetype['emoji']   ?? '';
        $arch_tag    = $archetype['tagline'] ?? '';
        $arch_rarete = $archetype['rarete']  ?? '';

        $sujet_user = $arch_nom
            ? "{$arch_emoji} {$arch_nom} — votre profil de personnalité"
            : "Vos résultats de personnalité — {$site_name}";

        // Expéditeur fixe @praxis-accompagnement.com pour conformité SPF/DKIM OVH.
        $from_email  = 'contact@praxis-accompagnement.com';
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <' . $from_email . '>',
            'Reply-To: ' . $site_name . ' <' . $admin_email . '>',
            'X-Mailer: Plugin-Personnalite/' . PP_VERSION,
        );

        // Email utilisateur
        $body_user = self::template_user($prenom, $scores_profil, $rdv_url, $site_name,
                                          $arch_nom, $arch_emoji, $arch_tag, $arch_rarete, $token);
        $sent_user = wp_mail($email, $sujet_user, $body_user, $headers);

        if (!$sent_user) {
            PP_Logger::error('mailer', 'Échec envoi email utilisateur', array('email'=>$email,'token'=>$token));
        } else {
            PP_Logger::info('mailer', 'Email résultats envoyé', array('email'=>$email,'archetype'=>$arch_nom));
        }

        // Copie admin (non bloquante)
        if ($admin_email && is_email($admin_email)) {
            $sujet_admin = "[{$site_name}] Nouveau test : {$arch_emoji}{$arch_nom} — {$prenom} ({$email})";
            $body_admin  = self::template_admin($prenom, $email, $scores_profil, $arch_nom, $arch_emoji, $token, $site_name);
            $sent_admin  = wp_mail($admin_email, $sujet_admin, $body_admin, $headers);
            if (!$sent_admin) {
                PP_Logger::warning('mailer', 'Échec copie admin', array('admin'=>$admin_email));
            }
        }

        return $sent_user;
    }

    public static function envoyer_relance( $row, $jours ) {
        $admin_email = get_option('pp_admin_email', get_option('admin_email'));
        $rdv_url     = get_option('pp_rdv_url', home_url('/contact'));
        $site_name   = get_option('pp_site_name_override','') ?: get_bloginfo('name');
        $profil_url  = $row->token ? home_url('/profil/'.$row->token) : $rdv_url;

        $opt_key = $jours === 3 ? 'pp_texte_relance_3j' : 'pp_texte_relance_8j';
        $default_intro = $jours === 3
            ? 'Il y a 3 jours, vous avez découvert votre profil de personnalité. Avez-vous envie d\'aller plus loin dans la connaissance de vous-même ? Un bilan de compétences peut vous aider à transformer cette clarté en un projet concret.'
            : 'Voilà 8 jours que vous avez découvert votre profil. Peut-être avez-vous des questions qui sont apparues depuis — sur vos talents, vos axes de développement, ou votre prochain cap professionnel ? C\'est exactement ce qu\'on explore ensemble dans un bilan de compétences.';
        $intro_text = get_option($opt_key, $default_intro);

        $arch = ! empty( $row->archetype_nom ) ? $row->archetype_nom : '';

        $sujet = $jours === 3
            ? ( $arch
                ? "{$row->prenom}, votre profil {$arch} vous réserve encore des surprises"
                : "{$row->prenom}, votre profil vous réserve encore des surprises" )
            : ( $arch
                ? "8 jours déjà — et si on transformait votre profil {$arch} en plan d'action, {$row->prenom} ?"
                : "8 jours déjà — et si on transformait votre profil en plan d'action, {$row->prenom} ?" );

        $from_email = 'contact@praxis-accompagnement.com';
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <' . $from_email . '>',
            'Reply-To: ' . $site_name . ' <' . $admin_email . '>',
        );
        $body = self::template_relance($row->prenom, '<p>'.wp_kses_post($intro_text).'</p>',
                                        $rdv_url, $profil_url, $site_name);
        $sent = wp_mail($row->email, $sujet, $body, $headers);
        if (!$sent) {
            PP_Logger::warning('relance', "Échec relance J+{$jours}", array('email'=>$row->email));
        }
        return $sent;
    }

    // ── Templates ─────────────────────────────────────────────────────────────

    private static function template_user($prenom, $profil, $rdv_url, $site_name,
                                           $arch_nom, $arch_emoji, $arch_tag, $arch_rarete, $token) {
        ob_start();
        include PP_PLUGIN_DIR . 'templates/email-resultats.php';
        $html = ob_get_clean();
        if ($html === false) {
            PP_Logger::error('mailer','ob_get_clean() a échoué pour template_user');
            return self::fallback_email($prenom, $site_name, $rdv_url, $token);
        }
        return $html;
    }

    /** Email de secours si le template PHP échoue. */
    private static function fallback_email( $prenom, $site_name, $rdv_url, $token ) {
        $profil_url  = $token ? home_url( '/profil/' . $token ) : $rdv_url;
        $prenom_safe = esc_html( $prenom );
        $site_safe   = esc_html( $site_name );
        return "<!DOCTYPE html><html><body style='font-family:Arial,sans-serif;'>"
             . "<h2>Bonjour {$prenom_safe},</h2>"
             . "<p>Vos résultats de personnalité sont disponibles en ligne :</p>"
             . "<p><a href='" . esc_url( $profil_url ) . "'>Voir mon profil complet →</a></p>"
             . "<p><a href='" . esc_url( $rdv_url )    . "'>Prendre rendez-vous →</a></p>"
             . "<p>— {$site_safe}</p>"
             . "</body></html>";
    }

    private static function template_admin($prenom, $email, $profil, $arch_nom, $arch_emoji, $token, $site_name) {
        $profil_url = $token ? home_url('/profil/'.$token) : admin_url('admin.php?page=pp-resultats');
        $arch_str   = $arch_nom ? '<li>Archétype : ' . esc_html($arch_emoji.' '.$arch_nom) . '</li>' : '';
        $token_str  = $token ? '<li>Profil : <a href="'.esc_url($profil_url).'">'.$token.'</a></li>' : '';
        $scores_html = '';
        foreach ((array)$profil as $p) {
            if (isset($p['label'],$p['score'])) {
                $scores_html .= '<li>' . esc_html($p['label']) . ' : ' . intval($p['score']) . '%</li>';
            }
        }
        $body = "<ul><li>Prénom : ".esc_html($prenom)."</li>"
              . "<li>Email : ".esc_html($email)."</li>"
              . $arch_str . $token_str . "</ul>"
              . "<ul>{$scores_html}</ul>"
              . '<p><a href="'.esc_url(admin_url('admin.php?page=pp-resultats')).'">Voir dans l\'admin →</a></p>';
        return self::wrap_html($body, $site_name);
    }

    private static function template_relance($prenom, $intro, $rdv_url, $profil_url, $site_name) {
        $content = '<p>Bonjour ' . esc_html($prenom) . ',</p>' . $intro
            . '<p style="text-align:center;margin:28px 0;">'
            . '<a href="'.esc_url($profil_url).'" style="background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff;padding:14px 32px;border-radius:999px;text-decoration:none;font-weight:bold;display:inline-block;">Revoir mon profil</a>'
            . '</p><p style="text-align:center;">'
            . '<a href="'.esc_url($rdv_url).'" style="color:#4F46E5;font-weight:600;">📅 Prendre rendez-vous</a>'
            . '</p><p>À bientôt,<br><strong>L\'équipe '.esc_html($site_name).'</strong></p>';
        return self::wrap_html($content, $site_name);
    }

    public static function wrap_html($content, $site_name) {
        $c1 = get_option('pp_color_primary', '#4F46E5');
        if (!preg_match('/^#[0-9A-Fa-f]{3,6}$/', $c1)) $c1 = '#4F46E5';
        return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="font-family:\'Segoe UI\',Arial,sans-serif;max-width:600px;margin:0 auto;padding:24px;color:#333;background:#f1f5f9;">
<div style="background:#fff;border-radius:12px;padding:32px;border-top:4px solid '.$c1.';">
<h2 style="color:'.$c1.';margin-top:0;">'.esc_html($site_name).'</h2>'
        .$content.'</div></body></html>';
    }
}
