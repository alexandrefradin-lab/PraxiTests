<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * PraxiCare — Mailing
 *
 * Architecture identique à PraxiMum :
 * - Pas de configuration SMTP dans le plugin (géré par le plugin SMTP tiers installé sur le site)
 * - Pas de header From: forcé (le plugin SMTP tiers impose son propre expéditeur)
 * - wp_mail() seul suffit, le plugin SMTP tiers le configure automatiquement
 * - Reply-To uniquement pour orienter les réponses vers l'admin
 */

function praxicare_send_email( $prenom, $email, $profil, $scores ) {
    $admin_email = get_option( 'praxicare_admin_email', get_option( 'admin_email' ) );
    $site_name   = get_bloginfo( 'name' );

    // Pas de From: — laissé au plugin SMTP tiers pour conformité SPF/DKIM OVH
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: ' . $site_name . ' <' . $admin_email . '>',
        'X-Mailer: PraxiCare/' . PRAXICARE_VERSION,
    );

    $sujet = 'Votre diagnostic PraxiCare — ' . $prenom;

    // Préconisations HTML — gestion array (lien Calendly) ou string
    $preconisations_html = '';
    foreach ( $profil['preconisations'] as $preco ) {
        if ( is_array( $preco ) ) {
            $texte = isset( $preco['texte'] ) ? esc_html( $preco['texte'] ) : '';
            $lien  = isset( $preco['lien'] )  ? esc_url( $preco['lien'] )  : '';
            if ( $lien ) {
                $preconisations_html .= '<li style="margin-bottom:10px;"><a href="' . $lien . '" style="color:#E8541A;font-weight:600;">' . $texte . '</a></li>';
            } else {
                $preconisations_html .= '<li style="margin-bottom:10px;">' . $texte . '</li>';
            }
        } else {
            $preconisations_html .= '<li style="margin-bottom:10px;">' . esc_html( $preco ) . '</li>';
        }
    }

    $urgence_block = '';
    if ( in_array( $profil['niveau'], array( 'critique', 'rouge' ), true ) ) {
        $urgence_block = '
      <tr><td style="padding:0 40px 24px;">
        <div style="background:#EEF3F8;border-left:4px solid #5B8DB8;padding:16px 20px;border-radius:4px;text-align:center;">
          <p style="margin:0;font-weight:600;color:#1E2A3A;font-size:15px;">💙 Si vous traversez une période difficile, vous n\'êtes pas seul(e).</p>
          <p style="margin:8px 0 0;color:#555;font-size:14px;">Des professionnels sont disponibles pour vous écouter, gratuitement et en toute confidentialité, 24h/24, au <strong>3114</strong>.</p>
        </div>
      </td></tr>';
    }

    $body = praxicare_email_body( $prenom, $profil, $preconisations_html, $urgence_block, $scores );

    $sent = wp_mail( $email, $sujet, $body, $headers );
    if ( ! $sent ) {
        error_log( 'PraxiCare — Echec envoi email utilisateur : ' . $email );
    }

    // Copie admin
    if ( $admin_email && is_email( $admin_email ) ) {
        $sujet_admin = '[PraxiCare] Nouveau test - ' . $profil['emoji'] . ' ' . $profil['titre'] . ' - ' . $prenom . ' (' . $email . ')';
        $body_admin  = praxicare_email_admin( $prenom, $email, $profil, $scores );
        wp_mail( $admin_email, $sujet_admin, $body_admin, $headers );
    }

    return $sent;
}

