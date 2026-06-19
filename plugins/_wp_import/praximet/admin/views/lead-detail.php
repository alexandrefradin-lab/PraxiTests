<?php
/**
 * PraxiMet – Vue fiche lead individuelle
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$lead_id = (int) $_GET['id'];
$table   = $wpdb->prefix . 'praximet_leads';
$lead    = $wpdb->get_row(
    $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $lead_id ),
    ARRAY_A
);

if ( ! $lead ) {
    echo '<div class="wrap"><p>Lead introuvable.</p></div>';
    return;
}

require_once PRAXIMET_PATH . 'includes/class-riasec-engine.php';
require_once PRAXIMET_PATH . 'includes/class-cron-manager.php';

$code    = $lead['code_riasec'];
$scores  = [
    'R' => (int) $lead['score_r'],
    'I' => (int) $lead['score_i'],
    'A' => (int) $lead['score_a'],
    'S' => (int) $lead['score_s'],
    'E' => (int) $lead['score_e'],
    'C' => (int) $lead['score_c'],
];
$resultat       = PraxiMet_Riasec_Engine::get_resultat_complet( $code );
$badge          = PraxiMet_Admin::statut_badge( $lead['statut'] );
$relance_planif = PraxiMet_Cron_Manager::relance_planifiee( $lead_id );
$updated        = isset( $_GET['updated'] );

$libelles = ['R'=>'Réaliste','I'=>'Investigateur','A'=>'Artistique',
             'S'=>'Social','E'=>'Entrepreneur','C'=>'Conventionnel'];
?>

<div class="wrap praximet-wrap">

  <div class="praximet-admin-header">
    <h1 class="praximet-admin-title">
      <span class="praximet-logo">P</span>
      <?php echo esc_html( $lead['prenom'] . ' ' . $lead['nom'] ); ?>
    </h1>
    <a href="<?php echo esc_url( admin_url('admin.php?page=praximet-leads') ); ?>"
       class="praximet-btn-secondary">← Retour aux leads</a>

    <form method="post"
          action="<?php echo esc_url( admin_url('admin-post.php') ); ?>"
          style="display:inline;">
        <input type="hidden" name="action"  value="praximet_admin_export_pdf">
        <input type="hidden" name="lead_id" value="<?php echo $lead_id; ?>">
        <?php wp_nonce_field('praximet_admin_export_pdf'); ?>
        <button type="submit" class="praximet-btn-secondary">
            ⬇ Rapport PDF
        </button>
    </form>

    <form method="post"
          action="<?php echo esc_url( admin_url('admin-post.php') ); ?>"
          style="display:inline;"
          onsubmit="return confirm('Supprimer définitivement ce lead ? Cette action est irréversible.');">
        <input type="hidden" name="action"  value="praximet_supprimer_lead">
        <input type="hidden" name="lead_id" value="<?php echo $lead_id; ?>">
        <?php wp_nonce_field('praximet_supprimer_lead'); ?>
        <button type="submit" class="praximet-btn-danger">
            🗑 Supprimer
        </button>
    </form>
  </div>

  <?php if ( $updated ) : ?>
  <div class="praximet-notice praximet-notice--success">
    ✓ Statut mis à jour avec succès.
  </div>
  <?php endif; ?>

  <div class="praximet-fiche-grid">

    <!-- ── Colonne gauche : coordonnées + statut ────────────────────── -->
    <div class="praximet-fiche-col">

      <div class="praximet-card">
        <h2 class="praximet-card-title">Coordonnées</h2>
        <table class="praximet-info-table">
          <tr>
            <th>Prénom</th>
            <td><?php echo esc_html($lead['prenom']); ?></td>
          </tr>
          <tr>
            <th>Nom</th>
            <td><?php echo esc_html($lead['nom']); ?></td>
          </tr>
          <tr>
            <th>Email</th>
            <td>
              <a href="mailto:<?php echo esc_attr($lead['email']); ?>">
                <?php echo esc_html($lead['email']); ?>
              </a>
            </td>
          </tr>

          <tr>
            <th>Source</th>
            <td>
              <?php if ( $lead['source_page'] ) : ?>
              <a href="<?php echo esc_url($lead['source_page']); ?>"
                 target="_blank" rel="noopener">
                <?php echo esc_html( parse_url($lead['source_page'], PHP_URL_PATH) ?: $lead['source_page'] ); ?>
              </a>
              <?php else : ?>
              <span class="praximet-empty-val">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <th>Inscrit le</th>
            <td><?php echo esc_html( date_i18n('d/m/Y à H\hi', strtotime($lead['created_at'])) ); ?></td>
          </tr>
          <tr>
            <th>RGPD</th>
            <td><span class="praximet-tag praximet-tag--ok">✓ Consentement recueilli</span></td>
          </tr>
        </table>
      </div>

      <!-- Statut -->
      <div class="praximet-card">
        <h2 class="praximet-card-title">Statut du lead</h2>
        <form method="post"
              action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
          <input type="hidden" name="action"  value="praximet_update_statut">
          <input type="hidden" name="lead_id" value="<?php echo $lead_id; ?>">
          <?php wp_nonce_field('praximet_update_statut'); ?>

          <div class="praximet-statut-form">
            <select name="statut" class="praximet-select-large">
              <?php foreach ( ['nouveau','contacte','rdv_pris','converti','archive'] as $s ) : ?>
              <option value="<?php echo $s; ?>" <?php selected($lead['statut'], $s); ?>>
                <?php echo esc_html( PraxiMet_Admin::statut_badge($s)['label'] ); ?>
              </option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="praximet-btn-primary">
              Mettre à jour
            </button>
          </div>
        </form>

        <div class="praximet-lead-meta">
          <p>
            RDV pris :
            <strong><?php echo $lead['rdv_pris'] ? '✓ Oui' : '✗ Non'; ?></strong>
          </p>
          <p>
            Relance :
            <?php if ( $lead['relance_envoyee'] ) : ?>
              <strong>✉ Envoyée</strong>
            <?php elseif ( $relance_planif ) : ?>
              <strong>⏳ Planifiée</strong>
            <?php else : ?>
              <strong>— Non planifiée</strong>
            <?php endif; ?>
          </p>
        </div>
      </div>

    </div><!-- /col gauche -->

    <!-- ── Colonne droite : profil RIASEC ───────────────────────────── -->
    <div class="praximet-fiche-col">

      <div class="praximet-card">
        <h2 class="praximet-card-title">Profil RIASEC</h2>

        <!-- Code dominant -->
        <div class="praximet-code-display">
          <?php foreach ( str_split($code) as $lettre ) : ?>
          <span class="praximet-lettre-admin"><?php echo esc_html($lettre); ?></span>
          <?php endforeach; ?>
        </div>

        <!-- Barres de scores -->
        <div class="praximet-scores">
          <?php foreach ( $scores as $lettre => $score ) :
            $pct   = min( 100, round( ($score / 14) * 100 ) );
            $label = $libelles[$lettre] ?? $lettre;
            $is_dominant = strpos($code, $lettre) !== false;
          ?>
          <div class="praximet-score-row <?php echo $is_dominant ? 'praximet-score-row--dominant' : ''; ?>">
            <div class="praximet-score-label">
              <strong><?php echo esc_html($lettre); ?></strong>
              <span><?php echo esc_html($label); ?></span>
            </div>
            <div class="praximet-score-bar-wrap">
              <div class="praximet-score-bar"
                   style="width:<?php echo $pct; ?>%"></div>
            </div>
            <span class="praximet-score-val"><?php echo $score; ?>/14</span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Descriptions des 3 types dominants -->
      <div class="praximet-card">
        <h2 class="praximet-card-title">Description du profil</h2>
        <?php foreach ( $resultat['profil'] as $index => $type ) :
          $rangs = ['Dominant','Secondaire','Tertiaire'];
        ?>
        <div class="praximet-type-desc-block">
          <p class="praximet-type-rang">
            <?php echo esc_html( $rangs[$index] ?? '' ); ?>
          </p>
          <h3 class="praximet-type-nom">
            <?php echo esc_html($type['label']); ?>
          </h3>
          <p class="praximet-type-desc">
            <?php echo esc_html($type['description']); ?>
          </p>
        </div>
        <?php endforeach; ?>
      </div>

    </div><!-- /col droite -->

  </div><!-- .praximet-fiche-grid -->

</div><!-- .wrap -->
