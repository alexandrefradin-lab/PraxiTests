<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Variables injectées depuis PE_Mailer::template_user() via ob_start/include :
 *   $prenom, $results, $rdv_url, $site_name
 *
 * $results contient :
 *   - dim_scores[1..16]
 *   - score_global (int)
 *   - niveau_qe (string)
 *   - phrase_qe (string)
 *   - top_forces (array de dim_id)
 *   - top_dev (array de dim_id)
 */

$dims    = PE_Calculator::get_dimensions();
$fams    = PE_Calculator::get_familles();
$c1      = get_option( 'pemo_color_primary', '#E8541A' );
if ( ! preg_match( '/^#[0-9A-Fa-f]{3,6}$/', $c1 ) ) $c1 = '#E8541A';
$c2      = '#1E2A3A';

// Couleurs familles pour barres
$fam_colors = array(
    1 => $c1,
    2 => '#F59E0B',
    3 => '#3B82F6',
    4 => '#16A34A',
);

$score_global = intval( $results['score_global'] ?? 0 );
$niveau_qe    = esc_html( $results['niveau_qe']   ?? '' );
$phrase_qe    = esc_html( $results['phrase_qe']   ?? '' );

// Top forces & développement labels
$forces_labels = array();
$dev_labels    = array();
foreach ( ( $results['top_forces'] ?? array() ) as $dim_id ) {
    $forces_labels[] = esc_html( $dims[ $dim_id ]['label'] ?? '' );
}
foreach ( ( $results['top_dev'] ?? array() ) as $dim_id ) {
    $dev_labels[] = esc_html( $dims[ $dim_id ]['label'] ?? '' );
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:32px 16px;">
<tr><td align="center">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;">

  <!-- HEADER -->
  <tr><td style="background:linear-gradient(135deg,<?php echo $c1; ?> 0%,<?php echo $c2; ?> 100%);border-radius:16px 16px 0 0;padding:40px 32px 32px;text-align:center;color:#fff;">
    <p style="margin:0 0 4px;opacity:.75;font-size:12px;text-transform:uppercase;letter-spacing:.1em;">Le profil de</p>
    <h1 style="margin:0 0 10px;font-size:26px;font-weight:800;letter-spacing:-.3px;"><?php echo esc_html( $prenom ); ?></h1>
    <div style="font-size:44px;font-weight:800;line-height:1;margin-bottom:8px;"><?php echo $score_global; ?><span style="font-size:20px;opacity:.65;"> / 320</span></div>
    <div style="display:inline-block;background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.3);border-radius:999px;padding:6px 18px;font-size:13px;font-weight:700;margin-bottom:14px;"><?php echo $niveau_qe; ?></div>
    <p style="margin:0;font-size:14px;opacity:.85;line-height:1.6;max-width:420px;margin:0 auto;"><?php echo $phrase_qe; ?></p>
  </td></tr>

  <!-- CORPS -->
  <tr><td style="background:#ffffff;padding:32px;">

    <p style="margin:0 0 24px;font-size:15px;color:#334155;line-height:1.7;">
      Bonjour <strong><?php echo esc_html( $prenom ); ?></strong>,<br>
      voici le détail de vos 16 dimensions d'Intelligence Émotionnelle.
    </p>

    <?php foreach ( $fams as $fam_id => $fam ) :
        $bar_color = $fam_colors[ $fam_id ] ?? $c1;
    ?>
    <!-- Famille -->
    <div style="margin-bottom:22px;">
      <p style="margin:0 0 10px;font-size:13px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.05em;"><?php echo esc_html( $fam['emoji'] . ' ' . $fam['label'] ); ?></p>

      <?php foreach ( $dims as $dim_id => $dim ) :
          if ( $dim['famille'] !== $fam_id ) continue;
          $score = intval( $results['dim_scores'][ $dim_id ] ?? 0 );
          $pct   = round( ( $score / 20 ) * 100 );
      ?>
      <div style="margin-bottom:11px;">
        <table width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <td style="font-size:13px;font-weight:600;color:#475569;width:160px;white-space:nowrap;"><?php echo esc_html( $dim['label'] ); ?></td>
            <td style="padding:0 10px;">
              <div style="background:#e2e8f0;border-radius:999px;height:7px;overflow:hidden;">
                <div style="background:<?php echo $bar_color; ?>;height:7px;width:<?php echo $pct; ?>%;border-radius:999px;"></div>
              </div>
            </td>
            <td style="font-size:13px;font-weight:700;color:<?php echo $bar_color; ?>;width:40px;text-align:right;white-space:nowrap;"><?php echo $score; ?>/20</td>
          </tr>
        </table>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <!-- Séparateur -->
    <div style="border-top:1px solid #e2e8f0;margin:26px 0;"></div>

    <!-- Points forts & Développement -->
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:26px;">
      <tr>
        <td style="width:50%;padding-right:8px;vertical-align:top;">
          <div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:10px;padding:16px;">
            <p style="margin:0 0 10px;font-size:13px;font-weight:700;color:#15803D;">🏆 Points forts</p>
            <?php foreach ( $forces_labels as $l ) : ?>
            <p style="margin:0 0 4px;font-size:13px;color:#1E293B;">• <?php echo $l; ?></p>
            <?php endforeach; ?>
          </div>
        </td>
        <td style="width:50%;padding-left:8px;vertical-align:top;">
          <div style="background:#EFF6FF;border:1px solid #BFDBFE;border-radius:10px;padding:16px;">
            <p style="margin:0 0 10px;font-size:13px;font-weight:700;color:#1D4ED8;">🎯 Axes de progression</p>
            <?php foreach ( $dev_labels as $l ) : ?>
            <p style="margin:0 0 4px;font-size:13px;color:#1E293B;">• <?php echo $l; ?></p>
            <?php endforeach; ?>
          </div>
        </td>
      </tr>
    </table>

    <!-- Bloc CTA -->
    <div style="background:#FFF7F4;border:1px solid #FED7C3;border-radius:12px;padding:24px;text-align:center;margin-bottom:26px;">
      <p style="margin:0 0 6px;font-size:15px;font-weight:700;color:<?php echo $c1; ?>;">Votre profil mérite un regard expert.</p>
      <p style="margin:0 0 18px;font-size:14px;color:#475569;line-height:1.6;">Un entretien de débriefing avec Alexandre vous permettra de comprendre ce que révèlent réellement vos scores — et comment les transformer en leviers concrets.</p>
      <a href="<?php echo esc_url( $rdv_url ); ?>"
         style="display:inline-block;background:linear-gradient(135deg,<?php echo $c1; ?>,<?php echo $c2; ?>);color:#fff;text-decoration:none;padding:14px 32px;border-radius:999px;font-size:15px;font-weight:700;">
        📅 Réserver mon entretien gratuit →
      </a>
    </div>

    <!-- Footer légal -->
    <div style="border-top:1px solid #e2e8f0;padding-top:16px;">
      <?php $privacy_url = PE_Shortcode::get_privacy_url(); ?>
      <p style="margin:0;font-size:12px;color:#94a3b8;line-height:1.6;">
        Vous recevez ce message car vous avez réalisé un test IE sur
        <strong><?php echo esc_html( $site_name ); ?></strong>.<br>
        Vos données sont traitées conformément à notre
        <a href="<?php echo $privacy_url; ?>" style="color:#94a3b8;" target="_blank" rel="noopener noreferrer">politique de confidentialité</a>.
      </p>
    </div>

  </td></tr>

  <!-- FOOTER EMAIL -->
  <tr><td style="background:#f8fafc;border-radius:0 0 16px 16px;padding:16px 32px;text-align:center;">
    <p style="margin:0;font-size:12px;color:#94a3b8;"><?php echo esc_html( $site_name ); ?> &mdash; Praxis Accompagnement</p>
  </td></tr>

</table>
</td></tr>
</table>
</body>
</html>
