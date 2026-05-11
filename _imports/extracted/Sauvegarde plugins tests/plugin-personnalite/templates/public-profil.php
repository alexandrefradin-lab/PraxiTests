<?php
/**
 * Template page publique profil
 * Variables : $row, $scores_data, $arch_data, $profil, $carte_html, $rdv_url, $site_name
 */
if ( ! defined('ABSPATH') ) exit;

$token_actuel  = $row->token;
$nonce_pub     = wp_create_nonce('pp_nonce');
$ajax_url      = admin_url('admin-ajax.php');
$pdf_url       = home_url( '/profil/' . $token_actuel . '?pp_pdf=1' );
$facettes_map  = PP_Questions::get_facettes_map();
$scores_fac    = $scores_data['scores_facette'] ?? array();
$scores_dim    = $scores_data['scores_dim'] ?? array();

$dim_cfg = array(
    'O' => array('icon'=>'🔭','label'=>'Ouverture',          'color'=>'#1E2A3A',
                 'desc'=>"Mesure la curiosité intellectuelle, la créativité et l'ouverture aux nouvelles expériences."),
    'C' => array('icon'=>'🗂️','label'=>'Conscience',         'color'=>'#E8541A',
                 'desc'=>"Reflète l'organisation, la fiabilité, l'autodiscipline et la persévérance dans les objectifs."),
    'E' => array('icon'=>'💬','label'=>'Extraversion',       'color'=>'#C4430F',
                 'desc'=>"Évalue la sociabilité, l'assertivité et l'énergie tirée des interactions sociales."),
    'A' => array('icon'=>'🤝','label'=>'Agréabilité',        'color'=>'#2E4A6A',
                 'desc'=>"Traduit la coopération, l'empathie et la tendance à la confiance envers autrui."),
    'N' => array('icon'=>'🌊','label'=>'Stabilité émotionnelle','color'=>'#8FA8BE',
                 'desc'=>"Indique la fréquence des émotions négatives. Score bas = grande stabilité."),
);
?>
<div class="pp-public-profil" style="max-width:760px;margin:40px auto;padding:0 16px 80px;font-family:'Segoe UI',system-ui,sans-serif;">

<?php
  $c1 = ! empty($arch_data['couleur1']) ? PP_Archetypes::sanitize_color($arch_data['couleur1']) : '';
  $c1 = $c1 ?: '#E8541A';
  $c2 = ! empty($arch_data['couleur2']) ? PP_Archetypes::sanitize_color($arch_data['couleur2']) : '';
  $c2 = $c2 ?: '#1E2A3A';
