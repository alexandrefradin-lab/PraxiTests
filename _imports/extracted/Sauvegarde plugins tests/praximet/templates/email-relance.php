<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Votre bilan de compétences</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:Arial,Helvetica,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9;padding:32px 0;">
  <tr>
    <td align="center">
      <table width="600" cellpadding="0" cellspacing="0"
             style="background:#ffffff;border-radius:8px;overflow:hidden;
                    box-shadow:0 2px 8px rgba(0,0,0,0.08);max-width:600px;width:100%;">

        <!-- En-tête -->
        <tr>
          <td style="background:#1e3a5f;padding:32px 40px;text-align:center;">
            <h1 style="color:#ffffff;margin:0;font-size:22px;font-weight:700;">
              <?php echo esc_html( get_bloginfo('name') ); ?>
            </h1>
          </td>
        </tr>

        <!-- Corps -->
        <tr>
          <td style="padding:40px;">

            <p style="font-size:18px;color:#1e3a5f;margin:0 0 8px;font-weight:600;">
              Bonjour <?php echo esc_html( $prenom ); ?>,
            </p>
            <p style="font-size:15px;color:#555;margin:0 0 20px;line-height:1.6;">
              Il y a quelques jours, vous avez découvert votre profil RIASEC
              <strong style="color:#1e3a5f;"><?php echo esc_html( $code ); ?></strong>.
            </p>
            <p style="font-size:15px;color:#555;margin:0 0 28px;line-height:1.6;">
              Avez-vous eu l'occasion de réfléchir à votre projet professionnel ?
              Un entretien découverte gratuit peut vous aider à
              <strong>clarifier vos options</strong> et voir si un bilan de compétences
              est fait pour vous.
            </p>

            <!-- Rappel code -->
            <table width="100%" cellpadding="0" cellspacing="0"
                   style="background:#f0f5fb;border-radius:8px;margin:0 0 28px;">
              <tr>
                <td style="padding:20px;text-align:center;">
                  <p style="margin:0 0 8px;font-size:13px;color:#888;">
                    Votre code RIASEC
                  </p>
                  <?php foreach ( str_split( $code ) as $lettre ) : ?>
                  <span style="display:inline-block;width:48px;height:48px;
                               background:#1e3a5f;color:#ffffff;
                               border-radius:8px;font-size:24px;font-weight:700;
                               line-height:48px;text-align:center;margin:0 3px;">
                    <?php echo esc_html( $lettre ); ?>
                  </span>
                  <?php endforeach; ?>
                </td>
              </tr>
            </table>

            <!-- CTA -->
            <?php if ( ! empty( $calendly_url ) ) : ?>
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="text-align:center;">
                  <a href="<?php echo esc_url( $calendly_url ); ?>"
                     style="display:inline-block;background:#1e3a5f;color:#ffffff;
                            text-decoration:none;padding:16px 36px;border-radius:6px;
                            font-size:16px;font-weight:700;">
                    📅 Réserver mon entretien gratuit
                  </a>
                  <p style="margin:14px 0 0;font-size:13px;color:#888;">
                    Sans engagement, 100% gratuit
                  </p>
                </td>
              </tr>
            </table>
            <?php endif; ?>

          </td>
        </tr>

        <!-- Pied de page -->
        <tr>
          <td style="background:#f4f6f9;padding:24px 40px;text-align:center;
                     border-top:1px solid #e8ecf0;">
            <p style="margin:0;font-size:12px;color:#999;line-height:1.6;">
              Vous recevez cet email car vous avez complété un test RIASEC sur
              <?php echo esc_html( get_bloginfo('name') ); ?>.<br>
              Pour ne plus recevoir de messages, répondez à cet email.
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
