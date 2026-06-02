<?php if ( ! defined('ABSPATH') ) exit;
$stats       = PP_Codes::get_stats();
$nonce_c     = wp_create_nonce('pp_codes_nonce');
$detail_camp = sanitize_text_field($_GET['campagne'] ?? '');
?>
<div class="wrap" style="max-width:860px;">
  <h1>🔑 Codes d'accès anonymes</h1>
  <p style="color:#64748b;margin-bottom:24px;">
    Distribuez des codes plutôt que des liens nommés pour garantir l'anonymat.
    Chaque code est à usage unique. Les participants saisissent leur code sur la page du test.
  </p>

  <!-- Générateur -->
  <div style="background:#fff;border:1.5px solid #e2e8f0;border-radius:14px;padding:24px;margin-bottom:28px;">
    <h2 style="margin:0 0 16px;font-size:15px;color:#1e293b;">Générer de nouveaux codes</h2>
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
      <div>
        <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Nom du lot</label>
        <input type="text" id="pp-codes-camp" class="regular-text"
               placeholder="Ex : Bilan équipe RH — Mars 2026" style="width:260px;">
      </div>
      <div>
        <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Nombre de codes</label>
        <input type="number" id="pp-codes-n" value="20" min="1" max="500"
               style="width:80px;padding:6px 10px;border:1px solid #e2e8f0;border-radius:6px;">
      </div>
      <button onclick="ppGenererCodes()" class="button button-primary">Générer →</button>
    </div>
    <div id="pp-codes-result" style="display:none;margin-top:20px;"></div>
  </div>

  <!-- Liste des lots -->
  <?php if ($stats) : ?>
  <div style="background:#fff;border:1.5px solid #e2e8f0;border-radius:14px;padding:24px;">
    <h2 style="margin:0 0 16px;font-size:15px;color:#1e293b;">Lots de codes</h2>
    <table class="wp-list-table widefat fixed striped">
      <thead><tr>
        <th>Lot</th>
        <th style="width:80px;text-align:center;">Total</th>
        <th style="width:100px;text-align:center;">Disponibles</th>
        <th style="width:80px;text-align:center;">Utilisés</th>
        <th style="width:90px;text-align:center;">Taux</th>
        <th style="width:100px;">Actions</th>
      </tr></thead>
      <tbody>
        <?php foreach ($stats as $s) :
          $taux = $s->total > 0 ? round($s->utilises/$s->total*100) : 0;
        ?>
        <tr>
          <td><strong><?php echo esc_html($s->campagne); ?></strong></td>
          <td style="text-align:center;"><?php echo intval($s->total); ?></td>
          <td style="text-align:center;color:#2E4A6A;font-weight:700;"><?php echo intval($s->disponibles); ?></td>
          <td style="text-align:center;"><?php echo intval($s->utilises); ?></td>
          <td style="text-align:center;">
            <span style="display:inline-block;background:<?php echo $taux>=50?'#EEF3F8':($taux>=25?'#FFF4EF':'#FFF0EB'); ?>;color:<?php echo $taux>=50?'#2E4A6A':($taux>=25?'#E8541A':'#C4430F'); ?>;padding:2px 10px;border-radius:999px;font-size:12px;font-weight:700;">
              <?php echo $taux; ?>%
            </span>
          </td>
          <td>
            <a href="<?php echo esc_url(add_query_arg(array('page'=>'pp-codes','campagne'=>urlencode($s->campagne)), admin_url('admin.php'))); ?>"
               class="button button-small">Liste</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <!-- Détail codes -->
  <?php if ($detail_camp) :
    $codes = PP_Codes::get_codes($detail_camp);
    $disponibles = array_filter($codes, fn($c)=>$c->statut==='disponible');
  ?>
  <div style="background:#fff;border:1.5px solid #e2e8f0;border-radius:14px;padding:24px;margin-top:24px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
      <h2 style="margin:0;font-size:15px;color:#1e293b;">
        Codes — <?php echo esc_html($detail_camp); ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=pp-codes')); ?>" style="font-size:12px;font-weight:400;margin-left:12px;">← Retour</a>
      </h2>
      <button onclick="ppCopierTousCodes()" class="button">📋 Copier les codes disponibles</button>
    </div>

    <!-- Codes disponibles en bloc texte copiable -->
    <?php if ($disponibles) : ?>
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px;margin-bottom:16px;font-family:monospace;font-size:13px;line-height:1.8;" id="pp-codes-dispo">
      <?php foreach ($disponibles as $c) : ?>
      <span style="display:inline-block;margin:2px 8px 2px 0;padding:2px 10px;background:#fff;border:1px solid #e2e8f0;border-radius:4px;"><?php echo esc_html($c->code); ?></span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <table class="wp-list-table widefat fixed striped">
      <thead><tr>
        <th>Code</th>
        <th style="width:120px;">Statut</th>
        <th style="width:130px;">Date création</th>
        <th style="width:130px;">Date utilisation</th>
      </tr></thead>
      <tbody>
        <?php foreach ($codes as $c) :
          $col = $c->statut === 'disponible' ? '#2E4A6A' : '#94A3B8';
        ?>
        <tr>
          <td style="font-family:monospace;font-size:13px;font-weight:700;"><?php echo esc_html($c->code); ?></td>
          <td>
            <span style="color:<?php echo $col; ?>;font-size:12px;font-weight:700;text-transform:uppercase;">
              <?php echo esc_html($c->statut); ?>
            </span>
          </td>
          <td style="font-size:12px;"><?php echo date('d/m/Y H:i',strtotime($c->date_cree)); ?></td>
          <td style="font-size:12px;"><?php echo $c->date_utilise ? date('d/m/Y H:i',strtotime($c->date_utilise)) : '—'; ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<script>