?>
  <!-- ── HERO v1 ── -->
  <?php if ( ! empty($arch_data) ) :
    $initiales = strtoupper(substr($row->prenom, 0, 2));
  ?>
  <div style="background:<?php echo $c2; ?> !important;border-radius:24px;padding:48px 28px 36px;text-align:center;color:#fff;margin-bottom:20px;position:relative;overflow:hidden;">
    <div style="position:absolute;top:-50px;right:-50px;width:180px;height:180px;background:rgba(255,255,255,.04);border-radius:50%;"></div>
    <div style="font-size:64px;line-height:1;margin-bottom:16px;position:relative;"><?php echo $arch_data['emoji'] ?? ''; ?></div>
    <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:.14em;opacity:.6;font-weight:600;">Le profil de</p>
    <p style="margin:0 0 12px;font-size:15px;font-weight:700;opacity:.85;"><?php echo esc_html($row->prenom); ?></p>
    <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:.14em;opacity:.6;font-weight:600;">Votre archétype</p>
    <h1 style="font-size:28px;font-weight:900;margin:0 0 8px;letter-spacing:-.02em;color:#fff;"><?php echo esc_html($arch_data['nom']??''); ?></h1>
    <p style="font-size:14px;font-style:italic;margin:0 0 20px;opacity:.8;"><?php echo esc_html($arch_data['tagline']??''); ?></p>
    <?php if(!empty($arch_data['rarete'])): ?>
    <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:999px;padding:6px 16px;font-size:12px;font-weight:600;margin-bottom:20px;">✦ Profil présent chez seulement <?php echo intval($arch_data['rarete']); ?>% des personnes</div>
    <?php endif; ?>
    <div style="margin-top:16px;">
      <button onclick="ppGeneratePDFPublic()" style="display:inline-block;background:rgba(255,255,255,.12);border:1.5px solid rgba(255,255,255,.3);color:#fff;cursor:pointer;padding:10px 24px;border-radius:999px;font-size:13px;font-weight:700;font-family:inherit;">⬇ Télécharger mon rapport PDF</button>
    </div>
  </div>

  <!-- Description -->
  <?php if(!empty($arch_data['description'])): ?>
  <div style="background:#fff;border-left:4px solid <?php echo $c1; ?>;border-radius:0 16px 16px 0;padding:18px 22px;margin-bottom:16px;">
    <p style="margin:0;color:#334155;font-size:14px;line-height:1.85;"><?php echo esc_html($arch_data['description']); ?></p>
  </div>
  <?php endif; ?>

  <!-- Traits -->
  <?php if(!empty($arch_data['traits'])): ?>
  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;">
    <?php foreach((array)$arch_data['traits'] as $tr): ?>
    <span style="background:<?php echo $c2; ?> !important;color:#fff !important;padding:5px 14px;border-radius:999px;font-size:12px;font-weight:600;"><?php echo esc_html($tr); ?></span>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>

  <!-- ── VUE D'ENSEMBLE OCEAN ── -->
  <div style="margin-bottom:32px;">
    <h2 style="font-size:18px;font-weight:800;color:#1E2A3A;margin:0 0 16px;padding-bottom:8px;border-bottom:2px solid #e2e8f0;">
      📊 Les 5 grandes dimensions
    </h2>
    <?php foreach ( $dim_cfg as $key => $cfg ) :
      $dim_data = $scores_dim[$key] ?? array();
      $pct   = intval($dim_data['pct']   ?? 50);
      $niveau= esc_html($dim_data['label'] ?? '');
    ?>
    <div style="margin-bottom:12px;padding:14px 16px;background:#fff;border:1.5px solid #e2e8f0;border-radius:12px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
        <span style="font-weight:700;font-size:14px;color:#1E2A3A;">
          <?php echo $cfg['icon']; ?> <?php echo esc_html($cfg['label']); ?>
        </span>
        <span style="font-weight:800;font-size:16px;color:<?php echo $cfg['color']; ?>;"><?php echo $pct; ?>%</span>
      </div>
      <div style="background:#e2e8f0;border-radius:999px;height:9px;overflow:hidden;margin-bottom:8px;">
        <div style="background:<?php echo $cfg['color']; ?>;height:9px;width:<?php echo $pct; ?>%;border-radius:999px;"></div>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <span style="font-size:12px;color:#64748b;"><?php echo $cfg['desc']; ?></span>
        <span style="font-size:11px;font-weight:600;color:<?php echo $cfg['color']; ?>;white-space:nowrap;margin-left:12px;"><?php echo $niveau; ?></span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- ── 30 FACETTES PAR DIMENSION ── -->
  <div style="margin-bottom:32px;">
    <h2 style="font-size:18px;font-weight:800;color:#1E2A3A;margin:0 0 4px;padding-bottom:8px;border-bottom:2px solid #e2e8f0;">
      🔬 Détail des 30 facettes
    </h2>
    <p style="font-size:13px;color:#64748b;margin:0 0 20px;">Chaque dimension se décompose en 6 facettes qui précisent votre fonctionnement.</p>

    <?php foreach ( $dim_cfg as $key => $cfg ) :
      $fac_keys = array_filter( array_keys($facettes_map), function($k) use ($facettes_map, $key) {
          return ($facettes_map[$k]['dim'] ?? '') === $key;
      });
    ?>
    <div style="margin-bottom:20px;">
      <div style="background:<?php echo $cfg['color']; ?> !important;color:#fff !important;border-radius:12px 12px 0 0;padding:12px 16px;display:flex;justify-content:space-between;align-items:center;">
        <span style="font-weight:800;font-size:14px;"><?php echo $cfg['icon']; ?> <?php echo esc_html($cfg['label']); ?></span>
        <span style="font-size:13px;opacity:.85;"><?php echo intval($scores_dim[$key]['pct']??50); ?>% — <?php echo esc_html($scores_dim[$key]['label']??''); ?></span>
      </div>
      <div style="border:1.5px solid #e2e8f0;border-top:none;border-radius:0 0 12px 12px;overflow:hidden;">
        <?php $i = 0; foreach ( $fac_keys as $fk ) :
          $fm   = $facettes_map[$fk];
          $fs   = $scores_fac[$fk] ?? array();
          $fpct = intval($fs['pct'] ?? 50);
          $flbl = esc_html($fs['label'] ?? '');
          $bg   = ($i % 2 === 0) ? '#fff' : '#f8fafc';
          $i++;
        ?>
        <div style="padding:12px 16px;background:<?php echo $bg; ?>;<?php echo $i < count(array_values($fac_keys)) ? 'border-bottom:1px solid #e2e8f0;' : ''; ?>">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
            <div>
              <span style="font-weight:700;font-size:13px;color:#1E2A3A;"><?php echo esc_html($fm['label']); ?></span>
              <span style="font-size:11px;color:#64748b;margin-left:8px;"><?php echo $flbl; ?></span>
            </div>
            <span style="font-weight:700;font-size:13px;color:<?php echo $cfg['color']; ?>;white-space:nowrap;"><?php echo $fpct; ?>%</span>
          </div>
          <div style="background:#e2e8f0;border-radius:999px;height:6px;overflow:hidden;margin-bottom:5px;">
            <div style="background:<?php echo $cfg['color']; ?>;height:6px;width:<?php echo $fpct; ?>%;border-radius:999px;opacity:.85;"></div>
          </div>
          <p style="margin:0;font-size:12px;color:#64748b;line-height:1.5;"><?php echo esc_html($fm['desc']); ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- ── CARTE PARTAGEABLE ── -->
  <?php echo $carte_html; ?>

  <!-- ── COMPATIBILITE ── -->
  <div id="pp-compat-section" style="margin:32px 0 0;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:16px;padding:28px;">
    <h2 style="margin:0 0 8px;font-size:18px;font-weight:800;color:#1E2A3A;">🤝 Tester la compatibilité</h2>
    <p style="margin:0 0 18px;color:#64748b;font-size:13px;">Invitez un collègue à faire le test puis entrez son lien de profil ci-dessous.</p>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <input type="url" id="pp-compat-url" placeholder="https://votresite.fr/profil/abc123..."
             style="flex:1;min-width:220px;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;" />
      <button onclick="ppCalculerCompat()"
              style="background:#E8541A;color:#fff;padding:10px 22px;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;">
        Calculer →
      </button>
    </div>
    <div style="margin-top:10px;font-size:12px;color:#94a3b8;">
      💡 <button onclick="ppCopierLien()" style="background:none;border:none;color:#E8541A;font-weight:600;cursor:pointer;font-size:12px;padding:0 3px;">Copier mon lien profil</button> pour l'envoyer à un collègue.
    </div>
    <div id="pp-compat-result" style="display:none;margin-top:20px;"></div>
  </div>

  <!-- ── RGPD ── -->
  <div style="text-align:center;margin-top:40px;padding-top:20px;border-top:1px solid #e2e8f0;">
    <p style="font-size:12px;color:#94a3b8;margin:0;">
      Vos données sont traitées conformément au RGPD —
      <a href="<?php echo esc_url(home_url('/supprimer-mes-donnees/'.$token_actuel)); ?>" style="color:#94a3b8;">Supprimer mes données</a>
      ·
      <a href="<?php echo esc_url(home_url('/mes-donnees/'.$token_actuel)); ?>" style="color:#94a3b8;">Télécharger mon dossier</a>
    </p>
  </div>

  <!-- ── CTA RDV v1 ── -->
  <div style="background:#1E2A3A !important;border-radius:20px;padding:28px 24px;margin:32px 0 16px;color:#fff !important;">
    <p style="font-size:10px;text-transform:uppercase;letter-spacing:.14em;color:#E8541A !important;font-weight:700;margin:0 0 10px;">Et maintenant ?</p>
    <p style="font-size:18px;font-weight:800;line-height:1.3;margin:0 0 8px;color:#fff !important;">Transformez ce profil en projet professionnel concret</p>
    <p style="font-size:13px;line-height:1.65;margin:0 0 20px;color:rgba(255,255,255,.7) !important;">Le bilan de compétences vous aide à clarifier vos forces, vos motivations et construire un cap professionnel aligné avec qui vous êtes vraiment.</p>
    <a href="<?php echo esc_url($rdv_url); ?>" style="display:block !important;text-align:center;background:#E8541A !important;color:#fff !important;padding:14px;border-radius:999px;text-decoration:none !important;font-size:14px;font-weight:700;">✦ Réserver mon entretien gratuit</a>
  </div>
