<?php if ( ! defined('ABSPATH') ) exit; ?>
<?php
// Déclencher l'export CSV avant tout output
if ( isset($_GET['pp_export_csv']) && wp_verify_nonce($_GET['_wpnonce'] ?? '', 'pp_export_csv') ) {
    include __DIR__ . '/export-csv.php';
    exit;
}

$page      = max(1, intval($_GET['paged'] ?? 1));
$per_page  = 20;
$search    = sanitize_text_field($_GET['search'] ?? '');
$archetype = sanitize_text_field($_GET['archetype'] ?? '');
$ds_eleve  = ! empty($_GET['ds_eleve']);

$filters = array('search'=>$search, 'archetype'=>$archetype, 'ds_eleve'=>$ds_eleve);
$rows    = PP_DB::get_all($page, $per_page, $filters);
$total   = PP_DB::count($filters);
$pages   = max(1, ceil($total / $per_page));
$archs   = PP_DB::get_archetypes_list();

$base_url = admin_url('admin.php?page=pp-resultats');
$export_nonce = wp_create_nonce('pp_export_csv');
$export_url   = add_query_arg(array_merge($filters, array(
    'page'=>'pp-resultats','pp_export_csv'=>1,'_wpnonce'=>$export_nonce
)), admin_url('admin.php'));
?>
<div class="wrap">
  <h1 class="wp-heading-inline">Résultats — PraxiMum</h1>
  <span style="margin-left:12px;color:#64748b;"><?php echo $total; ?> profil(s)</span>
  <script>var PP_RELANCE_NONCE='<?php echo wp_create_nonce('pp_toggle_relance'); ?>',PP_AJAX_URL='<?php echo esc_js(admin_url('admin-ajax.php')); ?>';</script>

  <!-- Filtres -->
  <form method="get" style="margin:20px 0;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
    <input type="hidden" name="page" value="pp-resultats">
    <input type="text" name="search" value="<?php echo esc_attr($search); ?>"
           placeholder="Rechercher prénom / email…" class="regular-text" style="width:220px;">
    <select name="archetype">
      <option value="">Tous les archétypes</option>
      <?php foreach ( $archs as $a ) : ?>
      <option value="<?php echo esc_attr($a); ?>" <?php selected($archetype,$a); ?>><?php echo esc_html($a); ?></option>
      <?php endforeach; ?>
    </select>
    <label style="display:flex;align-items:center;gap:6px;font-size:13px;">
      <input type="checkbox" name="ds_eleve" value="1" <?php checked($ds_eleve); ?>>
      DS élevée (≥70%)
    </label>
    <?php submit_button('Filtrer', 'secondary', '', false); ?>
    <?php if ($search || $archetype || $ds_eleve) : ?>
    <a href="<?php echo esc_url($base_url); ?>" class="button">Réinitialiser</a>
    <?php endif; ?>
    <a href="<?php echo esc_url($export_url); ?>" class="button button-primary" style="margin-left:auto;">
      ⬇ Exporter CSV
    </a>
  </form>

  <!-- Tableau -->
  <table class="wp-list-table widefat fixed striped" style="margin-top:0;">
    <thead>
      <tr>
        <th style="width:40px;">ID</th>
        <th>Prénom</th>
        <th>Email</th>
        <th>Archétype</th>
        <th style="width:260px;">Scores OCEAN</th>
        <th style="width:60px;">DS</th>
        <th style="width:80px;" title="Décocher pour désactiver les relances J+3 et J+8 pour ce candidat">Relances</th>
        <th style="width:130px;">Date</th>
        <th style="width:80px;">Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php if ( empty($rows) ) : ?>
      <tr><td colspan="8" style="text-align:center;padding:32px;color:#94a3b8;">Aucun résultat.</td></tr>
    <?php else : ?>
    <?php foreach ( $rows as $r ) :
        $ds_alert = intval($r->score_DS) >= 70;
        $profil_url = home_url('/profil/' . $r->token);
    ?>
      <tr <?php if($ds_alert) echo 'style="background:#fef2f2;"'; ?>>
        <td><?php echo intval($r->id); ?></td>
        <td><strong><?php echo esc_html($r->prenom); ?></strong></td>
        <td style="font-size:12px;"><?php echo esc_html($r->email); ?></td>
        <td style="font-size:12px;"><?php echo esc_html($r->archetype_nom ?: '—'); ?></td>
        <td>
          <?php
          $dims = array('O'=>'#E8541A','C'=>'#1E2A3A','E'=>'#C4430F','A'=>'#2E4A6A','N'=>'#8FA8BE');
          foreach($dims as $d=>$c):
              $pct = intval($r->{'score_'.$d});
          ?>
          <span title="<?php echo $d; ?>" style="display:inline-block;width:36px;background:#e2e8f0;border-radius:3px;overflow:hidden;height:6px;vertical-align:middle;margin-right:3px;">
            <span style="display:block;width:<?php echo $pct; ?>%;height:6px;background:<?php echo $c; ?>;"></span>
          </span>
          <?php endforeach; ?>
        </td>
        <td>
          <span style="<?php echo $ds_alert ? 'color:#C4430F;font-weight:700;' : ''; ?>">
            <?php echo intval($r->score_DS); ?>%
          </span>
          <?php if($ds_alert) echo '<span title="DS élevée" style="color:#C4430F;"> ⚠</span>'; ?>
        </td>
        <td style="text-align:center;">
          <input type="checkbox"
            <?php checked( empty($r->relance_bloquee) ); ?>
            title="Relances actives pour ce candidat"
            onchange="ppToggleRelance(<?php echo intval($r->id); ?>, this)"
            style="cursor:pointer;width:16px;height:16px;">
        </td>
        <td style="font-size:12px;"><?php echo esc_html( date('d/m/Y H:i', strtotime($r->date_soumis)) ); ?></td>
        <td style="white-space:nowrap;">
          <a href="<?php echo esc_url(admin_url('admin.php?page=pp-resultats&detail='.$r->id)); ?>" class="button button-small">Détail</a>
          <?php if($r->token): ?>
          <a href="<?php echo esc_url($profil_url); ?>" class="button button-small" target="_blank" title="Voir profil public">↗</a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>

  <!-- Pagination -->
  <?php if ($pages > 1) : ?>
  <div style="margin-top:16px;display:flex;gap:8px;align-items:center;">
    <?php for($p=1; $p<=$pages; $p++):
        $url = add_query_arg(array_merge($filters,array('page'=>'pp-resultats','paged'=>$p)), admin_url('admin.php'));
    ?>
    <a href="<?php echo esc_url($url); ?>"
       style="padding:6px 12px;border-radius:6px;border:1px solid #e2e8f0;text-decoration:none;<?php echo $p==$page?'background:#E8541A;color:#fff;':'background:#fff;color:#334155;'; ?>">
      <?php echo $p; ?>
    </a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
<script>
function ppToggleRelance(id, cb) {
  var valeur = cb.checked ? 0 : 1;
  cb.disabled = true;
  fetch(PP_AJAX_URL, {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: 'action=pp_toggle_relance_bloquee&nonce='+PP_RELANCE_NONCE+'&id='+id+'&valeur='+valeur
  })
  .then(function(r){ return r.json(); })
  .then(function(data){
    cb.disabled = false;
    if (!data.success) { cb.checked = !cb.checked; alert('Erreur lors de la sauvegarde.'); }
  })
  .catch(function(){ cb.disabled = false; cb.checked = !cb.checked; });
}
</script>
