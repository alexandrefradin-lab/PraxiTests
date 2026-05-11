<?php
/**
 * PraxiMet – Vue Paramètres
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$calendly         = get_option( 'praximet_calendly_url',     '' );
$email_conseiller = get_option( 'praximet_email_conseiller', get_option('admin_email') );
$delai            = (int) get_option( 'praximet_delai_relance', 48 );
$smtp_host        = get_option( 'praximet_smtp_host',   'ssl0.ovh.net' );
$smtp_port        = (int) get_option( 'praximet_smtp_port',   465 );
$smtp_user        = get_option( 'praximet_smtp_user',   '' );
$smtp_from        = get_option( 'praximet_smtp_from',   'contact@praxis-accompagnement.com' );
$smtp_secure      = get_option( 'praximet_smtp_secure', 'ssl' );
$smtp_configured  = ! empty( get_option('praximet_smtp_pass', '') );
$saved            = isset( $_GET['saved'] );
?>

<div class="wrap praximet-wrap">

  <div class="praximet-admin-header">
    <h1 class="praximet-admin-title">
      <span class="praximet-logo">P</span> PraxiMet — Paramètres
    </h1>
    <a href="<?php echo esc_url( admin_url('admin.php?page=praximet-leads') ); ?>"
       class="praximet-btn-secondary">← Retour aux leads</a>
  </div>

  <?php if ( $saved ) : ?>
  <div class="praximet-notice praximet-notice--success">
    ✓ Paramètres enregistrés avec succès.
  </div>
  <?php endif; ?>

  <form method="post"
        action="<?php echo esc_url( admin_url('admin-post.php') ); ?>"
        class="praximet-settings-form">

    <input type="hidden" name="action" value="praximet_save_settings">
    <?php wp_nonce_field('praximet_save_settings'); ?>

    <!-- ── Intégration Calendly ─────────────────────────────────────── -->
    <div class="praximet-card">
      <h2 class="praximet-card-title">📅 Intégration Calendly</h2>
      <div class="praximet-field">
        <label for="praximet_calendly_url">URL de votre page Calendly</label>
        <input type="url"
               id="praximet_calendly_url"
               name="praximet_calendly_url"
               value="<?php echo esc_attr($calendly); ?>"
               placeholder="https://calendly.com/votre-nom/entretien-decouverte"
               class="praximet-input-large" />
        <p class="praximet-field-desc">
          Collez ici l'URL de votre événement Calendly.
          Elle sera utilisée dans les emails et sur la page de résultat du quiz.
        </p>
        <?php if ( $calendly ) : ?>
        <p>
          <a href="<?php echo esc_url($calendly); ?>"
             target="_blank" rel="noopener"
             class="praximet-btn-link">Tester le lien →</a>
        </p>
        <?php endif; ?>
      </div>
    </div>

    <!-- ── Notifications ────────────────────────────────────────────── -->
    <div class="praximet-card">
      <h2 class="praximet-card-title">✉ Notifications</h2>

      <div class="praximet-field">
        <label for="praximet_email_conseiller">Email de notification (le vôtre)</label>
        <input type="email"
               id="praximet_email_conseiller"
               name="praximet_email_conseiller"
               value="<?php echo esc_attr($email_conseiller); ?>"
               placeholder="vous@exemple.fr"
               class="praximet-input-large" />
        <p class="praximet-field-desc">
          Vous recevrez un email à cette adresse à chaque nouveau lead.
        </p>
      </div>

      <div class="praximet-field">
        <label for="praximet_delai_relance">Délai avant relance automatique (en heures)</label>
        <input type="number"
               id="praximet_delai_relance"
               name="praximet_delai_relance"
               value="<?php echo esc_attr($delai); ?>"
               min="1" max="168"
               class="praximet-input-small" />
        <p class="praximet-field-desc">
          Si le candidat n'a pas pris de RDV après ce délai, un email de relance
          lui sera envoyé automatiquement. Valeur recommandée : 48h.
        </p>
      </div>
    </div>

    <!-- ── Configuration SMTP ───────────────────────────────────────── -->
    <div class="praximet-card">
      <h2 class="praximet-card-title">
        📨 Configuration SMTP
        <?php if ( $smtp_configured ) : ?>
          <span style="font-size:12px;font-weight:400;color:#16a34a;margin-left:8px;">✓ Configuré</span>
        <?php else : ?>
          <span style="font-size:12px;font-weight:400;color:#f59e0b;margin-left:8px;">⚠ Non configuré</span>
        <?php endif; ?>
      </h2>

      <p class="praximet-field-desc" style="margin-bottom:20px;">
        Configurez votre serveur SMTP pour éviter que les emails arrivent en spam.
        Pour OVH, utilisez les valeurs pré-remplies ci-dessous.
      </p>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

        <div class="praximet-field">
          <label for="praximet_smtp_host">Hôte SMTP</label>
          <input type="text"
                 id="praximet_smtp_host"
                 name="praximet_smtp_host"
                 value="<?php echo esc_attr($smtp_host); ?>"
                 placeholder="ssl0.ovh.net"
                 class="praximet-input-large" />
          <p class="praximet-field-desc">OVH : ssl0.ovh.net</p>
        </div>

        <div class="praximet-field">
          <label for="praximet_smtp_port">Port</label>
          <input type="number"
                 id="praximet_smtp_port"
                 name="praximet_smtp_port"
                 value="<?php echo esc_attr($smtp_port); ?>"
                 placeholder="465"
                 class="praximet-input-small" />
          <p class="praximet-field-desc">465 (SSL) ou 587 (TLS)</p>
        </div>

        <div class="praximet-field">
          <label for="praximet_smtp_secure">Chiffrement</label>
          <select id="praximet_smtp_secure" name="praximet_smtp_secure" class="praximet-input-large">
            <option value="ssl"  <?php selected($smtp_secure, 'ssl'); ?>>SSL (port 465)</option>
            <option value="tls"  <?php selected($smtp_secure, 'tls'); ?>>TLS (port 587)</option>
            <option value=""     <?php selected($smtp_secure, ''); ?>>Aucun</option>
          </select>
        </div>

        <div class="praximet-field">
          <label for="praximet_smtp_from">Adresse expéditrice</label>
          <input type="email"
                 id="praximet_smtp_from"
                 name="praximet_smtp_from"
                 value="<?php echo esc_attr($smtp_from); ?>"
                 placeholder="contact@praxis-accompagnement.com"
                 class="praximet-input-large" />
        </div>

        <div class="praximet-field">
          <label for="praximet_smtp_user">Identifiant SMTP (email)</label>
          <input type="email"
                 id="praximet_smtp_user"
                 name="praximet_smtp_user"
                 value="<?php echo esc_attr($smtp_user); ?>"
                 placeholder="contact@praxis-accompagnement.com"
                 class="praximet-input-large" />
        </div>

        <div class="praximet-field">
          <label for="praximet_smtp_pass">
            Mot de passe SMTP
            <?php if ( $smtp_configured ) : ?>
              <span style="font-size:11px;color:#64748b;">(laisser vide pour conserver l'actuel)</span>
            <?php endif; ?>
          </label>
          <input type="password"
                 id="praximet_smtp_pass"
                 name="praximet_smtp_pass"
                 value=""
                 placeholder="<?php echo $smtp_configured ? '••••••••' : 'Mot de passe de votre boîte OVH'; ?>"
                 autocomplete="new-password"
                 class="praximet-input-large" />
        </div>

      </div>

      <!-- Test SMTP -->
      <div class="praximet-field" style="margin-top:20px;padding-top:20px;border-top:1px solid #e2e8f0;">
        <label>Tester la configuration</label>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
          <input type="email"
                 id="praximet-smtp-test-to"
                 placeholder="votre@email.fr"
                 value="<?php echo esc_attr( get_option('admin_email') ); ?>"
                 style="width:260px;" />
          <button type="button"
                  id="praximet-smtp-test-btn"
                  class="praximet-btn-secondary">
            Envoyer un email de test
          </button>
          <span id="praximet-smtp-test-result" style="font-size:13px;"></span>
        </div>
        <p class="praximet-field-desc">
          Enregistrez d'abord vos paramètres, puis envoyez un email de test pour vérifier que tout fonctionne.
        </p>
      </div>

      <!-- Guide DNS -->
      <details style="margin-top:20px;">
        <summary style="cursor:pointer;font-weight:600;font-size:13px;color:#1e3a5f;padding:8px 0;">
          📋 Guide — Enregistrements DNS anti-spam (SPF / DMARC)
        </summary>
        <div style="margin-top:12px;padding:16px;background:#f8fafc;border-radius:8px;font-size:12px;line-height:1.8;">
          <p style="margin:0 0 10px;"><strong>À ajouter dans votre Zone DNS OVH :</strong></p>

          <table style="width:100%;border-collapse:collapse;font-size:12px;">
            <thead>
              <tr style="background:#e8f0fb;">
                <th style="padding:8px;text-align:left;border:1px solid #c8d8ec;">Type</th>
                <th style="padding:8px;text-align:left;border:1px solid #c8d8ec;">Sous-domaine</th>
                <th style="padding:8px;text-align:left;border:1px solid #c8d8ec;">Valeur</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td style="padding:8px;border:1px solid #e2e8f0;">TXT</td>
                <td style="padding:8px;border:1px solid #e2e8f0;"><em>(vide)</em></td>
                <td style="padding:8px;border:1px solid #e2e8f0;font-family:monospace;font-size:11px;">v=spf1 include:mx.ovh.com ~all</td>
              </tr>
              <tr style="background:#fafbfd;">
                <td style="padding:8px;border:1px solid #e2e8f0;">TXT</td>
                <td style="padding:8px;border:1px solid #e2e8f0;font-family:monospace;">_dmarc</td>
                <td style="padding:8px;border:1px solid #e2e8f0;font-family:monospace;font-size:11px;">v=DMARC1; p=none; rua=mailto:contact@praxis-accompagnement.com</td>
              </tr>
            </tbody>
          </table>

          <p style="margin:12px 0 0;color:#64748b;">
            Le DKIM se configure directement depuis votre espace client OVH → Web Cloud → Emails → votre domaine → Informations générales → Configurer DKIM.
          </p>
        </div>
      </details>

    </div>

    <!-- ── Shortcode ────────────────────────────────────────────────── -->
    <div class="praximet-card">
      <h2 class="praximet-card-title">🔧 Utilisation</h2>
      <p>
        Ajoutez le shortcode suivant sur n'importe quelle page WordPress
        pour afficher le quiz RIASEC :
      </p>
      <div class="praximet-shortcode-box">
        <code>[praximet_quiz]</code>
        <button type="button"
                class="praximet-btn-link praximet-copy-btn"
                data-copy="[praximet_quiz]">
          Copier
        </button>
      </div>
    </div>

    <div class="praximet-settings-footer">
      <button type="submit" class="praximet-btn-primary praximet-btn--large">
        Enregistrer les paramètres
      </button>
    </div>

  </form>

</div><!-- .wrap -->

<script>
document.getElementById('praximet-smtp-test-btn').addEventListener('click', function() {
    var btn    = this;
    var to     = document.getElementById('praximet-smtp-test-to').value;
    var result = document.getElementById('praximet-smtp-test-result');

    btn.disabled    = true;
    btn.textContent = 'Envoi en cours…';
    result.textContent = '';
    result.style.color = '';

    var body = new URLSearchParams({
        action: 'praximet_test_smtp',
        nonce:  '<?php echo wp_create_nonce('praximet_admin_nonce'); ?>',
        to:     to,
    });

    fetch( '<?php echo admin_url('admin-ajax.php'); ?>', {
        method:  'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body:    body.toString(),
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        btn.disabled    = false;
        btn.textContent = 'Envoyer un email de test';
        if ( data.success ) {
            result.textContent = '✓ ' + data.data.message;
            result.style.color = '#16a34a';
        } else {
            result.textContent = '✗ ' + (data.data ? data.data.message : 'Erreur inconnue');
            result.style.color = '#dc2626';
        }
    })
    .catch(function() {
        btn.disabled    = false;
        btn.textContent = 'Envoyer un email de test';
        result.textContent = '✗ Impossible de contacter le serveur.';
        result.style.color = '#dc2626';
    });
});
</script>