</div>

<script>
// Données profil pour la génération PDF côté client
var PP_PROFILE_DATA = <?php echo wp_json_encode(array(
  'prenom'         => $row->prenom,
  'archetype'      => $arch_data,
  'scores_dim'     => $scores_data['scores_dim']     ?? array(),
  'scores_facette' => $scores_data['scores_facette'] ?? array(),
  'facettes_map'   => array_map(function($v){ return array('label'=>$v['label'],'dim'=>$v['dim'],'desc'=>$v['desc']); }, $facettes_map),
  'rdv_url'        => $rdv_url,
  'profil_url'     => home_url('/profil/'.$row->token),
  'site_name'      => get_bloginfo('name'),
  'date'           => date_i18n( get_option('date_format'), strtotime($row->date_soumis) ),
)); ?>;

// Alias pour que front.js récupère profil_url lors du partage LinkedIn
window.ppProfileData = PP_PROFILE_DATA;

function ppGeneratePDFPublic() {
  if (window.ppGeneratePDF) { ppGeneratePDF(PP_PROFILE_DATA); }
  else { setTimeout(function(){ if(window.ppGeneratePDF) ppGeneratePDF(PP_PROFILE_DATA); else ppToast('⚠️ PDF indisponible.'); }, 1200); }
}

var PP_TOKEN = '<?php echo esc_js($token_actuel); ?>';
var PP_NONCE = '<?php echo esc_js($nonce_pub); ?>';
var PP_AJAX_URL = (typeof PP_AJAX === 'object') ? PP_AJAX.url : '<?php echo esc_js($ajax_url); ?>';
var PP_URL   = '<?php echo esc_js(home_url('/profil/'.$token_actuel)); ?>';

