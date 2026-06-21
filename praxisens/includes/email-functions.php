<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Configuration SMTP OVH (mutualisé).
 * À personnaliser : PRAXISENS_SMTP_USER / PRAXISENS_SMTP_PASS / PRAXISENS_FROM.
 */
if ( ! defined( 'PRAXISENS_SMTP_HOST' ) ) { define( 'PRAXISENS_SMTP_HOST', 'ssl0.ovh.net' ); }
if ( ! defined( 'PRAXISENS_SMTP_PORT' ) ) { define( 'PRAXISENS_SMTP_PORT', 465 ); }
if ( ! defined( 'PRAXISENS_SMTP_USER' ) ) { define( 'PRAXISENS_SMTP_USER', 'contact@votre-domaine.fr' ); }
if ( ! defined( 'PRAXISENS_SMTP_PASS' ) ) { define( 'PRAXISENS_SMTP_PASS', 'A_DEFINIR' ); }
if ( ! defined( 'PRAXISENS_FROM' ) )      { define( 'PRAXISENS_FROM', 'contact@votre-domaine.fr' ); }
if ( ! defined( 'PRAXISENS_FROM_NAME' ) ) { define( 'PRAXISENS_FROM_NAME', 'Praxis Accompagnement' ); }

add_action( 'phpmailer_init', 'praxisens_configure_smtp' );
function praxisens_configure_smtp( $phpmailer ) {
    if ( PRAXISENS_SMTP_PASS === 'A_DEFINIR' ) {
        return; // SMTP non configuré : on laisse le mail() par défaut.
    }
    $phpmailer->isSMTP();
    $phpmailer->Host       = PRAXISENS_SMTP_HOST;
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = PRAXISENS_SMTP_PORT;
    $phpmailer->Username   = PRAXISENS_SMTP_USER;
    $phpmailer->Password   = PRAXISENS_SMTP_PASS;
    $phpmailer->SMTPSecure = 'ssl';
    $phpmailer->From       = PRAXISENS_FROM;
    $phpmailer->FromName   = PRAXISENS_FROM_NAME;
}

function praxisens_send_results_email( $first_name, $email, $scores ) {
    $dims  = Praxisens::dimensions();
    $hello = $first_name ? esc_html( $first_name ) : 'Bonjour';

    $bar = function( $label, $pct, $color ) {
        $pct = (int) $pct;
        return '<tr><td style="padding:6px 0;font:14px Arial,sans-serif;color:#1f2937;width:180px;">' . esc_html( $label ) . '</td>'
            . '<td style="padding:6px 0;"><div style="background:#eef2f7;border-radius:8px;height:14px;width:100%;max-width:280px;">'
            . '<div style="background:' . esc_attr( $color ) . ';height:14px;border-radius:8px;width:' . $pct . '%;"></div></div></td>'
            . '<td style="padding:6px 0 6px 10px;font:bold 14px Arial,sans-serif;color:#111827;">' . $pct . '%</td></tr>';
    };

    $rows  = $bar( $dims['EOE']['label'], $scores['EOE'], $dims['EOE']['color'] );
    $rows .= $bar( $dims['AES']['label'], $scores['AES'], $dims['AES']['color'] );
    $rows .= $bar( $dims['LST']['label'], $scores['LST'], $dims['LST']['color'] );

    $body = '<div style="max-width:560px;margin:0 auto;font:15px Arial,sans-serif;color:#1f2937;">'
        . '<h2 style="color:#7c3aed;">' . $hello . ', voici votre profil de sensibilité</h2>'
        . '<p style="font-size:17px;"><strong>Score global : ' . (int) $scores['global'] . '%</strong> — ' . esc_html( $scores['profile'] ) . '</p>'
        . '<p>' . Praxisens::band_text( $scores['global'] ) . '</p>'
        . '<table style="width:100%;border-collapse:collapse;margin:18px 0;">' . $rows . '</table>'
        . '<p style="color:#6b7280;font-size:13px;">Ce résultat est une photographie de votre fonctionnement sensoriel, pas un diagnostic médical. '
        . 'Pour aller plus loin, l\'accompagnement Praxis vous aide à transformer cette sensibilité en ressource.</p>'
        . '<p style="margin-top:24px;">À bientôt,<br><strong>L\'équipe Praxis Accompagnement</strong></p>'
        . '</div>';

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . PRAXISENS_FROM_NAME . ' <' . PRAXISENS_FROM . '>',
    );

    return wp_mail( $email, 'Votre profil d\'hypersensibilité', $body, $headers );
}
