<?php if ( ! defined('ABSPATH') ) exit;
$campagnes  = PP_Batch::get_campagnes();
$detail_camp = sanitize_text_field($_GET['campagne'] ?? '');
$nonce_b    = wp_create_nonce('pp_batch_nonce');
$test_url   = get_option('pp_test_url', home_url('/'));
?>
<div class="wrap" style="max-width:900px;">
  <h1>📧 Mode batch — Invitations</h1>

  <!-- Formulaire envoi -->
  <div style="background:#fff;border:1.5px solid #e2e8f0;border-radius:14px;padding:28px;margin-bottom:32px;">
    <h2 style="margin:0 0 4px;font-size:16px;color:#1e293b;">Envoyer une nouvelle campagne</h2>
    <p style="margin:0 0 20px;font-size:13px;color:#64748b;">
      Chaque personne reçoit un lien unique qui pré-remplit son email.
      Format de la liste : une entrée par ligne — <code>email</code> ou <code>email, Prénom</code>
    </p>

    <table class="form-table" style="width:100%;">
      <tr>
        <th style="width:200px;text-align:left;padding:8px 16px 8px 0;font-size:13px;">Nom de la campagne</th>
        <td><input type="text" id="pp-batch-camp" class="regular-text" placeholder="Ex : Bilan d'équipe Mars 2026"></td>
      </tr>
      <tr>
        <th style="text-align:left;padding:8px 16px 8px 0;font-size:13px;vertical-align:top;">Liste de destinataires</th>
        <td>
          <textarea id="pp-batch-liste" rows="8" style="width:100%;max-width:500px;font-size:13px;font-family:monospace;"
                    placeholder="marie@example.fr, Marie&#10;paul@example.fr&#10;lea@example.fr, Léa Dupont"></textarea>
          <p class="description">Une adresse par ligne. Le prénom est facultatif.</p>
        </td>
      </tr>
      <tr>
        <th style="text-align:left;padding:8px 16px 8px 0;font-size:13px;vertical-align:top;">Message personnalisé (optionnel)</th>
        <td>
          <textarea id="pp-batch-msg" rows="3" style="width:100%;max-width:500px;font-size:13px;"
                    placeholder="Ajoutez un contexte ou une instruction spécifique à cette campagne…"></textarea>
        </td>
      </tr>
      <tr>
        <th style="text-align:left;padding:8px 16px 8px 0;font-size:13px;">Page du test</th>
        <td>
          <input type="url" id="pp-batch-testurl" value="<?php echo esc_attr($test_url); ?>" class="regular-text"
                 placeholder="URL de la page contenant [test_personnalite]">
          <p class="description">Enregistrez-la dans <a href="<?php echo admin_url('admin.php?page=pp-reglages'); ?>">Réglages → URL du test</a> pour ne plus avoir à la saisir.</p>
        </td>
      </tr>
    </table>

    <div style="margin-top:20px;">
      <button id="pp-batch-btn" class="button button-primary button-large"
              onclick="ppBatchSend()">
        Envoyer les invitations →
      </button>
      <span id="pp-batch-status" style="margin-left:16px;font-size:13px;color:#64748b;"></span>
    </div>
  </div>

  <!-- Liste campagnes -->
  <?php if ($campagnes) : ?>
  <div style="background:#fff;border:1.5px solid #e2e8f0;border-radius:14px;padding:24px;">
    <h2 style="margin:0 0 16px;font-size:16px;color:#1e293b;">Campagnes envoyées</h2>
    <table class="wp-list-table widefat fixed striped" style="margin:0;">
      <thead>
        <tr>
          <th>Campagne</th>
          <th style="width:80px;text-align:center;">Envoyées</th>
          <th style="width:80px;text-align:center;">Ouvertes</th>
          <th style="width:90px;text-align:center;">Complétées</th>
          <th style="width:90px;text-align:center;">Taux</th>
          <th style="width:120px;">Date</th>
          <th style="width:80px;">Détail</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($campagnes as $c) :
          $taux = $c->total > 0 ? round($c->complets/$c->total*100) : 0;
        ?>
        <tr>
          <td><strong><?php echo esc_html($c->campagne); ?></strong></td>
          <td style="text-align:center;"><?php echo intval($c->total); ?></td>
          <td style="text-align:center;"><?php echo intval($c->ouverts); ?></td>
          <td style="text-align:center;font-weight:700;color:#2E4A6A;"><?php echo intval($c->complets); ?></td>
          <td style="text-align:center;">
            <span style="display:inline-block;background:<?php echo $taux>=50?'#EEF3F8':($taux>=25?'#FFF4EF':'#FFF0EB'); ?>;color:<?php echo $taux>=50?'#2E4A6A':($taux>=25?'#E8541A':'#C4430F'); ?>;padding:2px 10px;border-radius:999px;font-size:12px;font-weight:700;">
              <?php echo $taux; ?>%
            </span>
          </td>
          <td style="font-size:12px;"><?php echo date('d/m/Y', strtotime($c->date_debut)); ?></td>
          <td style="white-space:nowrap;">
            <a href="<?php echo esc_url(add_query_arg(array('page'=>'pp-batch','campagne'=>urlencode($c->campagne)), admin_url('admin.php'))); ?>"
               class="button button-small">Détail</a>
            <?php
            $eq_token = PP_Batch::get_campagne_token($c->campagne);
            $eq_url   = home_url('/equipe/' . $eq_token);
            ?>
            <a href="<?php echo esc_url($eq_url); ?>" target="_blank"
               class="button button-small" title="Vue équipe publique">👥</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <!-- Détail campagne -->
  <?php if ($detail_camp) :
    $invites = PP_Batch::get_invites($detail_camp);
  ?>
  <div style="background:#fff;border:1.5px solid #e2e8f0;border-radius:14px;padding:24px;margin-top:24px;">
    <h2 style="margin:0 0 16px;font-size:16px;color:#1e293b;">
      Détail — <?php echo esc_html($detail_camp); ?>
      <a href="<?php echo esc_url(admin_url('admin.php?page=pp-batch')); ?>" style="font-size:12px;font-weight:400;margin-left:12px;">← Retour</a>
    </h2>
    <table class="wp-list-table widefat fixed striped">
      <thead><tr>
        <th>Email</th><th>Prénom</th>
        <th style="width:100px;">Statut</th>
        <th style="width:130px;">Date envoi</th>
        <th style="width:80px;">Résultat</th>
      </tr></thead>
      <tbody>
        <?php foreach ($invites as $inv) :
          $statut_colors = array('envoye'=>'#64748b','ouvert'=>'#E8541A','commence'=>'#E8541A','complete'=>'#2E4A6A');
          $col = $statut_colors[$inv->statut] ?? '#64748b';
        ?>
        <tr>
          <td><?php echo esc_html($inv->email); ?></td>
          <td><?php echo esc_html($inv->prenom ?: '—'); ?></td>
          <td>
            <span style="display:inline-block;background:<?php echo $col; ?>22;color:<?php echo $col; ?>;padding:2px 10px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase;">
              <?php echo esc_html($inv->statut); ?>
            </span>
          </td>
          <td style="font-size:12px;"><?php echo date('d/m H:i', strtotime($inv->date_envoi)); ?></td>
          <td>
            <?php if ($inv->resultat_id) : ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=pp-resultats&detail='.$inv->resultat_id)); ?>" class="button button-small">Voir</a>
            <?php else : echo '—'; endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<script>