function ppCopierLien() {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(PP_URL).then(function(){ ppToast('✅ Lien copié !'); });
  }
}

function ppCalculerCompat() {
  var input = document.getElementById('pp-compat-url').value.trim();
  // Accepte tokens hex 32 cars (minuscules ou majuscules) + éventuellement avec slash final
  var match = input.match(/\/profil\/([a-fA-F0-9]{32})\/?/);
  if (!match) {
    ppToast('⚠️ URL invalide. Collez le lien complet : https://…/profil/abc123…');
    return;
  }
  var token_b = match[1].toLowerCase();
  if (token_b === PP_TOKEN.toLowerCase()) {
    ppToast('⚠️ Entrez le profil d\'une autre personne !');
    return;
  }
  var btn = document.querySelector('#pp-compat-section button');
  var origLabel = btn.textContent;
  btn.innerHTML = '⏳ …'; btn.disabled = true;
  var fd = new FormData();
  fd.append('action','pp_compat');
  fd.append('nonce', PP_NONCE);
  fd.append('token_a', PP_TOKEN);
  fd.append('token_b', token_b);
  fetch(PP_AJAX_URL, {method:'POST', body:fd})
    .then(function(r){ return r.text(); })
    .then(function(txt){
      btn.textContent = origLabel; btn.disabled = false;
      try {
        var start = txt.indexOf('{');
        var d = JSON.parse(start >= 0 ? txt.slice(start) : txt);
        if (d.success) {
          renderCompatResult(d.data);
        } else {
          ppToast('❌ ' + (d.data && d.data.message ? d.data.message : 'Profil introuvable ou lien incorrect.'));
        }
      } catch(e) {
        ppToast('❌ Réponse inattendue du serveur. Réessayez.');
      }
    })
    .catch(function(e){
      btn.textContent = origLabel; btn.disabled = false;
      ppToast('❌ Erreur réseau. Réessayez.');
    });
}

