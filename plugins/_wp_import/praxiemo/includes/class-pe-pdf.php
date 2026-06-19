<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Génère un rapport PDF imprimable pour les résultats du test PraxiEmo.
 * Technique : HTML stylisé avec @media print — l'utilisateur imprime / enregistre en PDF.
 * Compatible OVH mutualisé (aucune dépendance externe).
 */
class PE_PDF {

    /**
     * Endpoint front-end : /?pemo_rapport=1&token=xxx&nonce=yyy
     * Évite complètement /wp-admin/ — compatible OVH mutualisé.
     */
    public static function init() {
        // Déclarer la query var
        add_filter( 'query_vars', array( __CLASS__, 'register_query_var' ) );
        // Intercepter la requête avant que WordPress charge un template
        add_action( 'template_redirect', array( __CLASS__, 'maybe_handle' ) );
    }

    public static function register_query_var( $vars ) {
        $vars[] = 'pemo_rapport';
        return $vars;
    }

    public static function maybe_handle() {
        if ( get_query_var( 'pemo_rapport' ) !== '1' ) return;
        self::handle();
        exit;
    }

    /**
     * Génère l'URL du rapport pour un token donné.
     */
    public static function get_url( $token, $nonce ) {
        return add_query_arg( array(
            'pemo_rapport' => '1',
            'token'        => $token,
            'nonce'        => $nonce,
        ), home_url( '/' ) );
    }

    public static function handle() {
        // Vérification du nonce passé en GET
        $nonce = sanitize_text_field( wp_unslash( $_GET['nonce'] ?? '' ) );
        if ( ! wp_verify_nonce( $nonce, 'pemo_nonce' ) ) {
            wp_die( 'Lien expiré ou invalide. Veuillez retourner sur la page du test.', 403 );
        }

        $token = sanitize_text_field( wp_unslash( $_GET['token'] ?? '' ) );
        if ( empty( $token ) ) {
            wp_die( 'Token manquant.', 400 );
        }

        $session = PE_DB::get_session_by_token( $token );
        if ( ! $session || ! $session->completed_at ) {
            wp_die( 'Session introuvable ou test non complété.', 404 );
        }

        $result_row = PE_DB::get_results_by_session( $session->id );
        if ( ! $result_row ) {
            wp_die(
                '<p>Les résultats de ce test n\'ont pas pu être sauvegardés (mise à jour récente du plugin).</p>'
                . '<p>Merci de <strong>refaire le test</strong> — cela prend 10 minutes et vos nouvelles réponses seront correctement enregistrées.</p>'
                . '<p><a href="' . esc_url( home_url( '/' ) ) . '">&larr; Retour au site</a></p>',
                'Résultats introuvables'
            );
        }

        // Reconstituer $results depuis la ligne DB
        $dim_scores = array();
        for ( $i = 1; $i <= 16; $i++ ) {
            $col                = 'dim_' . $i;
            $dim_scores[ $i ]   = intval( $result_row->$col ?? 0 );
        }
        $score_global = intval( $result_row->score_global );

        // Recalculer top_forces et top_dev
        $sorted = $dim_scores;
        arsort( $sorted );
        $sorted_ids = array_keys( $sorted );
        $top_forces = array_slice( $sorted_ids, 0, 3 );
        $top_dev    = array();
        foreach ( array_reverse( $sorted_ids ) as $dim_id ) {
            if ( count( $top_dev ) >= 3 ) break;
            if ( $dim_scores[ $dim_id ] <= 12 ) $top_dev[] = $dim_id;
        }

        list( $niveau_qe, $phrase_qe ) = self::interpret_global( $score_global );

        $results = array(
            'dim_scores'   => $dim_scores,
            'score_global' => $score_global,
            'top_forces'   => $top_forces,
            'top_dev'      => $top_dev,
            'niveau_qe'    => $niveau_qe,
            'phrase_qe'    => $phrase_qe,
        );

        self::render( $session, $results );
        exit;
    }

