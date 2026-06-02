<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nouveau lead PraxiMet</title>
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
          <td style="background:#1e3a5f;padding:28px 40px;">
            <p style="margin:0 0 4px;font-size:12px;color:#a8c4e0;
                       text-transform:uppercase;letter-spacing:1px;">
              PraxiMet — Nouveau lead
            </p>
            <h1 style="color:#ffffff;margin:0;font-size:22px;font-weight:700;">
              🎯 <?php echo esc_html( $prenom . ' ' . $nom ); ?> vient de compléter le test RIASEC
            </h1>
          </td>
        </tr>

        <!-- Corps -->
        <tr>
          <td style="padding:36px 40px;">

            <!-- Coordonnées -->
            <h2 style="font-size:14px;text-transform:uppercase;letter-spacing:1px;
                       color:#888;margin:0 0 16px;font-weight:600;">
              Coordonnées
            </h2>
            <table width="100%" cellpadding="0" cellspacing="0"
                   style="background:#f8fafc;border-radius:8px;margin:0 0 28px;">
              <tr>
                <td style="padding:20px 24px;">
                  <table width="100%" cellpadding="4" cellspacing="0">
                    <tr>
                      <td style="font-size:13px;color:#888;width:120px;">Prénom</td>
                      <td style="font-size:14px;color:#1e3a5f;font-weight:600;">
                        <?php echo esc_html( $prenom ); ?>
                      </td>
                    </tr>
                    <tr>
                      <td style="font-size:13px;color:#888;">Nom</td>
                      <td style="font-size:14px;color:#1e3a5f;font-weight:600;">
                        <?php echo esc_html( $nom ); ?>
                      </td>
                    </tr>
                    <tr>
                      <td style="font-size:13px;color:#888;">Email</td>
                      <td style="font-size:14px;">
                        <a href="mailto:<?php echo esc_attr( $email ); ?>"
                           style="color:#1e3a5f;font-weight:600;">
                          <?php echo esc_html( $email ); ?>
                        </a>
                      </td>
                    </tr>

                  </table>
                </td>
              </tr>
            </table>

            <!-- Code RIASEC -->
            <h2 style="font-size:14px;text-transform:uppercase;letter-spacing:1px;
                       color:#888;margin:0 0 16px;font-weight:600;">
              Profil RIASEC
            </h2>
            <table width="100%" cellpadding="0" cellspacing="0"
                   style="background:#f0f5fb;border-radius:8px;margin:0 0 20px;">
              <tr>
                <td style="padding:20px 24px;text-align:center;">
                  <?php foreach ( str_split( $code ) as $lettre ) : ?>
                  <span style="display:inline-block;width:52px;height:52px;
                               background:#1e3a5f;color:#ffffff;
                               border-radius:8px;font-size:26px;font-weight:700;
                               line-height:52px;text-align:center;margin:0 4px;">
                    <?php echo esc_html( $lettre ); ?>
                  </span>
                  <?php endforeach; ?>
                </td>
              </tr>
            </table>

            <!-- Scores détaillés -->
            <table width="100%" cellpadding="0" cellspacing="0"
                   style="margin:0 0 28px;">
              <?php
              $libelles = [
                  'R' => 'Réaliste',
                  'I' => 'Investigateur',
                  'A' => 'Artistique',
                  'S' => 'Social',
                  'E' => 'Entrepreneur',
                  'C' => 'Conventionnel',
              ];
              foreach ( $scores as $lettre => $score ) :
                  $pct   = min( 100, round( ( $score / 14 ) * 100 ) );
                  $label = $libelles[ $lettre ] ?? $lettre;
              ?>
              <tr>
                <td style="padding:4px 0;">
                  <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="width:130px;font-size:13px;color:#555;padding-right:12px;">
                        <strong><?php echo esc_html( $lettre ); ?></strong>
                        — <?php echo esc_html( $label ); ?>
                      </td>
                      <td>
                        <table width="100%" cellpadding="0" cellspacing="0"
                               style="background:#e8ecf0;border-radius:4px;height:10px;">
                          <tr>
                            <td style="width:<?php echo $pct; ?>%;background:#1e3a5f;
                                        border-radius:4px;height:10px;"></td>
                            <td></td>
                          </tr>
                        </table>
                      </td>
                      <td style="width:40px;text-align:right;font-size:13px;
                                 color:#555;padding-left:10px;">
                        <?php echo esc_html( $score ); ?>/14
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <?php endforeach; ?>
            </table>

            <!-- Lien dashboard -->
            <table width="100%" cellpadding="0" cellspacing="0"
                   style="background:#e8f0fb;border-radius:8px;">
              <tr>
                <td style="padding:24px;text-align:center;">
                  <p style="margin:0 0 14px;font-size:14px;color:#1e3a5f;">
                    Retrouvez ce lead dans votre tableau de bord PraxiMet.
                  </p>
                  <a href="<?php echo esc_url( admin_url('admin.php?page=praximet-leads&id=' . $lead_id) ); ?>"
                     style="display:inline-block;background:#1e3a5f;color:#ffffff;
                            text-decoration:none;padding:12px 28px;border-radius:6px;
                            font-size:14px;font-weight:700;">
                    Voir le lead →
                  </a>
                </td>
              </tr>
            </table>

          </td>
        </tr>

        <!-- Pied de page -->
        <tr>
          <td style="background:#f4f6f9;padding:20px 40px;text-align:center;
                     border-top:1px solid #e8ecf0;">
            <p style="margin:0;font-size:12px;color:#999;">
              Notification automatique PraxiMet •
              <?php echo esc_html( get_bloginfo('name') ); ?>
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