function renderCompatResult(d) {
  var color = d.score>=78?'#16A34A':d.score>=62?'#E8541A':d.score>=45?'#D97706':'#DC2626';
  var bars = '';
  Object.keys(d.details).forEach(function(k) {
    var dim = d.details[k];
    var pictoColor = dim.picto==='✅'?'#16A34A':dim.picto==='⚡'?'#D97706':'#DC2626';
    bars += '<div style="margin-bottom:14px;padding:12px 14px;background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;">'
      + '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">'
      +   '<span style="font-weight:700;color:#1E2A3A;font-size:13px;">'+dim.label+'</span>'
      +   '<span style="font-size:16px;color:'+pictoColor+';">'+dim.picto+'</span>'
      + '</div>'
      + '<div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">'
      +   '<span style="font-size:11px;color:#64748b;width:80px;text-align:right;flex-shrink:0;">'+d.prenom_a+' '+dim.score_a+'%</span>'
      +   '<div style="flex:1;background:#e2e8f0;border-radius:999px;height:8px;">'
      +     '<div style="background:#1E2A3A;height:8px;border-radius:999px;width:'+dim.score_a+'%;"></div>'
      +   '</div>'
      + '</div>'
      + '<div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">'
      +   '<span style="font-size:11px;color:#64748b;width:80px;text-align:right;flex-shrink:0;">'+d.prenom_b+' '+dim.score_b+'%</span>'
      +   '<div style="flex:1;background:#e2e8f0;border-radius:999px;height:8px;">'
      +     '<div style="background:#E8541A;height:8px;border-radius:999px;width:'+dim.score_b+'%;"></div>'
      +   '</div>'
      + '</div>'
      + (dim.phrase ? '<p style="margin:0;font-size:12px;color:#475569;font-style:italic;line-height:1.5;">'+dim.phrase+'</p>' : '')
      + '</div>';
  });
  var html = '<div style="text-align:center;margin-bottom:24px;">'
    + '<div style="font-size:44px;margin-bottom:4px;">'+d.emoji+'</div>'
    + '<div style="font-size:52px;font-weight:900;color:'+color+';line-height:1;">'+d.score+'<span style="font-size:22px;">%</span></div>'
    + '<div style="font-size:17px;font-weight:700;color:#1E2A3A;margin:6px 0 4px;">'+d.niveau+'</div>'
    + '<p style="font-size:13px;color:#64748b;margin:0 auto;max-width:360px;">'+d.description+'</p>'
    + '<div style="display:flex;justify-content:center;gap:16px;margin-top:10px;font-size:11px;color:#94a3b8;">'
    +   '<span><span style="display:inline-block;width:10px;height:10px;background:#1E2A3A;border-radius:50%;margin-right:4px;vertical-align:middle;"></span>'+d.prenom_a+'</span>'
    +   '<span><span style="display:inline-block;width:10px;height:10px;background:#E8541A;border-radius:50%;margin-right:4px;vertical-align:middle;"></span>'+d.prenom_b+'</span>'
    + '</div>'
    + '</div>'
    + '<div>'+bars+'</div>';
  var el = document.getElementById('pp-compat-result');
  el.innerHTML = html;
  el.style.display = 'block';
  el.scrollIntoView({behavior:'smooth', block:'nearest'});
}

// Auto-déclenche le PDF si l'URL contient #download-pdf (redirect depuis ?pp_pdf=1)
document.addEventListener('DOMContentLoaded', function() {
  if (window.location.hash === '#download-pdf') {
    history.replaceState(null, '', window.location.pathname);
    setTimeout(ppGeneratePDFPublic, 800);
  }
});

function ppToast(msg) {
  var t=document.createElement('div');
  t.textContent=msg;
  t.style.cssText='position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:#1E2A3A;color:#fff;padding:10px 20px;border-radius:999px;font-size:13px;font-weight:600;z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,.3);';
  document.body.appendChild(t);
  setTimeout(function(){t.remove();},3000);
}
</script>
