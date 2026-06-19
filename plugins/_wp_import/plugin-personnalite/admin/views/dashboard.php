<?php if ( ! defined('ABSPATH') ) exit;
global $wpdb;
$t = $wpdb->prefix . 'pp_resultats';

// Données générales
$total      = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$t}");
$ce_mois    = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$t} WHERE date_soumis >= DATE_FORMAT(NOW(),'%Y-%m-01')");
$rdv_clique = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$t} WHERE rdv_clique = 1");
$taux_rdv   = $total > 0 ? round($rdv_clique / $total * 100) : 0;
$ds_eleves  = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$t} WHERE score_DS >= 70");
$taux_ds    = $total > 0 ? round($ds_eleves / $total * 100) : 0;

// Moyennes OCEAN
$moyennes = $wpdb->get_row("SELECT
    ROUND(AVG(score_O),1) AS o,
    ROUND(AVG(score_C),1) AS c,
    ROUND(AVG(score_E),1) AS e,
    ROUND(AVG(score_A),1) AS a,
    ROUND(AVG(score_N),1) AS n
    FROM {$t}");

// Top 5 archétypes
$archetypes = $wpdb->get_results("SELECT archetype_nom, COUNT(*) AS n
    FROM {$t} WHERE archetype_nom != ''
    GROUP BY archetype_nom ORDER BY n DESC LIMIT 8");

// Inscriptions 12 dernières semaines
$weekly = $wpdb->get_results("SELECT
    DATE_FORMAT(date_soumis,'%Y-%u') AS semaine,
    YEARWEEK(date_soumis,1) AS yw,
    COUNT(*) AS n
    FROM {$t}
    WHERE date_soumis >= DATE_SUB(NOW(), INTERVAL 12 WEEK)
    GROUP BY yw ORDER BY yw ASC");

$dim_colors = array('O'=>'#E8541A','C'=>'#1E2A3A','E'=>'#C4430F','A'=>'#2E4A6A','N'=>'#8FA8BE');
$dim_labels = array('O'=>'Ouverture','C'=>'Conscience','E'=>'Extraversion','A'=>'Agréabilité','N'=>'Stabilité');
?>
<div class="wrap" style="max-width:1100px;">
  <h1 style="margin-bottom:24px;">📊 Tableau de bord</h1>

  <!-- KPI cards -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:32px;">
    <?php $cards = array(
      array('label'=>'Tests complétés','value'=>$total,          'color'=>'#E8541A','icon'=>'📝'),
      array('label'=>'Ce mois',         'value'=>$ce_mois,        'color'=>'#2E4A6A','icon'=>'📅'),
      array('label'=>'Clics RDV',       'value'=>$rdv_clique . ' (' . $taux_rdv . '%)', 'color'=>'#E8541A','icon'=>'📞'),
      array('label'=>'DS élevée ≥70%',  'value'=>$ds_eleves . ' (' . $taux_ds . '%)',   'color'=>'#C4430F','icon'=>'⚠️'),
    );
    foreach ($cards as $c) : ?>
    <div style="background:#fff;border:1.5px solid #e2e8f0;border-radius:14px;padding:20px;border-top:4px solid <?php echo $c['color']; ?>;">
      <div style="font-size:24px;margin-bottom:8px;"><?php echo $c['icon']; ?>
  <!-- Copyright -->
  <div style="margin-top:32px;padding-top:16px;border-top:1px solid #e2e8f0;text-align:center;font-size:12px;color:#94a3b8;">
    PraxiMum — Créé par <strong style="color:#1E2A3A;">Alexandre Fradin</strong> &nbsp;·&nbsp; <?php echo date('Y'); ?>
  </div>
</div>      <div style="font-size:26px;font-weight:900;color:<?php echo $c['color']; ?>;line-height:1.1;"><?php echo $c['value']; ?></div>
      <div style="font-size:12px;color:#64748b;margin-top:4px;"><?php echo $c['label']; ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:32px;">

    <!-- Profil moyen OCEAN -->
    <div style="background:#fff;border:1.5px solid #e2e8f0;border-radius:14px;padding:24px;">
      <h3 style="margin:0 0 16px;font-size:15px;color:#1e293b;">Profil moyen — Population testée</h3>
      <?php if ($total > 0) :
        foreach (array('O','C','E','A','N') as $d) :
          $pct = $moyennes->$d ?? 50; $col = $dim_colors[$d];
      ?>
      <div style="margin-bottom:10px;">
        <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:3px;">
          <span style="font-weight:600;color:#475569;"><?php echo $dim_labels[$d]; ?></span>
          <span style="font-weight:700;color:<?php echo $col; ?>;"><?php echo $pct; ?>%</span>
        </div>
        <div style="background:#e2e8f0;border-radius:999px;height:8px;overflow:hidden;">
          <div style="background:<?php echo $col; ?>;height:8px;width:<?php echo min(100,$pct); ?>%;border-radius:999px;"></div>
        </div>
      </div>
      <?php endforeach;
      else: ?>
      <p style="color:#94a3b8;">Aucune donnée disponible.</p>
      <?php endif; ?>
    </div>

    <!-- Top archétypes -->
    <div style="background:#fff;border:1.5px solid #e2e8f0;border-radius:14px;padding:24px;">
      <h3 style="margin:0 0 16px;font-size:15px;color:#1e293b;">Répartition des archétypes</h3>
      <?php if ($archetypes) :
        $max_n = max(array_column((array)$archetypes, 'n'));
        foreach ($archetypes as $a) :
          $pct = $max_n > 0 ? round($a->n / $max_n * 100) : 0;
      ?>
      <div style="margin-bottom:8px;">
        <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:2px;">
          <span style="font-weight:600;color:#475569;"><?php echo esc_html($a->archetype_nom); ?></span>
          <span style="color:#64748b;"><?php echo $a->n; ?></span>
        </div>
        <div style="background:#e2e8f0;border-radius:999px;height:6px;overflow:hidden;">
          <div style="background:#E8541A;height:6px;width:<?php echo $pct; ?>%;border-radius:999px;opacity:.75;"></div>
        </div>
      </div>
      <?php endforeach;
      else: ?>
      <p style="color:#94a3b8;">Aucun archétype enregistré.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Graphique inscription hebdomadaires -->
  <div style="background:#fff;border:1.5px solid #e2e8f0;border-radius:14px;padding:24px;margin-bottom:32px;">
    <h3 style="margin:0 0 16px;font-size:15px;color:#1e293b;">Inscriptions — 12 dernières semaines</h3>
    <?php if ($weekly) :
      $max_w = max(array_column((array)$weekly, 'n'));
      $chart_h = 80;
    ?>
    <div style="display:flex;gap:4px;align-items:flex-end;height:<?php echo $chart_h+24; ?>px;">
      <?php foreach ($weekly as $w) :
        $bar_h = $max_w > 0 ? round($w->n / $max_w * $chart_h) : 4;
        $bar_h = max(4, $bar_h);
      ?>
      <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;">
        <div style="font-size:10px;color:#64748b;font-weight:700;"><?php echo $w->n; ?></div>
        <div style="width:100%;background:linear-gradient(180deg,#E8541A,#1E2A3A);border-radius:4px 4px 0 0;height:<?php echo $bar_h; ?>px;min-height:4px;" title="S<?php echo substr($w->semaine,-2); ?> : <?php echo $w->n; ?> test(s)"></div>
        <div style="font-size:9px;color:#94a3b8;">S<?php echo substr($w->semaine,-2); ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p style="color:#94a3b8;">Pas encore de données sur 12 semaines.</p>
    <?php endif; ?>
  </div>

  <!-- Actions rapides -->
  <div style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:14px;padding:20px;">
    <h3 style="margin:0 0 14px;font-size:15px;color:#1e293b;">Actions rapides</h3>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <?php
      $nonce = wp_create_nonce('pp_export_csv');
      $export_url = add_query_arg(array('page'=>'pp-resultats','pp_export_csv'=>1,'_wpnonce'=>$nonce), admin_url('admin.php'));
      ?>
      <a href="<?php echo esc_url(admin_url('admin.php?page=pp-resultats')); ?>" class="button">📋 Voir tous les résultats</a>
      <a href="<?php echo esc_url($export_url); ?>" class="button button-primary">⬇ Exporter CSV complet</a>
      <a href="<?php echo esc_url(admin_url('admin.php?page=pp-reglages')); ?>" class="button">⚙️ Réglages</a>
      <?php if (class_exists('PP_Batch')) : ?>
      <a href="<?php echo esc_url(admin_url('admin.php?page=pp-batch')); ?>" class="button">📧 Mode batch</a>
      <?php endif; ?>
    </div>
  </div>
</div>