<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PE_Mailer {

    public static function init() {
        add_action( 'phpmailer_init', array( __CLASS__, 'configure_smtp' ) );
    }

    public static function configure_smtp( $phpmailer ) {
        $host = get_option( 'pemo_smtp_host', '' );
        $user = get_option( 'pemo_smtp_user', '' );
        $pass = get_option( 'pemo_smtp_pass', '' );
        $port = intval( get_option( 'pemo_smtp_port', 465 ) );
        $sec  = get_option( 'pemo_smtp_secure', 'ssl' );

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
     */
    public static function envoyer_resultats( $prenom, $email, $results ) {
        $admin_email = get_option( 'pemo_admin_email', get_option( 'admin_email' ) );
        $rdv_url     = get_option( 'pemo_rdv_url', home_url( '/contact' ) );
        $site_name   = get_option( 'pemo_site_name', '' ) ?: get_bloginfo( 'name' );
        $from_email  = 'contact@praxis-accompagnement.com';

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <' . $from_email . '>',
            'Reply-To: ' . $site_name . ' <' . $admin_email . '>',
            'X-Mailer: PraxiEmo/' . PEMO_VERSION,
        );

        $sujet_user = sprintf(
            '[%s], votre profil d\'Intelligence Émotionnelle est prêt 🧠',
            esc_html( $prenom )
        );

        $body_user = self::template_user( $prenom, $results, $rdv_url, $site_name );
        $sent_user = wp_mail( $email, $sujet_user, $body_user, $headers );

        if ( ! $sent_user ) {
            PE_Logger::error( 'mailer', 'Échec envoi email utilisateur', array( 'email' => $email ) );
        } else {
            PE_Logger::info( 'mailer', 'Email résultats envoyé', array(
                'email' => $email,
                'qe'    => $results['score_global'],
            ) );
        }

        // Copie admin (non bloquante)
        if ( $admin_email && is_email( $admin_email ) ) {
            $sujet_admin = sprintf(
                '[%s] Nouveau test IE : %s (%s) — QE %d',
                $site_name,
                $prenom,
                $email,
                $results['score_global']
            );
            $body_admin = self::template_admin( $prenom, $email, $results, $site_name );
            $sent_admin = wp_mail( $admin_email, $sujet_admin, $body_admin, $headers );
            if ( ! $sent_admin ) {
                PE_Logger::warning( 'mailer', 'Échec copie admin', array( 'admin' => $admin_email ) );
            }
        }

        return $sent_user;
    }

    public static function envoyer_relance( $row, $jours ) {
        $admin_email = get_option( 'pemo_admin_email', get_option( 'admin_email' ) );
        $rdv_url     = get_option( 'pemo_rdv_url', home_url( '/contact' ) );
        $site_name   = get_option( 'pemo_site_name', '' ) ?: get_bloginfo( 'name' );
        $from_email  = 'contact@praxis-accompagnement.com';

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <' . $from_email . '>',
            'Reply-To: ' . $site_name . ' <' . $admin_email . '>',
        );

        if ( $jours === 3 ) {
            $sujet = sprintf( '%s, avez-vous relu vos résultats ? 🔍', $row->prenom );
            $intro = '<p>Il y a 3 jours, vous avez découvert votre profil d\'Intelligence Émotionnelle.</p>'
                   . '<p>Avez-vous eu le temps de réfléchir à ce que cela signifie concrètement pour vous — dans votre travail, vos relations, votre façon de prendre des décisions ?</p>'
                   . '<p>C\'est exactement ce qu\'on explore ensemble lors d\'un entretien de débriefing.</p>';
        } else {
            $sujet = sprintf( '8 jours après votre test — et si on passait à l\'action, %s ?', $row->prenom );
            $intro = '<p>Voilà 8 jours que vous avez découvert vos 16 dimensions émotionnelles.</p>'
                   . '<p>Si vous ressentez l\'envie d\'aller plus loin — de comprendre comment votre profil émotionnel impacte votre quotidien professionnel — un accompagnement Praxis peut vous aider à transformer cette conscience en changement réel.</p>';
        }

        $body = self::template_relance( $row->prenom, $intro, $rdv_url, $site_name );
        $sent = wp_mail( $row->email, $sujet, $body, $headers );

        if ( ! $sent ) {
            PE_Logger::warning( 'relance', "Échec relance J+{$jours}", array( 'email' => $row->email ) );
        }
        return $sent;
    }

    // ── Templates ─────────────────────────────────────────────────────────────

    private static function template_user( $prenom, $results, $rdv_url, $site_name ) {
        ob_start();
        include PEMO_PLUGIN_DIR . 'templates/email-resultats.php';
        $html = ob_get_clean();
        if ( $html === false || $html === '' ) {
            PE_Logger::error( 'mailer', 'ob_get_clean() a échoué pour template_user' );
            return self::fallback_email( $prenom, $site_name, $rdv_url );
        }
        return $html;
    }

    private static function fallback_email( $prenom, $site_name, $rdv_url ) {
        return '<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;">'
             . '<h2>Bonjour ' . esc_html( $prenom ) . ',</h2>'
             . '<p>Merci d\'avoir complété votre test d\'Intelligence Émotionnelle.</p>'
             . '<p><a href="' . esc_url( $rdv_url ) . '">Réserver un entretien de débriefing →</a></p>'
             . '<p>— ' . esc_html( $site_name ) . '</p>'
             . '</body></html>';
    }

    private static function template_admin( $prenom, $email, $results, $site_name ) {
        $dims    = PE_Calculator::get_dimensions();
        $scores  = '';
        foreach ( $dims as $dim_id => $dim ) {
            $score  = $results['dim_scores'][ $dim_id ] ?? 0;
            $scores .= '<li>' . esc_html( $dim['label'] ) . ' : ' . intval( $score ) . '/20</li>';
        }
        $content = '<ul>'
                 . '<li>Prénom : ' . esc_html( $prenom ) . '</li>'
                 . '<li>Email : ' . esc_html( $email ) . '</li>'
                 . '<li>QE Global : ' . intval( $results['score_global'] ) . '/320 — ' . esc_html( $results['niveau_qe'] ) . '</li>'
                 . '</ul>'
                 . '<ul>' . $scores . '</ul>'
                 . '<p><a href="' . esc_url( admin_url( 'admin.php?page=pemo-resultats' ) ) . '">Voir dans l\'admin →</a></p>';
        return self::wrap_html( $content, $site_name );
    }

    private static function template_relance( $prenom, $intro_html, $rdv_url, $site_name ) {
        $c1      = self::get_primary_color();
        $content = '<p>Bonjour <strong>' . esc_html( $prenom ) . '</strong>,</p>'
                 . wp_kses_post( $intro_html )
                 . '<p style="text-align:center;margin:28px 0;">'
                 . '<a href="' . esc_url( $rdv_url ) . '" style="background:linear-gradient(135deg,' . $c1 . ',#1E2A3A);color:#fff;padding:14px 32px;border-radius:999px;text-decoration:none;font-weight:bold;display:inline-block;">📅 Réserver mon entretien gratuit →</a>'
                 . '</p>'
                 . '<p>À bientôt,<br><strong>Alexandre — ' . esc_html( $site_name ) . '</strong></p>';
        return self::wrap_html( $content, $site_name );
    }

    public static function wrap_html( $content, $site_name ) {
        $c1 = self::get_primary_color();
        return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="font-family:\'Segoe UI\',Arial,sans-serif;max-width:600px;margin:0 auto;padding:24px;color:#333;background:#f1f5f9;">
<div style="background:#fff;border-radius:12px;padding:32px;border-top:4px solid ' . $c1 . ';">
<h2 style="color:' . $c1 . ';margin-top:0;">' . esc_html( $site_name ) . '</h2>'
        . $content . '</div></body></html>';
    }

    private static function get_primary_color() {
        $c = get_option( 'pemo_color_primary', '#E8541A' );
        return preg_match( '/^#[0-9A-Fa-f]{3,6}$/', $c ) ? $c : '#E8541A';
    }
}
