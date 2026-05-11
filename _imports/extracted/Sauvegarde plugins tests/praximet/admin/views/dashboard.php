<?php
/**
 * PraxiMet – Vue Dashboard : liste des leads
 */
if ( ! defined( 'ABSPATH' ) ) exit;

require_once PRAXIMET_PATH . 'includes/class-lead-manager.php';

// ── Filtres ───────────────────────────────────────────────────────────
$filtre_statut = sanitize_key( $_GET['statut'] ?? '' );
$filtre_search = sanitize_text_field( $_GET['s'] ?? '' );
$page_num      = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
$per_page      = 20;
$offset        = ( $page_num - 1 ) * $per_page;

$args = [
    'limit'  => $per_page,
    'offset' => $offset,
];
if ( $filtre_statut ) $args['statut'] = $filtre_statut;
if ( $filtre_search ) $args['search'] = $filtre_search;

$leads = PraxiMet_Lead_Manager::get_leads( $args );

// Compteurs par statut
global $wpdb;
$table   = $wpdb->prefix . 'praximet_leads';
$counts  = $wpdb->get_results(
    "SELECT statut, COUNT(*) as total FROM {$table} GROUP BY statut",
    ARRAY_A
);
$total_par_statut = array_column( $counts, 'total', 'statut' );
$total_global     = array_sum( $total_par_statut );

// Leads du mois
$leads_mois = (int) $wpdb->get_var(
    "SELECT COUNT(*) FROM {$table}
     WHERE MONTH(created_at) = MONTH(NOW())
     AND YEAR(created_at) = YEAR(NOW())"
);
$convertis = (int) ( $total_par_statut['converti'] ?? 0 );
$taux      = $total_global > 0 ? round( ( $convertis / $total_global ) * 100 ) : 0;
?>