    // Interprétation globale (cohérente avec class-pe-calculator.php)
    private static function interpret_global( $score ) {
        if ( $score <= 120 ) return array( 'QE Faible',     'Votre intelligence émotionnelle est en construction. C\'est une excellente base pour commencer un travail sur vous.' );
        if ( $score <= 200 ) return array( 'QE Modéré',     'Vous disposez de vraies ressources émotionnelles. Quelques zones méritent d\'être renforcées pour libérer votre plein potentiel.' );
        if ( $score <= 280 ) return array( 'QE Élevé',      'Votre intelligence émotionnelle est un vrai atout. Vous gérez bien vos émotions et savez créer des relations de qualité.' );
        return array( 'QE Très élevé', 'Vous faites partie des profils à haute intelligence émotionnelle. Votre capacité à comprendre et réguler vos émotions est remarquable.' );
    }

    private static function render( $session, $results ) {
        $dims    = PE_Calculator::get_dimensions();
        $familles = PE_Calculator::get_familles();
        $c1      = get_option( 'pemo_color_primary', '#E8541A' );
        if ( ! preg_match( '/^#[0-9A-Fa-f]{3,6}$/', $c1 ) ) $c1 = '#E8541A';
        $c2      = '#1E2A3A';
        $rdv_url = get_option( 'pemo_rdv_url', home_url( '/contact' ) );
        $site    = get_option( 'pemo_site_name', '' ) ?: get_bloginfo( 'name' );
        $date    = wp_date( 'd/m/Y', strtotime( $session->completed_at ) );

        $fam_colors = array(
            1 => $c1,
            2 => '#F59E0B',
            3 => '#3B82F6',
            4 => '#16A34A',
        );

        $score_global = intval( $results['score_global'] );
        $niveau_qe    = esc_html( $results['niveau_qe'] );
        $phrase_qe    = esc_html( $results['phrase_qe'] );
        $prenom       = esc_html( $session->prenom );

        header( 'Content-Type: text/html; charset=UTF-8' );
        header( 'X-Robots-Tag: noindex' );
        ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Rapport IE — <?php echo $prenom; ?> — <?php echo $date; ?></title>
  <style>
    /* ── Reset & base ─────────────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { font-size: 13px; }
    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      color: #1E293B;
      background: #f8fafc;
      padding: 0;
    }

    /* ── Page wrapper ─────────────────────────────────────── */
    .page {
      max-width: 800px;
      margin: 0 auto;
      background: #fff;
    }

    /* ── Header ───────────────────────────────────────────── */
    .pdf-header {
      background: linear-gradient(135deg, <?php echo $c1; ?> 0%, <?php echo $c2; ?> 100%);
      color: #fff;
      padding: 40px 40px 32px;
      text-align: center;
    }
    .pdf-header .eyebrow {
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: .12em;
      opacity: .7;
      margin-bottom: 6px;
    }
    .pdf-header h1 {
      font-size: 26px;
      font-weight: 800;
      margin-bottom: 16px;
    }
    .pdf-header .score-wrap {
      display: inline-block;
      background: rgba(255,255,255,.15);
      border: 1px solid rgba(255,255,255,.3);
      border-radius: 16px;
      padding: 16px 32px;
      margin-bottom: 14px;
    }
    .pdf-header .score-big {
      font-size: 52px;
      font-weight: 900;
      line-height: 1;
    }
    .pdf-header .score-sub { font-size: 18px; opacity: .6; }
    .pdf-header .qe-badge {
      display: inline-block;
      background: rgba(255,255,255,.2);
      border: 1px solid rgba(255,255,255,.35);
      border-radius: 999px;
      padding: 5px 18px;
      font-size: 13px;
      font-weight: 700;
      margin: 8px 0 12px;
    }
    .pdf-header .phrase {
      font-size: 13px;
      opacity: .85;
      line-height: 1.6;
      max-width: 480px;
      margin: 0 auto;
    }

    /* ── Meta bar ─────────────────────────────────────────── */
    .meta-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 40px;
      background: #f1f5f9;
      border-bottom: 1px solid #e2e8f0;
      font-size: 11px;
      color: #64748B;
    }
    .meta-bar strong { color: #334155; }

    /* ── Body ─────────────────────────────────────────────── */
    .pdf-body { padding: 32px 40px; }

    /* ── Section title ────────────────────────────────────── */
    .section-title {
      font-size: 13px;
      font-weight: 700;
      color: <?php echo $c1; ?>;
      text-transform: uppercase;
      letter-spacing: .08em;
      border-bottom: 2px solid <?php echo $c1; ?>;
      padding-bottom: 6px;
      margin: 28px 0 16px;
    }
    .section-title:first-child { margin-top: 0; }

    /* ── Famille label ────────────────────────────────────── */
    .fam-label {
      font-size: 11px;
      font-weight: 700;
      color: #64748B;
      text-transform: uppercase;
      letter-spacing: .07em;
      margin: 20px 0 10px;
    }

    /* ── Dimension row ────────────────────────────────────── */
    .dim-row { margin-bottom: 14px; }
    .dim-header {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      margin-bottom: 5px;
    }
    .dim-label { font-size: 13px; font-weight: 600; color: #334155; }
    .dim-meta  { font-size: 12px; color: #64748B; }
    .dim-score-val { font-size: 13px; font-weight: 700; }
    .bar-track {
      background: #e2e8f0;
      border-radius: 999px;
      height: 8px;
      overflow: hidden;
    }
    .bar-fill {
      height: 8px;
      border-radius: 999px;
    }
    .dim-interp {
      font-size: 11px;
      color: #94A3B8;
      margin-top: 2px;
    }
    /* Descriptions masquées en PDF pour alléger */
    .dim-desc {
      font-size: 11px;
      color: #64748B;
      line-height: 1.5;
      margin-top: 4px;
    }

    /* ── Recommandations ──────────────────────────────────── */
    .reco-card {
      border-left: 3px solid;
      border-radius: 0 8px 8px 0;
      padding: 12px 16px;
      margin-bottom: 14px;
    }
    .reco-dim-label {
      font-size: 12px;
      font-weight: 700;
      margin-bottom: 8px;
    }
    .reco-actions { padding-left: 0; list-style: none; }
    .reco-actions li {
      font-size: 12px;
      line-height: 1.55;
      color: #334155;
      padding-left: 16px;
      position: relative;
      margin-bottom: 5px;
    }
    .reco-actions li::before {
      content: '→';
      position: absolute;
      left: 0;
      color: inherit;
      font-weight: 700;
    }

    /* ── Highlights ───────────────────────────────────────── */
    .highlights { display: flex; gap: 16px; margin-bottom: 20px; }
    .highlight-card {
      flex: 1;
      border-radius: 10px;
      padding: 16px;
    }
    .highlight-card.forces {
      background: #F0FDF4;
      border: 1px solid #BBF7D0;
    }
    .highlight-card.dev {
      background: #EFF6FF;
      border: 1px solid #BFDBFE;
    }
    .highlight-card h4 {
      font-size: 12px;
      font-weight: 700;
      margin-bottom: 8px;
    }
    .highlight-card.forces h4 { color: #15803D; }
    .highlight-card.dev h4 { color: #1D4ED8; }
    .highlight-card ul { list-style: none; padding: 0; }
    .highlight-card li {
      font-size: 12px;
      color: #1E293B;
      padding: 2px 0;
    }
    .highlight-card li::before { content: '• '; }

    /* ── CTA block ────────────────────────────────────────── */
    .cta-block {
      background: linear-gradient(135deg, <?php echo $c1; ?>18 0%, <?php echo $c2; ?>12 100%);
      border: 1px solid <?php echo $c1; ?>44;
      border-radius: 12px;
      padding: 24px;
      text-align: center;
      margin-top: 28px;
    }
    .cta-block h3 {
      font-size: 15px;
      font-weight: 800;
      color: <?php echo $c1; ?>;
      margin-bottom: 8px;
    }
    .cta-block p {
      font-size: 12px;
      color: #475569;
      line-height: 1.6;
      margin-bottom: 14px;
    }
    .cta-btn {
      display: inline-block;
      background: linear-gradient(135deg, <?php echo $c1; ?>, <?php echo $c2; ?>);
      color: #fff;
      text-decoration: none;
      padding: 11px 28px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 700;
    }

    /* ── Footer ───────────────────────────────────────────── */
    .pdf-footer {
      text-align: center;
      padding: 16px 40px;
      background: #f8fafc;
      border-top: 1px solid #e2e8f0;
      font-size: 11px;
      color: #94A3B8;
    }

    /* ── Print styles ─────────────────────────────────────── */
    @media print {
      body { background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .no-print { display: none !important; }
      .page { max-width: 100%; }
      a { color: inherit !important; }
      .cta-block, .highlights { break-inside: avoid; }
      .dim-row { break-inside: avoid; }
      .reco-card { break-inside: avoid; }
    }

    /* ── Screen-only toolbar ──────────────────────────────── */
    .toolbar {
      position: fixed;
      top: 0; left: 0; right: 0;
      background: <?php echo $c2; ?>;
      color: #fff;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 24px;
      z-index: 9999;
      box-shadow: 0 2px 12px rgba(0,0,0,.2);
    }
    .toolbar .tbar-title { font-size: 13px; font-weight: 600; opacity: .85; }
    .toolbar .tbar-actions { display: flex; gap: 10px; }
    .toolbar button {
      background: <?php echo $c1; ?>;
      color: #fff;
      border: none;
      border-radius: 999px;
      padding: 7px 20px;
      font-size: 12px;
      font-weight: 700;
      cursor: pointer;
    }
    .toolbar button:hover { opacity: .88; }
    .toolbar .btn-close {
      background: transparent;
      border: 1px solid rgba(255,255,255,.4);
    }
    @media print { .toolbar { display: none !important; } }
    body.has-toolbar { padding-top: 50px; }
  </style>
</head>
<body class="has-toolbar">

<!-- Toolbar (masquée à l'impression) -->
<div class="toolbar no-print">
  <span class="tbar-title">📄 Rapport Intelligence Émotionnelle — <?php echo $prenom; ?></span>
  <div class="tbar-actions">
    <button onclick="window.print()">🖨 Enregistrer en PDF</button>
    <button class="btn-close" onclick="window.close()">✕ Fermer</button>
  </div>
</div>

<div class="page">

  <!-- HEADER -->
  <div class="pdf-header">
    <p class="eyebrow">Rapport Intelligence Émotionnelle</p>
    <h1>Profil de <?php echo $prenom; ?></h1>
    <div class="score-wrap">
      <div class="score-big"><?php echo $score_global; ?><span class="score-sub"> / 320</span></div>
    </div>
    <div class="qe-badge"><?php echo $niveau_qe; ?></div>
    <p class="phrase"><?php echo $phrase_qe; ?></p>
  </div>

  <!-- META BAR -->
  <div class="meta-bar">
    <span>Test réalisé le <strong><?php echo $date; ?></strong></span>
    <span>16 dimensions · 80 questions · Modèle Bar-On / Goleman</span>
    <span><?php echo esc_html( $site ); ?></span>
  </div>

  <div class="pdf-body">

    <!-- Points forts & Axes -->
    <div class="section-title">Synthèse</div>
    <div class="highlights">
      <div class="highlight-card forces">
        <h4>🏆 Points forts</h4>
        <ul>
          <?php foreach ( $results['top_forces'] as $dim_id ) : ?>
          <li><?php echo esc_html( $dims[ $dim_id ]['label'] ?? '' ); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="highlight-card dev">
        <h4>🎯 Axes de progression</h4>
        <ul>
          <?php if ( ! empty( $results['top_dev'] ) ) : ?>
            <?php foreach ( $results['top_dev'] as $dim_id ) : ?>
            <li><?php echo esc_html( $dims[ $dim_id ]['label'] ?? '' ); ?></li>
            <?php endforeach; ?>
          <?php else : ?>
            <li>Toutes vos dimensions sont bien développées — félicitations !</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <!-- Profil complet par famille -->
    <div class="section-title">Vos 16 dimensions</div>

    <?php foreach ( $familles as $fam_id => $fam ) :
        $bar_color = $fam_colors[ $fam_id ] ?? $c1;
    ?>
      <p class="fam-label"><?php echo esc_html( $fam['emoji'] . '  ' . $fam['label'] ); ?></p>

      <?php foreach ( $dims as $dim_id => $dim ) :
          if ( $dim['famille'] !== $fam_id ) continue;
          $score = intval( $results['dim_scores'][ $dim_id ] ?? 0 );
          $pct   = round( ( max( 0, $score - 5 ) / 15 ) * 100 );
          $interp = PE_Calculator::interpret_dim( $score );
      ?>
      <div class="dim-row">
        <div class="dim-header">
          <span class="dim-label"><?php echo esc_html( $dim['label'] ); ?></span>
          <span class="dim-meta">
            <span class="dim-score-val" style="color:<?php echo $bar_color; ?>;"><?php echo $score; ?>/20</span>
            &nbsp;—&nbsp;<span class="dim-interp"><?php echo esc_html( $interp ); ?></span>
          </span>
        </div>
        <div class="bar-track">
          <div class="bar-fill" style="width:<?php echo $pct; ?>%;background:<?php echo $bar_color; ?>;"></div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endforeach; ?>

    <!-- Recommandations par dimension -->
    <?php if ( ! empty( $results['dim_scores'] ) ) : ?>
    <div class="section-title">Recommandations personnalisées</div>

    <?php
    // Afficher les recommandations pour toutes les dimensions (ordre famille)
    foreach ( $familles as $fam_id => $fam ) :
        $bar_color = $fam_colors[ $fam_id ] ?? $c1;
    ?>
      <p class="fam-label"><?php echo esc_html( $fam['emoji'] . '  ' . $fam['label'] ); ?></p>

      <?php foreach ( $dims as $dim_id => $dim ) :
          if ( $dim['famille'] !== $fam_id ) continue;
          $score = intval( $results['dim_scores'][ $dim_id ] ?? 0 );
          $reco  = PE_Calculator::get_recommendations( $dim_id, $score );
          if ( empty( $reco['actions'] ) ) continue;
          $bg_map = array(
              'Zone de développement prioritaire' => '#FEF2F2',
              'Compétence en construction'        => '#FFFBEB',
              'Compétence développée'             => '#EFF6FF',
              'Point fort'                        => '#F0FDF4',
          );
          $border_map = array(
              'Zone de développement prioritaire' => '#FCA5A5',
              'Compétence en construction'        => '#FCD34D',
              'Compétence développée'             => '#93C5FD',
              'Point fort'                        => '#86EFAC',
          );
          $bg     = $bg_map[ $reco['niveau'] ]     ?? '#F8FAFC';
          $border = $border_map[ $reco['niveau'] ] ?? $bar_color;
      ?>
      <div class="reco-card" style="background:<?php echo $bg; ?>;border-color:<?php echo $border; ?>;">
        <p class="reco-dim-label" style="color:<?php echo $bar_color; ?>;">
          <?php echo esc_html( $dim['label'] ); ?>
          <span style="font-weight:400;color:#64748B;font-size:11px;"> — <?php echo esc_html( $reco['niveau'] ); ?></span>
        </p>
        <ul class="reco-actions" style="color:<?php echo $border; ?>;">
          <?php foreach ( $reco['actions'] as $action ) : ?>
          <li style="color:#334155;"><?php echo esc_html( $action ); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endforeach; ?>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- CTA -->
    <div class="cta-block">
      <h3>Transformez ce profil en levier de développement</h3>
      <p>Un entretien de débriefing avec Alexandre vous permettra de comprendre ce que révèlent réellement vos scores et comment les transformer en changements durables et concrets.</p>
      <a href="<?php echo esc_url( $rdv_url ); ?>" class="cta-btn" target="_blank" rel="noopener">
        📅 Réserver mon entretien gratuit →
      </a>
    </div>

  </div><!-- /.pdf-body -->

  <div class="pdf-footer">
    <?php echo esc_html( $site ); ?> — Praxis Accompagnement &nbsp;|&nbsp;
    Test réalisé le <?php echo $date; ?> &nbsp;|&nbsp;
    Modèle inspiré Bar-On EQ-i &amp; Goleman &nbsp;|&nbsp;
    <a href="<?php echo PE_Shortcode::get_privacy_url(); ?>" style="color:#94A3B8;" target="_blank" rel="noopener noreferrer">Politique de confidentialité</a>
  </div>

</div><!-- /.page -->

<script>
  // Auto-ouvrir la boîte d'impression au chargement (pratique sur mobile)
  // On attend que les styles soient bien chargés
  window.addEventListener('load', function() {
    // On ne déclenche pas automatiquement — l'utilisateur clique sur le bouton
    // pour un meilleur contrôle du rendu
  });
</script>
</body>
</html>
        <?php
    }
}
