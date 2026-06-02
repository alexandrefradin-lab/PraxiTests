<?php
/**
 * Template page publique — Vue équipe d'une campagne batch
 * Variables : $campagne_token, $profils (array of rows), $campagne_nom
 */
if ( ! defined('ABSPATH') ) exit;

$dim_cfg = array(
    'O'=>array('label'=>'Ouverture',    'color'=>'#1E2A3A','short'=>'O'),
    'C'=>array('label'=>'Conscience',   'color'=>'#E8541A','short'=>'C'),
    'E'=>array('label'=>'Extraversion', 'color'=>'#C4430F','short'=>'E'),
    'A'=>array('label'=>'Agréabilité',  'color'=>'#2E4A6A','short'=>'A'),
    'N'=>array('label'=>'Stabilité',    'color'=>'#8FA8BE','short'=>'N'),
);

// Calcul des moyennes équipe
$moyennes = array();
$n = count($profils);
if ($n > 0) {
    foreach ( array('O','C','E','A','N') as $d ) {
        $sum = 0;
        foreach ($profils as $p) { $sum += intval($p->{'score_'.$d}); }
        $moyennes[$d] = round($sum / $n);
    }
}

// Distribution des archétypes
$arch_dist = array();
foreach ($profils as $p) {
    $nm = $p->archetype_nom ?: 'Non défini';
    $arch_dist[$nm] = ($arch_dist[$nm] ?? 0) + 1;
}
arsort($arch_dist);

