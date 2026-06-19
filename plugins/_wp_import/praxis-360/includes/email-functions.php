<?php
/**
 * Envoi des emails Praxis 360 via SMTP OVH (ssl0.ovh.net:465 SSL).
 * Les identifiants SMTP sont stockés dans les réglages du plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Praxis360_Mailer {

    /** Réglages SMTP/expéditeur (option WP). */
    public static function settings() {
        $defaults = array(
            'smtp_host'   => 'ssl0.ovh.net',
            'smtp_port'   => 465,
            'smtp_secure' => 'ssl',
            'smtp_user'   => '',
            'smtp_pass'   => '',
            'from_email'  => get_option( 'admin_email' ),
            'from_name'   => 'Praxis Accompagnement',
            'admin_email' => get_option( 'admin_email' ),
        );
        return wp_parse_args( get_option( 'praxis360_settings', array() ), $defaults );
    }

    /** Configure PHPMailer sur OVH si des identifiants SMTP sont renseignés. */
    public static function configure_phpmailer( $phpmailer ) {
        $s = self::settings();
        if ( empty( $s['smtp_user'] ) || empty( $s['smtp_pass'] ) ) {
            return; // Pas de SMTP configuré → wp_mail() utilise l'envoi par défaut.
        }
        $phpmailer->isSMTP();
        $phpmailer->Host       = $s['smtp_host'];
        $phpmailer->Port       = (int) $s['smtp_port'];
        $phpmailer->SMTPSecure = $s['smtp_secure']; // 'ssl' pour port 465
        $phpmailer->SMTPAuth   = true;
        $phpmailer->Username    = $s['smtp_user'];
        $phpmailer->Password    = $s['smtp_pass'];
        $phpmailer->setFrom( $s['from_email'], $s['from_name'] );
    }

    /** Enveloppe d'envoi HTML. */
    protected static function send( $to, $subject, $body_html ) {
        $headers = array( 'Content-Type: text/html; charset=UTF-8' );
        $s       = self::settings();
        $headers[] = 'From: ' . $s['from_name'] . ' <' . $s['from_email'] . '>';
        return wp_mail( $to, $subject, self::wrap( $body_html ), $headers );
    }

    /** Gabarit HTML commun (couleurs Praxis). */
    protected static function wrap( $inner ) {
        $accent = '#E95A00';
        $navy   = '#002345';
        ob_start();
        ?>
        <div style="margin:0;padding:24px;background:#f9f9fb;font-family:Arial,Helvetica,sans-serif;color:#212934;">
          <div style="max-width:560px;margin:0 auto;background:#ffffff;border:1px solid #e2e2e2;border-radius:10px;overflow:hidden;">
            <div style="background:<?php echo esc_attr( $navy ); ?>;height:6px;"></div>
            <div style="padding:28px;">
              <?php echo $inner; // déjà échappé en amont ?>
            </div>
            <div style="padding:16px 28px;border-top:1px solid #e2e2e2;font-size:12px;color:#6d6968;">
              Praxis Accompagnement — démarche de développement professionnel.
            </div>
          </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /** Bouton CTA. */
    protected static function button( $url, $label ) {
        return '<p style="text-align:center;margin:28px 0;">'
            . '<a href="' . esc_url( $url ) . '" style="display:inline-block;background:#E95A00;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:8px;font-weight:bold;">'
            . esc_html( $label ) . '</a></p>';
    }

    /** URL de passation pour un répondant. */
    public static function passation_url( $token ) {
        $page = get_option( 'praxis360_page_url', home_url( '/praxis-360/' ) );
        return add_query_arg( 'p360_token', $token, $page );
    }

    /** URL du rapport pour le sujet. */
    public static function report_url( $subject_token ) {
        $page = get_option( 'praxis360_page_url', home_url( '/praxis-360/' ) );
        return add_query_arg( 'p360_report', $subject_token, $page );
    }

    // --- Emails ---------------------------------------------------------------

    public static function invite_evaluator( $respondent, $campaign ) {
        $url  = self::passation_url( $respondent->token );
        $name = esc_html( $campaign->subject_name );
        $body = '<h2 style="color:#002345;margin-top:0;">' . $name . ' aimerait votre avis</h2>'
            . '<p>Bonjour,</p>'
            . '<p>Dans le cadre d\'une démarche de développement professionnel, <strong>' . $name . '</strong> souhaite recueillir des retours sincères sur ses compétences relationnelles — et votre regard fait partie de ceux qui comptent.</p>'
            . '<p>Cela vous prendra <strong>environ 10 minutes</strong>, et vos réponses resteront <strong>strictement confidentielles</strong> (regroupées, jamais nominatives).</p>'
            . self::button( $url, 'Donner mon avis' )
            . '<p>Merci d\'avance pour votre contribution.</p>';
        $subject = sprintf( '%s aimerait votre avis (≈ 10 min, confidentiel)', $campaign->subject_name );
        return self::send( $respondent->email, $subject, $body );
    }

    public static function invite_self( $respondent, $campaign ) {
        $url  = self::passation_url( $respondent->token );
        $body = '<h2 style="color:#002345;margin-top:0;">Votre évaluation 360° est prête</h2>'
            . '<p>Bonjour ' . esc_html( $campaign->subject_name ) . ',</p>'
            . '<p>Votre démarche 360° est lancée. Commencez par <strong>votre auto-évaluation</strong> (≈ 10 min) ; vos évaluateurs reçoivent leur invitation de leur côté.</p>'
            . self::button( $url, 'Faire mon auto-évaluation' );
        return self::send( $respondent->email, 'Votre évaluation 360° est prête', $body );
    }

    public static function reminder( $respondent, $campaign ) {
        $url  = self::passation_url( $respondent->token );
        $name = esc_html( $campaign->subject_name );
        $body = '<h2 style="color:#002345;margin-top:0;">Petit rappel</h2>'
            . '<p>Bonjour, vous n\'avez peut-être pas eu le temps… Il reste quelques jours pour partager votre regard, qui aidera vraiment ' . $name . '. 10 minutes suffisent.</p>'
            . self::button( $url, 'Donner mon avis maintenant' );
        return self::send( $respondent->email, sprintf( 'Petit rappel : votre avis pour %s (≈ 10 min)', $campaign->subject_name ), $body );
    }

    public static function send_results( $campaign ) {
        if ( empty( $campaign->subject_email ) ) {
            return false;
        }
        $url  = self::report_url( $campaign->subject_token );
        $body = '<h2 style="color:#002345;margin-top:0;">Votre rapport 360° est disponible</h2>'
            . '<p>Bonjour ' . esc_html( $campaign->subject_name ) . ', les retours sont là. Découvrez vos forces, vos axes de progrès et vos angles morts — ces zones où le regard des autres diffère du vôtre.</p>'
            . self::button( $url, 'Consulter mon rapport' )
            . '<p>Prenez le temps de le lire dans un moment calme. Et si vous souhaitez en faire un vrai levier, un échange avec votre coach Praxis peut vous aider à transformer ces retours en plan d\'action.</p>';
        return self::send( $campaign->subject_email, 'Votre rapport 360° est disponible', $body );
    }

    public static function notify_admin_closed( $campaign, $counts ) {
        $s     = self::settings();
        $lines = array();
        $labels = Praxis360_Items::relations();
        foreach ( $counts as $rel => $n ) {
            $lines[] = esc_html( isset( $labels[ $rel ] ) ? $labels[ $rel ] : $rel ) . ' : ' . (int) $n;
        }
        $body = '<h2 style="color:#002345;margin-top:0;">Campagne 360 clôturée</h2>'
            . '<p>La campagne « <strong>' . esc_html( $campaign->subject_name ) . '</strong> » est terminée.</p>'
            . '<p>Réponses reçues :<br>' . implode( '<br>', $lines ) . '</p>'
            . '<p>Le rapport est consultable depuis le tableau de bord Praxis 360.</p>';
        return self::send( $s['admin_email'], sprintf( 'Campagne 360 « %s » : clôturée', $campaign->subject_name ), $body );
    }
}

// Branche la config SMTP OVH sur PHPMailer.
add_action( 'phpmailer_init', array( 'Praxis360_Mailer', 'configure_phpmailer' ) );