function ppGenererCodes() {
  var camp = document.getElementById('pp-codes-camp').value.trim();
  var n    = document.getElementById('pp-codes-n').value;
  var res  = document.getElementById('pp-codes-result');
  if (!camp) { res.style.display='block'; res.innerHTML='<p style="color:#C4430F;">⚠️ Entrez un nom de lot.</p>'; return; }

  var fd = new FormData();
  fd.append('action','pp_generer_codes');
  fd.append('nonce','<?php echo esc_js($nonce_c); ?>');
  fd.append('campagne', camp);
  fd.append('nombre', n);

  fetch('<?php echo admin_url('admin-ajax.php'); ?>',{method:'POST',body:fd})
    .then(function(r){return r.json();})
    .then(function(d){
      if (d.success) {
        var html = '<p style="color:#2E4A6A;font-weight:700;">✅ '+d.data.n+' codes générés !</p>'
          + '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px;font-family:monospace;font-size:13px;line-height:1.8;" id="pp-new-codes">';
        d.data.codes.forEach(function(c){
          html += '<span style="display:inline-block;margin:2px 6px 2px 0;padding:2px 10px;background:#fff;border:1px solid #e2e8f0;border-radius:4px;">'+c+'</span>';
        });
        html += '</div><button onclick="ppCopierNouveauxCodes()" class="button" style="margin-top:10px;">📋 Copier tous les codes</button>';
        res.innerHTML = html;
        res.style.display = 'block';
        setTimeout(function(){location.reload();},4000);
      } else {
        res.style.display='block';
        res.innerHTML='<p style="color:#C4430F;">❌ '+(d.data.message||'Erreur')+'</p>';
      }
    });
}

function ppCopierNouveauxCodes() {
  var el = document.getElementById('pp-new-codes');
  if (!el) return;
  var codes = Array.from(el.querySelectorAll('span')).map(function(s){return s.textContent.trim();}).join('\n');
  navigator.clipboard.writeText(codes).then(function(){ ppToast('✅ Codes copiés !'); });
}

function ppCopierTousCodes() {
  var el = document.getElementById('pp-codes-dispo');
  if (!el) return;
  var codes = Array.from(el.querySelectorAll('span')).map(function(s){return s.textContent.trim();}).join('\n');
  navigator.clipboard.writeText(codes).then(function(){ ppToast('✅ '+codes.split('\n').length+' codes copiés !'); });
}

function ppToast(msg) {
  var t=document.createElement('div');
  t.textContent=msg;
  t.style.cssText='position:fixed;bottom:24px;right:24px;background:#1e293b;color:#fff;padding:10px 20px;border-radius:8px;font-size:13px;z-index:9999;';
  document.body.appendChild(t);
  setTimeout(function(){t.remove();},3000);
}
</script>