function praxicare_email_body( $prenom, $profil, $preconisations_html, $urgence_block, $scores ) {
    return '<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#F5F7FA;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F5F7FA;padding:32px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:#FFFFFF;border-radius:12px;overflow:hidden;max-width:600px;width:100%;">

  <tr><td style="background:#002345;padding:28px 40px;">
    <p style="margin:0;color:#FFFFFF;font-size:22px;font-weight:700;">PraxiCare</p>
    <p style="margin:4px 0 0;color:#A8B8D8;font-size:13px;">Diagnostic souffrance au travail — Praxis Accompagnement</p>
  </td></tr>

  <tr><td style="padding:32px 40px 24px;">
    <p style="margin:0;font-size:16px;color:#333;">Bonjour <strong>' . esc_html( $prenom ) . '</strong>,</p>
    <p style="margin:16px 0 0;font-size:15px;color:#555;line-height:1.6;">Merci d\'avoir pris le temps de faire le point sur votre situation. C\'est un acte de lucidite, souvent le premier pas vers un mieux.</p>
    <p style="margin:12px 0 0;font-size:13px;color:#888;font-style:italic;">Ce rapport est un outil d\'aide a la reflexion. Il ne constitue pas un diagnostic medical. En cas de doute, consultez votre medecin.</p>
  </td></tr>

  <tr><td style="padding:0 40px 24px;">
    <div style="background:#EEF3FB;border-radius:8px;padding:20px 24px;">
      <p style="margin:0;font-size:13px;color:#002345;font-weight:600;text-transform:uppercase;letter-spacing:1px;">Votre profil</p>
      <p style="margin:8px 0 0;font-size:20px;font-weight:700;color:#002345;">' . esc_html( $profil['emoji'] ) . ' ' . esc_html( $profil['titre'] ) . '</p>
      <p style="margin:12px 0 0;font-size:15px;color:#444;line-height:1.6;">' . esc_html( $profil['texte'] ) . '</p>
    </div>
  </td></tr>

  ' . $urgence_block . '

  <tr><td style="padding:0 40px 24px;">
    <p style="margin:0 0 12px;font-size:16px;font-weight:700;color:#002345;">Vos preconisations personnalisees</p>
    <ul style="margin:0;padding-left:20px;color:#333;font-size:15px;line-height:1.8;">' . $preconisations_html . '</ul>
  </td></tr>

  <tr><td style="padding:0 40px 24px;">
    <p style="margin:0 0 12px;font-size:16px;font-weight:700;color:#002345;">Vos scores</p>
    <table width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;color:#333;">
      <tr style="background:#EEF3FB;"><td style="padding:10px 16px;font-weight:600;">Charge de travail</td><td style="text-align:right;padding:10px 16px;">' . absint( $scores['demandes'] ) . ' / 36</td></tr>
      <tr><td style="padding:10px 16px;font-weight:600;">Autonomie</td><td style="text-align:right;padding:10px 16px;">' . absint( $scores['latitude'] ) . ' / 36</td></tr>
      <tr style="background:#EEF3FB;"><td style="padding:10px 16px;font-weight:600;">Soutien social</td><td style="text-align:right;padding:10px 16px;">' . absint( $scores['soutien'] ) . ' / 32</td></tr>
      <tr><td style="padding:10px 16px;font-weight:600;">Epuisement emotionnel</td><td style="text-align:right;padding:10px 16px;">' . absint( $scores['ee'] ) . ' / 27</td></tr>
      <tr style="background:#EEF3FB;"><td style="padding:10px 16px;font-weight:600;">Detachement affectif</td><td style="text-align:right;padding:10px 16px;">' . absint( $scores['dp'] ) . ' / 15</td></tr>
      <tr><td style="padding:10px 16px;font-weight:600;">Accomplissement personnel</td><td style="text-align:right;padding:10px 16px;">' . absint( $scores['ap'] ) . ' / 24</td></tr>
    </table>
  </td></tr>

  <tr><td style="padding:0 40px 32px;">
    <div style="background:#EEF3FB;border-radius:8px;padding:20px 24px;text-align:center;">
      <p style="margin:0;font-size:15px;color:#333;font-weight:600;">Vous souhaitez aller plus loin ?</p>
      <p style="margin:8px 0 16px;font-size:14px;color:#555;">Reservez un entretien gratuit de 15 minutes avec Alexandre pour faire le point sur votre situation.</p>
      <a href="https://calendly.com/alex-fradin/15min" style="display:inline-block;background:#E8541A;color:#FFFFFF;font-weight:700;font-size:15px;padding:12px 28px;border-radius:50px;text-decoration:none;">Reserver un entretien gratuit</a>
    </div>
  </td></tr>

  <tr><td style="background:#F5F7FA;padding:20px 40px;text-align:center;">
    <p style="margin:0;font-size:12px;color:#999;">Praxis Accompagnement · praxis-accompagnement.com</p>
    <p style="margin:8px 0 0;font-size:12px;color:#999;">En cas de detresse intense : 3114 (gratuit, 24h/24)</p>
  </td></tr>

</table>
</td></tr>
</table>
</body>
</html>';
}

function praxicare_email_admin( $prenom, $email, $profil, $scores ) {
    return '<div style="font-family:Arial,sans-serif;font-size:14px;color:#333;">'
         . '<h3 style="color:#002345;">Nouveau test PraxiCare</h3>'
         . '<ul>'
         . '<li><strong>Prenom :</strong> ' . esc_html( $prenom ) . '</li>'
         . '<li><strong>Email :</strong> ' . esc_html( $email ) . '</li>'
         . '<li><strong>Profil :</strong> ' . esc_html( $profil['emoji'] . ' ' . $profil['titre'] ) . '</li>'
         . '<li><strong>Niveau :</strong> ' . esc_html( $profil['niveau'] ) . '</li>'
         . '</ul><ul>'
         . '<li>Demandes : ' . absint( $scores['demandes'] ) . '/36</li>'
         . '<li>Latitude : ' . absint( $scores['latitude'] ) . '/36</li>'
         . '<li>Soutien : ' . absint( $scores['soutien'] ) . '/32</li>'
         . '<li>EE : ' . absint( $scores['ee'] ) . '/27</li>'
         . '<li>DP : ' . absint( $scores['dp'] ) . '/15</li>'
         . '<li>AP : ' . absint( $scores['ap'] ) . '/24</li>'
         . '</ul>'
         . '<p><a href="' . esc_url( admin_url( 'admin.php?page=praxicare-results' ) ) . '">Voir dans l\'admin</a></p>'
         . '</div>';
}
