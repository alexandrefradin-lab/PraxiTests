<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function praxivaleurs_send_user_email( $prenom, $email, $top5, $scores ) {
    $dimensions = PraxiValeurs::get_dimensions();
    $mapping    = PraxiValeurs::get_mapping();
    $consultant_email = get_option('praxivaleurs_consultant_email', get_option('admin_email'));

    // Construction des cartes valeurs
    $cartes_html = '';
    $rang = 1;
    foreach ( $top5 as $dim_key => $score ) {
        $dim     = $dimensions[$dim_key];
        $map     = $mapping[$dim_key];
        $pct     = min(100, round($score));
        $couleur = esc_attr($dim['couleur']);
        $label   = esc_html($dim['label']);
        $icon    = esc_html($dim['icon']);
        $desc    = esc_html($map['description']);
        $impl    = esc_html($map['implication']);
        $cartes_html .= "
        <div style='background:#fff;border-left:5px solid {$couleur};margin:0 0 18px 0;padding:20px 24px;border-radius:0 8px 8px 0;box-shadow:0 2px 8px rgba(0,0,0,0.06);'>
            <div style='display:flex;align-items:center;margin-bottom:10px;'>
                <span style='font-size:26px;margin-right:12px;'>{$icon}</span>
                <div>
                    <span style='color:#999;font-size:12px;text-transform:uppercase;letter-spacing:1px;'>Valeur #{$rang}</span><br>
                    <strong style='color:{$couleur};font-size:18px;'>{$label}</strong>
                    <span style='color:#ccc;font-size:13px;margin-left:8px;'>{$pct}%</span>
                </div>
            </div>
            <p style='color:#444;font-size:14px;line-height:1.6;margin:0 0 8px 0;'>{$desc}</p>
            <p style='color:#666;font-size:13px;background:#f8f6f1;padding:10px 14px;border-radius:6px;margin:0;'><strong>💼 Pour votre projet professionnel :</strong> {$impl}</p>
        </div>";
        $rang++;
    }

    $subject = sprintf('[Prénom], voici vos 5 valeurs fondamentales — à garder précieusement 🌟');
    $subject = str_replace('[Prénom]', $prenom, $subject);

    $body = "
<!DOCTYPE html>
<html lang='fr'>
<head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1.0'></head>
<body style='margin:0;padding:0;background:#f0ede8;font-family:Inter,Arial,sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0'>
<tr><td align='center' style='padding:30px 16px;'>
<table width='600' cellpadding='0' cellspacing='0' style='max-width:600px;width:100%;'>

  <!-- Header -->
  <tr><td style='background:#1B2A4A;border-radius:12px 12px 0 0;padding:36px 40px;text-align:center;'>
    <div style='color:#E8491D;font-size:11px;text-transform:uppercase;letter-spacing:3px;margin-bottom:8px;'>Praxis Accompagnement</div>
    <h1 style='color:#fff;margin:0;font-size:26px;font-weight:700;'>Votre Profil PraxiValeurs</h1>
    <p style='color:rgba(255,255,255,0.75);margin:8px 0 0 0;font-size:14px;'>Bilan de compétences · Évaluation des valeurs</p>
  </td></tr>

  <!-- Intro -->
  <tr><td style='background:#fff;padding:32px 40px;'>
    <p style='color:#2C2C2C;font-size:15px;line-height:1.7;margin:0 0 20px 0;'>Bonjour <strong>" . esc_html($prenom) . "</strong>,</p>
    <p style='color:#555;font-size:14px;line-height:1.7;margin:0 0 8px 0;'>Voici le résultat de votre évaluation des valeurs, réalisée dans le cadre de votre bilan de compétences avec <strong>Praxis Accompagnement</strong>.</p>
    <p style='color:#555;font-size:14px;line-height:1.7;margin:0;'>Ces <strong>5 valeurs dominantes</strong> sont le socle invisible de vos décisions de carrière. Elles vous appartiennent — gardez ce profil précieusement.</p>
  </td></tr>

  <!-- Top 5 -->
  <tr><td style='background:#f8f6f1;padding:30px 40px;'>
    <h2 style='color:#1B2A4A;font-size:18px;margin:0 0 20px 0;text-align:center;'>⭐ Vos 5 Valeurs Dominantes</h2>
    {$cartes_html}
  </td></tr>

  <!-- Footer -->
  <tr><td style='background:#f0ede8;border-radius:0 0 12px 12px;padding:20px 40px;text-align:center;'>
    <p style='color:#777;font-size:13px;margin:0 0 8px 0;'>Partagez ce profil avec votre consultant lors de votre prochain rendez-vous.</p>
    <p style='color:#999;font-size:12px;margin:0;'>🔒 Vos données sont confidentielles et utilisées uniquement dans le cadre de votre bilan de compétences.<br>
    <strong>Praxis Accompagnement</strong> — <a href='https://praxis-accompagnement.fr' style='color:#E8491D;'>praxis-accompagnement.fr</a></p>
  </td></tr>

</table>
</td></tr>
</table>
</body>
</html>";

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: Praxis Accompagnement <' . esc_html($consultant_email) . '>',
    );
    wp_mail( $email, $subject, $body, $headers );
}

function praxivaleurs_send_consultant_email( $prenom, $email, $top5 ) {
    $dimensions = PraxiValeurs::get_dimensions();
    $consultant_email = get_option('praxivaleurs_consultant_email', get_option('admin_email'));

    $top5_txt = '';
    $rang = 1;
    foreach ($top5 as $dim_key => $score) {
        $dim    = $dimensions[$dim_key];
        $pct    = min(100, round($score));
        $top5_txt .= "#{$rang} " . esc_html($dim['label']) . " ({$pct}%)\n";
        $rang++;
    }

    $subject = "[PraxiValeurs] Nouveau profil : " . esc_html($prenom);
    $body = "Bonjour,\n\nUn nouveau profil PraxiValeurs vient d'être complété.\n\n";
    $body .= "Prénom : " . esc_html($prenom) . "\n";
    $body .= "Email   : " . esc_html($email) . "\n\n";
    $body .= "Top 5 des valeurs :\n" . $top5_txt;
    $body .= "\nConnectez-vous à l'administration WordPress pour consulter l'historique complet.";

    wp_mail( $consultant_email, $subject, $body );
}
