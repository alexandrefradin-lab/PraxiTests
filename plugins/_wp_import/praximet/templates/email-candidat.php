<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Votre profil RIASEC</title>
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
            <h1 style="color:#ffffff;margin:0;font-size:24px;font-weight:700;
                       letter-spacing:1px;">
              <?php echo esc_html( get_bloginfo('name') ); ?>
            </h1>
            <p style="color:#a8c4e0;margin:8px 0 0;font-size:14px;">
              Bilan de compétences
            </p>
          </td>
        </tr>

        <!-- Corps -->
        <tr>
          <td style="padding:40px;">

            <p style="font-size:18px;color:#1e3a5f;margin:0 0 8px;font-weight:600;">
              Bonjour <?php echo esc_html( $prenom ); ?>,
            </p>
            <p style="font-size:15px;color:#555;margin:0 0 28px;line-height:1.6;">
              Merci d'avoir complété votre test RIASEC. Voici votre profil de personnalité professionnelle.
            </p>

            <!-- Code RIASEC -->
            <table width="100%" cellpadding="0" cellspacing="0"
                   style="background:#f0f5fb;border-radius:8px;margin:0 0 28px;">
              <tr>
                <td style="padding:28px;text-align:center;">
                  <p style="margin:0 0 12px;font-size:13px;color:#888;
                             text-transform:uppercase;letter-spacing:1px;">
                    Votre code RIASEC
                  </p>
                  <div style="display:inline-block;">
                    <?php foreach ( str_split( $code ) as $lettre ) : ?>
                    <span style="display:inline-block;width:56px;height:56px;
                                 background:#1e3a5f;color:#ffffff;
                                 border-radius:8px;font-size:28px;font-weight:700;
                                 line-height:56px;text-align:center;margin:0 4px;">
                      <?php echo esc_html( $lettre ); ?>
                    </span>
                    <?php endforeach; ?>
                  </div>
                </td>
              </tr>
            </table>

            <!-- Détail des 3 types -->
            <?php foreach ( $profil as $index => $type ) :
                $rangs = ['Profil dominant', 'Profil secondaire', 'Profil tertiaire'];
                $rang  = $rangs[ $index ] ?? '';
            ?>
            <table width="100%" cellpadding="0" cellspacing="0"
                   style="border-left:4px solid #1e3a5f;margin:0 0 20px;
                          padding-left:0;background:#fafbfd;border-radius:0 6px 6px 0;">
              <tr>
                <td style="padding:16px 20px;">
                  <p style="margin:0 0 4px;font-size:11px;color:#888;
                             text-transform:uppercase;letter-spacing:1px;">
                    <?php echo esc_html( $rang ); ?>
                  </p>
                  <p style="margin:0 0 6px;font-size:16px;font-weight:700;color:#1e3a5f;">
                    <?php echo esc_html( $type['label'] ); ?>
                  </p>
                  <p style="margin:0;font-size:14px;color:#555;line-height:1.6;">
                    <?php echo esc_html( $type['description'] ); ?>
                  </p>
                </td>
              </tr>
            </table>
            <?php endforeach; ?>

            <!-- CTA Calendly -->
            <?php if ( ! empty( $calendly_url ) ) : ?>
            <table width="100%" cellpadding="0" cellspacing="0"
                   style="background:#e8f0fb;border-radius:8px;margin:28px 0 0;">
              <tr>
                <td style="padding:28px;text-align:center;">
                  <p style="margin:0 0 16px;font-size:15px;color:#1e3a5f;font-weight:600;">
                    Envie d'aller plus loin ?
                  </p>
                  <p style="margin:0 0 20px;font-size:14px;color:#555;line-height:1.5;">
                    Réservez dès maintenant un entretien découverte gratuit
                    pour explorer votre projet de reconversion.
                  </p>
                  <a href="<?php echo esc_url( $calendly_url ); ?>"
                     style="display:inline-block;background:#1e3a5f;color:#ffffff;
                            text-decoration:none;padding:14px 32px;border-radius:6px;
                            font-size:15px;font-weight:700;">
                    📅 Réserver mon entretien
                  </a>
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
              <a href="<?php echo esc_url( home_url() ); ?>"
                 style="color:#1e3a5f;text-decoration:none;">
                <?php echo esc_html( get_bloginfo('name') ); ?>
              </a>.<br>
              Conformément au RGPD, vous pouvez demander la suppression de vos données
              en répondant à cet email.
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
