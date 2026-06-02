<?php if ( ! defined('ABSPATH') ) exit;
/**
 * Variables injectées depuis PP_Mailer::template_user() via ob_start/include :
 *   $prenom, $profil, $rdv_url, $site_name
 *   $arch_nom, $arch_emoji, $arch_tag, $arch_rarete
 *   $token  (string, peut être vide)
 */

// URL profil public — $token est bien dans le scope (héritage ob_start/include)
$profil_url = ! empty( $token ) ? home_url( '/profil/' . $token ) : $rdv_url;

// Map dimension → config visuelle (clés = clés du tableau $profil)
$dims_cfg = array(
    'score_O' => array( 'label' => 'Ouverture',    'color' => '#1E2A3A' ),
    'score_C' => array( 'label' => 'Conscience',   'color' => '#E8541A' ),
    'score_E' => array( 'label' => 'Extraversion', 'color' => '#C4430F' ),
    'score_A' => array( 'label' => 'Agréabilité',  'color' => '#2E4A6A' ),
    'score_N' => array( 'label' => 'Stabilité',    'color' => '#8FA8BE' ),
);

// Couleur principale (archétype ou fallback)
$color_main = '#E8541A';
$color_grad = '#1E2A3A';
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
  <tr><td style="background:linear-gradient(135deg,<?php echo $color_main;?> 0%,<?php echo $color_grad;?> 100%);border-radius:16px 16px 0 0;padding:40px 32px 32px;text-align:center;color:#fff;">
    <?php if ( ! empty($arch_emoji) ) : ?>
      <div style="font-size:54px;margin-bottom:10px;line-height:1;"><?php echo $arch_emoji; ?></div>
    <?php endif; ?>
    <p style="margin:0 0 4px;opacity:.75;font-size:12px;text-transform:uppercase;letter-spacing:.1em;">Le profil de</p>
    <h1 style="margin:0 0 8px;font-size:26px;font-weight:800;letter-spacing:-.3px;"><?php echo esc_html( $prenom ); ?></h1>
    <?php if ( ! empty($arch_nom) ) : ?>
      <p style="margin:0 0 6px;font-size:19px;font-weight:700;color:#c7d2fe;"><?php echo esc_html( $arch_nom ); ?></p>
      <p style="margin:0;font-size:14px;font-style:italic;opacity:.85;"><?php echo esc_html( $arch_tag ); ?></p>
    <?php endif; ?>
    <?php if ( ! empty($arch_rarete) ) : ?>
      <div style="display:inline-block;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);border-radius:999px;padding:5px 16px;margin-top:14px;font-size:12px;font-weight:600;">
        ✨ Profil présent chez seulement <?php echo intval($arch_rarete); ?>% des personnes
      </div>
    <?php endif; ?>
  </td></tr>

  <!-- CORPS -->
  <tr><td style="background:#ffffff;padding:32px;">

    <p style="margin:0 0 22px;font-size:15px;color:#334155;line-height:1.7;">
      Bonjour <strong><?php echo esc_html($prenom); ?></strong>,<br>
      voici le résumé de votre profil de personnalité Big Five.
    </p>

    <!-- ── Barres OCEAN ── -->
    <?php foreach ( $dims_cfg as $dim_key => $cfg ) :
        // Lecture directe dans $profil[$dim_key]['score'] — structure connue
        $pct = isset( $profil[$dim_key]['score'] ) ? intval( $profil[$dim_key]['score'] ) : 0;
    ?>
    <div style="margin-bottom:13px;">
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="font-size:13px;font-weight:600;color:#475569;width:110px;white-space:nowrap;"><?php echo esc_html($cfg['label']); ?></td>
          <td style="padding:0 10px;">
            <div style="background:#e2e8f0;border-radius:999px;height:8px;overflow:hidden;">
              <div style="background:<?php echo $cfg['color']; ?>;height:8px;width:<?php echo $pct; ?>%;border-radius:999px;"></div>
            </div>
          </td>
          <td style="font-size:13px;font-weight:700;color:<?php echo $cfg['color']; ?>;width:36px;text-align:right;"><?php echo $pct; ?>%</td>
        </tr>
      </table>
    </div>
    <?php endforeach; ?>

    <!-- Séparateur -->
    <div style="border-top:1px solid #e2e8f0;margin:26px 0;"></div>

    <p style="margin:0 0 20px;font-size:14px;color:#475569;line-height:1.7;">
      Retrouvez votre profil complet, votre carte personnalisée et la mécanique de compatibilité en cliquant sur le bouton ci-dessous.
    </p>

    <!-- CTA principal — profil public -->
    <div style="text-align:center;margin:0 0 14px;">
      <a href="<?php echo esc_url($profil_url); ?>"
         style="display:inline-block;background:linear-gradient(135deg,<?php echo $color_main;?>,<?php echo $color_grad;?>);color:#fff;text-decoration:none;padding:15px 36px;border-radius:999px;font-size:15px;font-weight:700;">
        Voir mon profil complet →
      </a>
    </div>

    <!-- CTA secondaire — RDV -->
    <div style="text-align:center;margin:0 0 28px;">
      <a href="<?php echo esc_url($rdv_url); ?>"
         style="display:inline-block;border:2px solid <?php echo $color_main;?>;color:<?php echo $color_main;?>;text-decoration:none;padding:11px 26px;border-radius:999px;font-size:14px;font-weight:600;">
        📅 Réserver un entretien de débriefing
      </a>
    </div>

    <div style="border-top:1px solid #e2e8f0;padding-top:16px;">
      <p style="margin:0;font-size:12px;color:#94a3b8;line-height:1.6;">
        Vous recevez ce message car vous avez réalisé un PraxiMum sur
        <strong><?php echo esc_html($site_name); ?></strong>.<br>
        Vos données sont traitées conformément à notre politique de confidentialité.
      </p>
    </div>
  </td></tr>

  <!-- FOOTER -->
  <tr><td style="background:#f8fafc;border-radius:0 0 16px 16px;padding:16px 32px;text-align:center;">
    <p style="margin:0;font-size:12px;color:#94a3b8;"><?php echo esc_html($site_name); ?></p>
    <?php if ( $token ) : ?>
    <p style="margin:8px 0 0;font-size:11px;color:#cbd5e1;">
      <a href="<?php echo esc_url(home_url('/supprimer-mes-donnees/'.$token)); ?>" style="color:#cbd5e1;">Supprimer mes données</a>
      &nbsp;·&nbsp;
      <a href="<?php echo esc_url(home_url('/mes-donnees/'.$token)); ?>" style="color:#cbd5e1;">Télécharger mon dossier</a>
    </p>
    <?php endif; ?>
  </td></tr>

</table>
</td></tr>
</table>
</body>
</html>