<div class="wrap praximet-wrap">

  <div class="praximet-admin-header">
    <h1 class="praximet-admin-title">
      <span class="praximet-logo">P</span> PraxiMet — Leads
    </h1>
    <a href="<?php echo esc_url( admin_url('admin.php?page=praximet-settings') ); ?>"
       class="praximet-btn-secondary">⚙ Paramètres</a>
  </div>

  <!-- ── KPI ──────────────────────────────────────────────────────── -->
  <div class="praximet-kpi-row">
    <div class="praximet-kpi">
      <span class="praximet-kpi-number"><?php echo $total_global; ?></span>
      <span class="praximet-kpi-label">Total leads</span>
    </div>
    <div class="praximet-kpi">
      <span class="praximet-kpi-number"><?php echo $leads_mois; ?></span>
      <span class="praximet-kpi-label">Ce mois-ci</span>
    </div>
    <div class="praximet-kpi">
      <span class="praximet-kpi-number"><?php echo (int)($total_par_statut['rdv_pris'] ?? 0); ?></span>
      <span class="praximet-kpi-label">RDV pris</span>
    </div>
    <div class="praximet-kpi">
      <span class="praximet-kpi-number"><?php echo $taux; ?>%</span>
      <span class="praximet-kpi-label">Taux conversion</span>
    </div>
  </div>

  <!-- ── Filtres ───────────────────────────────────────────────────── -->
  <div class="praximet-filters">

    <div class="praximet-filter-statuts">
      <?php
      $statuts = [
          ''          => 'Tous (' . $total_global . ')',
          'nouveau'   => 'Nouveaux ('   . ($total_par_statut['nouveau']  ?? 0) . ')',
          'contacte'  => 'Contactés ('  . ($total_par_statut['contacte'] ?? 0) . ')',
          'rdv_pris'  => 'RDV pris ('   . ($total_par_statut['rdv_pris'] ?? 0) . ')',
          'converti'  => 'Convertis ('  . ($total_par_statut['converti'] ?? 0) . ')',
          'archive'   => 'Archivés ('   . ($total_par_statut['archive']  ?? 0) . ')',
      ];
      foreach ( $statuts as $val => $label ) :
          $active = $filtre_statut === $val ? 'class="praximet-filter-active"' : '';
          $url    = add_query_arg( [ 'page' => 'praximet-leads', 'statut' => $val, 'paged' => 1 ], admin_url('admin.php') );
      ?>
      <a href="<?php echo esc_url($url); ?>" <?php echo $active; ?>>
        <?php echo esc_html($label); ?>
      </a>
      <?php endforeach; ?>
    </div>

    <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>"
          class="praximet-search-form">
      <input type="hidden" name="page" value="praximet-leads">
      <input type="text" name="s" value="<?php echo esc_attr($filtre_search); ?>"
             placeholder="Rechercher un lead…" class="praximet-search-input">
      <button type="submit" class="praximet-btn-primary">Rechercher</button>
    </form>

  </div>

  <!-- ── Tableau des leads ─────────────────────────────────────────── -->
  <?php if ( empty($leads) ) : ?>
    <div class="praximet-empty">
      <p>Aucun lead trouvé.<?php echo $filtre_search ? ' Essayez un autre terme de recherche.' : ''; ?></p>
    </div>
  <?php else : ?>

  <div class="praximet-table-wrap">
    <table class="praximet-table">
      <thead>
        <tr>
          <th>Candidat</th>
          <th>Contact</th>
          <th>Code RIASEC</th>
          <th>Statut</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $leads as $lead ) :
          $badge = PraxiMet_Admin::statut_badge( $lead['statut'] );
          $url_fiche = admin_url('admin.php?page=praximet-leads&id=' . $lead['id']);
        ?>
        <tr data-lead-id="<?php echo (int)$lead['id']; ?>">

          <td>
            <a href="<?php echo esc_url($url_fiche); ?>" class="praximet-lead-name">
              <?php echo esc_html( $lead['prenom'] . ' ' . $lead['nom'] ); ?>
            </a>
          </td>

          <td>
            <a href="mailto:<?php echo esc_attr($lead['email']); ?>">
              <?php echo esc_html($lead['email']); ?>
            </a>

          </td>

          <td>
            <span class="praximet-code-badge">
              <?php echo esc_html($lead['code_riasec']); ?>
            </span>
          </td>

          <td>
            <select class="praximet-statut-select"
                    data-lead-id="<?php echo (int)$lead['id']; ?>"
                    data-nonce="<?php echo esc_attr( wp_create_nonce('praximet_admin_nonce') ); ?>">
              <?php foreach ( ['nouveau','contacte','rdv_pris','converti','archive'] as $s ) : ?>
              <option value="<?php echo $s; ?>"
                      <?php selected( $lead['statut'], $s ); ?>>
                <?php echo esc_html( PraxiMet_Admin::statut_badge($s)['label'] ); ?>
              </option>
              <?php endforeach; ?>
            </select>
          </td>

          <td>
            <span class="praximet-date">
              <?php echo esc_html( date_i18n( 'd/m/Y', strtotime($lead['created_at']) ) ); ?>
            </span>
            <?php if ( $lead['relance_envoyee'] ) : ?>
            <br><span class="praximet-tag praximet-tag--relance">✉ relancé</span>
            <?php endif; ?>
          </td>

          <td>
            <a href="<?php echo esc_url($url_fiche); ?>"
               class="praximet-btn-link">Voir →</a>
          </td>

        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php endif; ?>

  <!-- ── Export CSV ────────────────────────────────────────────────── -->
  <div class="praximet-footer-actions">
    <form method="post"
          action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
      <input type="hidden" name="action" value="praximet_export_csv">
      <?php wp_nonce_field('praximet_export_csv'); ?>
      <button type="submit" class="praximet-btn-secondary">
        ⬇ Exporter en CSV
      </button>
    </form>
  </div>

</div><!-- .wrap -->