function ppBatchSend() {
  var camp  = document.getElementById('pp-batch-camp').value.trim();
  var liste = document.getElementById('pp-batch-liste').value.trim();
  var msg   = document.getElementById('pp-batch-msg').value.trim();
  var turl  = document.getElementById('pp-batch-testurl').value.trim();
  var btn   = document.getElementById('pp-batch-btn');
  var status= document.getElementById('pp-batch-status');

  if (!camp || !liste) { status.textContent = '⚠️ Nom de campagne et liste obligatoires.'; return; }

  btn.disabled = true;
  btn.textContent = '⏳ Envoi en cours…';
  status.textContent = '';

  // Sauvegarder l'URL du test
  if (turl) {
    var fd0 = new FormData();
    fd0.append('action','pp_save_option'); fd0.append('nonce','<?php echo wp_create_nonce('pp_save_option'); ?>');
    fd0.append('key','pp_test_url'); fd0.append('value', turl);
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {method:'POST',body:fd0});
  }

  var fd = new FormData();
  fd.append('action','pp_batch_send');
  fd.append('nonce','<?php echo esc_js($nonce_b); ?>');
  fd.append('campagne', camp);
  fd.append('liste', liste);
  fd.append('message_perso', msg);

  fetch('<?php echo admin_url('admin-ajax.php'); ?>', {method:'POST',body:fd})
    .then(function(r){return r.json();})
    .then(function(data){
      btn.disabled=false; btn.textContent='Envoyer les invitations →';
      if(data.success) {
        var d = data.data;
        status.style.color='#2E4A6A';
        status.textContent = '✅ ' + d.sent + ' invitation(s) envoyée(s)'
          + (d.errors>0 ? ' — ' + d.errors + ' erreur(s) : ' + d.err_list : '');
        setTimeout(function(){location.reload();},2000);
      } else {
        status.style.color='#C4430F';
        status.textContent = '❌ ' + (data.data.message || 'Erreur');
      }
    });
}
</script>