// Matrice de compatibilité (premier vs tous)
// Calculée via PP_Public_Profil::calculer_compatibilite
$compat_matrix = array();
if ($n >= 2) {
    for ($i=0; $i<$n; $i++) {
        for ($j=$i+1; $j<$n; $j++) {
            $pi = $profils[$i]; $pj = $profils[$j];
            $si = array('O'=>$pi->score_O,'C'=>$pi->score_C,'E'=>$pi->score_E,'A'=>$pi->score_A,'N'=>$pi->score_N);
            $sj = array('O'=>$pj->score_O,'C'=>$pj->score_C,'E'=>$pj->score_E,'A'=>$pj->score_A,'N'=>$pj->score_N);
            // Adapter au format attendu par calculer_compatibilite
            $siFull = array_map(function($v){ return array('pct'=>$v); }, $si);
            $sjFull = array_map(function($v){ return array('pct'=>$v); }, $sj);
            $res = PP_Public_Profil::calculer_compatibilite($pi, $pj, $siFull, $sjFull);
            $compat_matrix[$i][$j] = $res['score'];
        }
    }
}
$rdv_url = get_option('pp_rdv_url', home_url('/contact'));
?>
<div class="pp-equipe-page" style="max-width:900px;margin:40px auto;padding:0 16px 80px;font-family:'Segoe UI',system-ui,sans-serif;">

  <!-- Header -->
  <div style="background:linear-gradient(135deg,var(--pp-c1,#E8541A),var(--pp-c2,#1E2A3A));border-radius:20px;padding:36px 32px;text-align:center;color:#fff;margin-bottom:32px;">
    <div style="font-size:48px;margin-bottom:10px;">👥</div>
    <h1 style="margin:0 0 6px;font-size:26px;font-weight:800;">Vue équipe</h1>
    <p style="margin:0;font-size:15px;opacity:.85;"><?php echo esc_html($campagne_nom); ?></p>
    <div style="display:inline-block;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);border-radius:999px;padding:5px 16px;margin-top:12px;font-size:13px;font-weight:600;">
      <?php echo $n; ?> profil<?php echo $n>1?'s':''; ?> complété<?php echo $n>1?'s':''; ?>
    </div>
  </div>

  <?php if ($n === 0) : ?>
  <div style="text-align:center;padding:40px;color:#94a3b8;">
    <p>Aucun membre n'a encore complété le test dans cette campagne.</p>
  </div>
  <?php else : ?>

  <!-- Profil moyen équipe -->
  <div style="background:#fff;border:1.5px solid #e2e8f0;border-radius:16px;padding:24px;margin-bottom:24px;">
    <h2 style="margin:0 0 16px;font-size:16px;font-weight:800;color:#1e293b;">📊 Profil moyen de l'équipe</h2>
    <?php foreach ($dim_cfg as $key => $cfg) :
      $pct = $moyennes[$key] ?? 50;
    ?>
    <div style="margin-bottom:10px;">
      <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:3px;">
        <span style="font-weight:600;color:#475569;"><?php echo $cfg['label']; ?></span>
        <span style="font-weight:700;color:<?php echo $cfg['color']; ?>;"><?php echo $pct; ?>%</span>
      </div>
      <div style="background:#e2e8f0;border-radius:999px;height:8px;overflow:hidden;">
        <div style="background:<?php echo $cfg['color']; ?>;height:8px;width:<?php echo $pct; ?>%;border-radius:999px;"></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">

    <!-- Distribution archétypes -->
    <div style="background:#fff;border:1.5px solid #e2e8f0;border-radius:16px;padding:22px;">
      <h2 style="margin:0 0 14px;font-size:15px;font-weight:800;color:#1e293b;">🎭 Archétypes présents</h2>
      <?php $max_a = max(array_values($arch_dist)) ?: 1;
      foreach ($arch_dist as $anom => $acnt) :
        $pct_a = round($acnt/$n*100);
      ?>
      <div style="margin-bottom:9px;">
        <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:2px;">
          <span style="font-weight:600;color:#475569;"><?php echo esc_html($anom); ?></span>
          <span style="color:#64748b;"><?php echo $acnt; ?> (<?php echo $pct_a; ?>%)</span>
        </div>
        <div style="background:#e2e8f0;border-radius:999px;height:6px;overflow:hidden;">
          <div style="background:#E8541A;height:6px;width:<?php echo round($acnt/$max_a*100); ?>%;border-radius:999px;opacity:.7;"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Points forts / zones de vigilance -->
    <div style="background:#fff;border:1.5px solid #e2e8f0;border-radius:16px;padding:22px;">
      <h2 style="margin:0 0 14px;font-size:15px;font-weight:800;color:#1e293b;">💡 Lecture collective</h2>
      <?php
      $forces = array(); $vigilances = array();
      foreach ($dim_cfg as $k => $cfg) {
          $pct = $moyennes[$k] ?? 50;
          if ($pct >= 65) $forces[]    = array($cfg['label'], $pct, $cfg['color']);
          if ($pct <= 35) $vigilances[]= array($cfg['label'], $pct, $cfg['color']);
      }
      ?>
      <?php if ($forces) : ?>
      <div style="margin-bottom:12px;">
        <div style="font-size:12px;font-weight:700;color:#2E4A6A;margin-bottom:6px;">✅ Points forts collectifs</div>
        <?php foreach ($forces as $f) : ?>
        <div style="font-size:12px;color:#475569;padding:3px 0;">
          <span style="font-weight:600;color:<?php echo $f[2]; ?>;"><?php echo esc_html($f[0]); ?></span>
          <?php echo $f[1]; ?>% — dimension dominante de l'équipe
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <?php if ($vigilances) : ?>
      <div>
        <div style="font-size:12px;font-weight:700;color:#C4430F;margin-bottom:6px;">⚠️ Zones de vigilance</div>
        <?php foreach ($vigilances as $v) : ?>
        <div style="font-size:12px;color:#475569;padding:3px 0;">
          <span style="font-weight:600;color:<?php echo $v[2]; ?>;"><?php echo esc_html($v[0]); ?></span>
          <?php echo $v[1]; ?>% — à renforcer collectivement
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <?php if (!$forces && !$vigilances) : ?>
      <p style="font-size:13px;color:#64748b;">Profil équipe équilibré — aucune dimension extrême.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Cartes individuelles -->
  <div style="margin-bottom:24px;">
    <h2 style="font-size:16px;font-weight:800;color:#1e293b;margin:0 0 16px;padding-bottom:8px;border-bottom:2px solid #e2e8f0;">
      👤 Profils individuels
    </h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;">
      <?php foreach ($profils as $p) :
        $arch  = json_decode($p->archetype_data, true) ?: array();
        $c1    = PP_Archetypes::sanitize_color($arch['couleur1']??'#E8541A');
        $c2    = PP_Archetypes::sanitize_color($arch['couleur2']??'#1E2A3A');
        $purl  = $p->result_token ? home_url('/profil/'.$p->result_token) : '#';
      ?>
      <div style="background:#fff;border:1.5px solid #e2e8f0;border-radius:14px;overflow:hidden;">
        <div style="background:linear-gradient(135deg,<?php echo $c1; ?>,<?php echo $c2; ?>);padding:16px 20px;display:flex;align-items:center;gap:12px;">
          <div style="font-size:32px;line-height:1;"><?php echo $arch['emoji'] ?? '👤'; ?></div>
          <div>
            <div style="font-weight:800;font-size:15px;color:#fff;"><?php echo esc_html($p->prenom); ?></div>
            <div style="font-size:12px;color:rgba(255,255,255,.8);"><?php echo esc_html($p->archetype_nom ?: '—'); ?></div>
          </div>
        </div>
        <div style="padding:14px 16px;">
          <?php foreach ($dim_cfg as $dk => $dcfg) :
            $dpct = intval($p->{'score_'.$dk});
          ?>
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;">
            <span style="font-size:11px;font-weight:600;color:#475569;width:70px;"><?php echo $dcfg['short']; ?></span>
            <div style="flex:1;background:#e2e8f0;border-radius:999px;height:5px;overflow:hidden;">
              <div style="background:<?php echo $dcfg['color']; ?>;height:5px;width:<?php echo $dpct; ?>%;border-radius:999px;"></div>
            </div>
            <span style="font-size:11px;font-weight:700;color:<?php echo $dcfg['color']; ?>;width:28px;text-align:right;"><?php echo $dpct; ?>%</span>
          </div>
          <?php endforeach; ?>
          <a href="<?php echo esc_url($purl); ?>" target="_blank"
             style="display:block;text-align:center;margin-top:10px;font-size:12px;color:#E8541A;font-weight:600;text-decoration:none;">
            Voir le profil complet →
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Matrice de compatibilité -->
  <?php if ($n >= 2 && $n <= 10) : ?>
  <div style="background:#fff;border:1.5px solid #e2e8f0;border-radius:16px;padding:24px;margin-bottom:24px;overflow-x:auto;">
    <h2 style="margin:0 0 4px;font-size:16px;font-weight:800;color:#1e293b;">🤝 Matrice de compatibilité</h2>
    <p style="margin:0 0 16px;font-size:13px;color:#64748b;">Score de compatibilité OCEAN entre chaque paire.</p>
    <table style="border-collapse:collapse;min-width:100%;">
      <thead>
        <tr>
          <th style="padding:8px 10px;font-size:12px;color:#94a3b8;text-align:left;border-bottom:1px solid #e2e8f0;">
          </th>
          <?php foreach ($profils as $p) : ?>
          <th style="padding:8px 10px;font-size:12px;font-weight:700;color:#475569;text-align:center;border-bottom:1px solid #e2e8f0;white-space:nowrap;">
            <?php echo esc_html($p->prenom); ?>
          </th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($profils as $i => $pi) : ?>
        <tr>
          <td style="padding:8px 10px;font-size:12px;font-weight:700;color:#475569;white-space:nowrap;border-right:1px solid #e2e8f0;">
            <?php echo esc_html($pi->prenom); ?>
          </td>
          <?php foreach ($profils as $j => $pj) : ?>
          <?php if ($i === $j) : ?>
            <td style="padding:8px 10px;text-align:center;background:#f8fafc;font-size:12px;color:#94a3b8;">—</td>
          <?php else :
            $score = isset($compat_matrix[min($i,$j)][max($i,$j)]) ? $compat_matrix[min($i,$j)][max($i,$j)] : '?';
            $bg    = $score >= 80 ? '#EEF3F8' : ($score >= 65 ? '#FFF0EB' : ($score >= 50 ? '#F5F7FA' : '#FFF0EB'));
            $fc    = $score >= 80 ? '#2E4A6A' : ($score >= 65 ? '#E8541A' : ($score >= 50 ? '#C4430F' : '#8FA8BE'));
          ?>
            <td style="padding:8px 10px;text-align:center;background:<?php echo $bg; ?>;">
              <span style="font-size:12px;font-weight:700;color:<?php echo $fc; ?>;"><?php echo $score; ?>%</span>
            </td>
          <?php endif; ?>
          <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <!-- CTA admin -->
  <div style="text-align:center;margin:32px 0 0;">
    <a href="<?php echo esc_url($rdv_url); ?>"
       style="display:inline-block;background:linear-gradient(135deg,#E8541A,#1E2A3A);color:#fff;text-decoration:none;padding:14px 32px;border-radius:999px;font-size:14px;font-weight:700;">
      📅 Organiser un débriefing d'équipe
    </a>
  </div>

  <?php endif; // $n > 0 ?>
</div>
