<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PE_Admin {

    public static function init() {
        add_action( 'admin_menu',      array( __CLASS__, 'register_menus' ) );
        add_action( 'admin_init',      array( __CLASS__, 'register_settings' ) );
        add_action( 'admin_head',      array( __CLASS__, 'admin_styles' ) );
        add_action( 'wp_ajax_pemo_test_smtp',         array( __CLASS__, 'handle_test_smtp' ) );
        add_action( 'wp_ajax_pemo_export_csv',        array( __CLASS__, 'handle_export_csv' ) );
        add_action( 'wp_ajax_pemo_delete_session',    array( __CLASS__, 'handle_delete_session' ) );
        add_action( 'wp_ajax_pemo_recreate_privacy',  array( __CLASS__, 'handle_recreate_privacy' ) );
    }

    public static function register_menus() {
        add_menu_page(
            'Praxis IE',
            'Praxis IE',
            'manage_options',
            'pemo-dashboard',
            array( __CLASS__, 'page_dashboard' ),
            'dashicons-smiley',
            30
        );
        add_submenu_page( 'pemo-dashboard', 'Résultats',   'Résultats',   'manage_options', 'pemo-resultats',   array( __CLASS__, 'page_resultats' ) );
        add_submenu_page( 'pemo-dashboard', 'Analytiques', 'Analytiques', 'manage_options', 'pemo-analytics',   array( __CLASS__, 'page_analytics' ) );
        add_submenu_page( 'pemo-dashboard', 'Réglages',    'Réglages',    'manage_options', 'pemo-reglages',    array( __CLASS__, 'page_reglages' ) );
        add_submenu_page( 'pemo-dashboard', 'Logs',        'Logs',        'manage_options', 'pemo-logs',        array( __CLASS__, 'page_logs' ) );
    }

    public static function register_settings() {
        $fields = array(
            'pemo_smtp_host', 'pemo_smtp_user', 'pemo_smtp_pass',
            'pemo_smtp_port', 'pemo_smtp_secure',
            'pemo_admin_email', 'pemo_rdv_url', 'pemo_site_name',
            'pemo_color_primary', 'pemo_relances_actives', 'pemo_privacy_url',
        );
        foreach ( $fields as $f ) {
            register_setting( 'pemo_settings', $f );
        }
    }

    public static function admin_styles() {
        ?>
        <style>
            .pemo-stats-grid { display:flex; gap:16px; margin:20px 0; flex-wrap:wrap; }
            .pemo-stat-card {
                background:#fff;
                border-radius:10px;
                padding:18px 24px;
                border-left:4px solid;
                box-shadow:0 1px 4px rgba(0,0,0,.07);
                min-width:140px;
                flex:1;
            }
            .pemo-stat-val { font-size:30px; font-weight:800; line-height:1.1; }
            .pemo-stat-lbl { font-size:12px; color:#64748B; margin-top:4px; }

            .pemo-section { background:#fff; border-radius:10px; padding:24px; box-shadow:0 1px 4px rgba(0,0,0,.07); margin:20px 0; }
            .pemo-section h3 { font-size:14px; font-weight:700; margin:0 0 18px; color:#1E2A3A; border-bottom:2px solid #E8541A; padding-bottom:8px; display:inline-block; }

            /* Barres dimensions */
            .pemo-dim-bar-row { display:flex; align-items:center; gap:10px; margin-bottom:10px; }
            .pemo-dim-bar-lbl { width:175px; font-size:12px; color:#334155; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; flex-shrink:0; }
            .pemo-dim-bar-track { flex:1; background:#e2e8f0; border-radius:999px; height:10px; overflow:hidden; }
            .pemo-dim-bar-fill  { height:10px; border-radius:999px; transition:width .6s ease; }
            .pemo-dim-bar-val   { width:46px; font-size:12px; font-weight:700; text-align:right; flex-shrink:0; }

            /* Distribution QE */
            .pemo-dist-grid { display:flex; gap:12px; flex-wrap:wrap; }
            .pemo-dist-card {
                flex:1; min-width:120px;
                border-radius:10px; padding:16px; text-align:center;
            }
            .pemo-dist-card .pct { font-size:28px; font-weight:800; }
            .pemo-dist-card .cnt { font-size:12px; margin-top:2px; }
            .pemo-dist-card .lbl { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; margin-top:6px; }

            /* Tableau résultats */
            .pemo-results-table { width:100%; border-collapse:collapse; font-size:13px; }
            .pemo-results-table th { text-align:left; padding:8px 10px; background:#f8fafc; border-bottom:2px solid #e2e8f0; font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#64748B; }
            .pemo-results-table td { padding:8px 10px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
            .pemo-results-table tr:hover td { background:#fafbfc; }
            .pemo-qe-badge { display:inline-block; border-radius:999px; padding:2px 10px; font-size:11px; font-weight:700; }
            .badge-faible    { background:#FEE2E2; color:#B91C1C; }
            .badge-modere    { background:#FEF3C7; color:#92400E; }
            .badge-eleve     { background:#DCFCE7; color:#15803D; }
            .badge-tres-eleve { background:#EDE9FE; color:#5B21B6; }

            /* Export */
            .pemo-actions { display:flex; gap:10px; margin:16px 0; flex-wrap:wrap; }
            .pemo-btn-primary {
                background: linear-gradient(135deg,#E8541A,#1E2A3A);
                color:#fff; border:none; border-radius:999px;
                padding:8px 20px; font-size:12px; font-weight:700; cursor:pointer;
                text-decoration:none; display:inline-block;
            }
            .pemo-btn-secondary {
                background:#fff; color:#1E2A3A; border:1px solid #CBD5E1;
                border-radius:999px; padding:8px 20px; font-size:12px; font-weight:700;
                cursor:pointer; text-decoration:none; display:inline-block;
            }
            .pemo-btn-primary:hover { opacity:.88; color:#fff; }
            .pemo-btn-secondary:hover { background:#f8fafc; color:#1E2A3A; }
            .pemo-btn-danger {
                background:#FEE2E2; color:#B91C1C; border:1px solid #FECACA;
                border-radius:999px; padding:8px 20px; font-size:12px; font-weight:700;
                cursor:pointer; text-decoration:none; display:inline-block;
            }
            .pemo-btn-danger:hover { background:#FCA5A5; color:#7F1D1D; }
            .pemo-btn-icon-del {
                background:none; border:none; cursor:pointer;
                color:#CBD5E1; font-size:16px; padding:2px 6px; border-radius:6px;
                transition:color .15s, background .15s;
            }
            .pemo-btn-icon-del:hover { color:#B91C1C; background:#FEE2E2; }
            .pemo-cb-all, .pemo-cb-row { cursor:pointer; accent-color:#E8541A; }
            #pemo-bulk-bar {
                display:none; align-items:center; gap:12px;
                background:#FEF2F2; border:1px solid #FECACA;
                border-radius:10px; padding:10px 16px; margin-bottom:12px;
                font-size:13px; font-weight:600; color:#B91C1C;
            }
            #pemo-bulk-bar.visible { display:flex; }
            tr.pemo-row-selected td { background:#FFF5F5 !important; }
        </style>
        <?php
    }

    // ── PAGE : Tableau de bord ─────────────────────────────────────────────────

    public static function page_dashboard() {
        $total     = PE_DB::count_sessions();
        $completed = PE_DB::count_completed();
        $rate      = $total > 0 ? round( ( $completed / $total ) * 100 ) : 0;
        $avg_score = $completed > 0 ? round( PE_DB::get_avg_score() ) : 0;
        $avg_qe    = $avg_score > 0 ? PE_Admin::score_to_badge( $avg_score ) : array( '', '' );
        ?>
        <div class="wrap">
            <h1>🧠 Praxis IE — Tableau de bord</h1>

            <div class="pemo-stats-grid">
                <?php self::stat_card( 'Tests démarrés',    $total,        '#E8541A' ); ?>
                <?php self::stat_card( 'Tests complétés',   $completed,    '#16A34A' ); ?>
                <?php self::stat_card( 'Taux de complétion', $rate . '%',  '#3B82F6' ); ?>
                <?php self::stat_card( 'Score QE moyen',    $avg_score > 0 ? $avg_score . '/320' : '—', '#8B5CF6' ); ?>
            </div>

            <?php if ( $completed > 0 ) :
                $dist = PE_DB::get_qe_distribution();
                $total_dist = array_sum( $dist );
            ?>
            <div class="pemo-section">
                <h3>📊 Distribution des niveaux QE</h3>
                <div class="pemo-dist-grid">
                    <?php
                    $levels = array(
                        'faible'     => array( 'QE Faible',     '#FEE2E2', '#B91C1C' ),
                        'modere'     => array( 'QE Modéré',     '#FEF3C7', '#92400E' ),
                        'eleve'      => array( 'QE Élevé',      '#DCFCE7', '#15803D' ),
                        'tres_eleve' => array( 'QE Très élevé', '#EDE9FE', '#5B21B6' ),
                    );
                    foreach ( $levels as $key => $meta ) :
                        $cnt = $dist[ $key ] ?? 0;
                        $pct = $total_dist > 0 ? round( ( $cnt / $total_dist ) * 100 ) : 0;
                    ?>
                    <div class="pemo-dist-card" style="background:<?php echo $meta[1]; ?>;border:1px solid <?php echo $meta[2]; ?>33;">
                        <div class="pct" style="color:<?php echo $meta[2]; ?>;"><?php echo $pct; ?>%</div>
                        <div class="cnt" style="color:<?php echo $meta[2]; ?>;"><?php echo $cnt; ?> personne<?php echo $cnt > 1 ? 's' : ''; ?></div>
                        <div class="lbl" style="color:<?php echo $meta[2]; ?>;"><?php echo $meta[0]; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="pemo-section">
                <h3>⚡ Accès rapides</h3>
                <p style="margin:0 0 8px;color:#64748B;font-size:13px;">Shortcode à insérer sur votre page test :</p>
                <code style="background:#f1f5f9;padding:6px 12px;border-radius:6px;font-size:13px;">[praxiemo]</code>
                <div class="pemo-actions" style="margin-top:16px;">
                    <a href="<?php echo admin_url( 'admin.php?page=pemo-resultats' ); ?>" class="pemo-btn-secondary">📋 Voir les résultats</a>
                    <a href="<?php echo admin_url( 'admin.php?page=pemo-analytics' ); ?>" class="pemo-btn-secondary">📈 Analytiques</a>
                    <a href="<?php echo admin_url( 'admin.php?page=pemo-reglages' ); ?>" class="pemo-btn-secondary">⚙️ Réglages</a>
                </div>
            </div>
        </div>
        <?php
    }

    // ── PAGE : Résultats ───────────────────────────────────────────────────────

    public static function page_resultats() {
        $rows = PE_DB::get_all_results( 100, 0 );
        ?>
        <div class="wrap">
            <h1>Résultats — Test IE</h1>

            <div class="pemo-actions">
                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-ajax.php?action=pemo_export_csv' ), 'pemo_export_csv' ) ); ?>"
                   class="pemo-btn-primary">⬇️ Exporter CSV</a>
            </div>

            <!-- Barre suppression en masse (cachée par défaut) -->
            <div id="pemo-bulk-bar">
                <span id="pemo-bulk-count">0 profil(s) sélectionné(s)</span>
                <button type="button" id="pemo-bulk-delete" class="pemo-btn-danger">🗑 Supprimer la sélection</button>
                <button type="button" id="pemo-bulk-cancel" class="pemo-btn-secondary" style="font-size:12px;padding:6px 14px;">Annuler</button>
            </div>

            <div class="pemo-section" style="padding:0;overflow:hidden;">
                <table class="pemo-results-table" id="pemo-results-table">
                    <thead>
                        <tr>
                            <th style="width:32px;"><input type="checkbox" class="pemo-cb-all" title="Tout sélectionner"></th>
                            <th>#</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>QE Global</th>
                            <th>Niveau</th>
                            <th>Démarré</th>
                            <th>Complété</th>
                            <th style="width:60px;">PDF</th>
                            <th style="width:70px;">Fiabilité</th>
                            <th style="width:48px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ( $rows as $row ) :
                        list( $badge_class, $niveau ) = self::score_to_badge( intval( $row->score_global ) );
                        $token = '';
                        if ( $row->score_global ) {
                            global $wpdb;
                            $token = $wpdb->get_var( $wpdb->prepare(
                                "SELECT token FROM {$wpdb->prefix}pemo_sessions WHERE id = %d",
                                absint( $row->id )
                            ) );
                        }
                    ?>
                        <tr data-id="<?php echo intval( $row->id ); ?>">
                            <td><input type="checkbox" class="pemo-cb-row" value="<?php echo intval( $row->id ); ?>"></td>
                            <td style="color:#94A3B8;"><?php echo intval( $row->id ); ?></td>
                            <td><strong><?php echo esc_html( $row->prenom ); ?></strong></td>
                            <td style="color:#64748B;"><?php echo esc_html( $row->email ); ?></td>
                            <td style="font-weight:700;"><?php echo $row->score_global ? intval( $row->score_global ) . '/320' : '—'; ?></td>
                            <td>
                                <?php if ( $row->score_global ) : ?>
                                <span class="pemo-qe-badge <?php echo esc_attr( $badge_class ); ?>"><?php echo esc_html( $niveau ); ?></span>
                                <?php else : echo '—'; endif; ?>
                            </td>
                            <td style="color:#64748B;font-size:12px;"><?php echo esc_html( $row->started_at ); ?></td>
                            <td style="color:#64748B;font-size:12px;">
                                <?php echo $row->completed_at ? esc_html( $row->completed_at ) : '<span style="color:#F59E0B;">⏳ En cours</span>'; ?>
                            </td>
                            <td>
                                <?php if ( $row->completed_at && $token ) :
                                    $pdf_url = PE_PDF::get_url( $token, wp_create_nonce( 'pemo_nonce' ) );
                                ?>
                                <a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" title="Voir le rapport PDF" style="text-decoration:none;">📄</a>
                                <?php else : echo '—'; endif; ?>
                            </td>
                            <td>
                                <?php
                                // Score de désirabilité — récupéré en jointure si disponible
                                $des = isset( $row->score_desirabilite ) ? intval( $row->score_desirabilite ) : null;
                                if ( $des !== null && $row->score_global ) :
                                    if ( $des <= 12 ) {
                                        echo '<span title="Biais fort (score ' . $des . '/24)" style="color:#B91C1C;font-weight:700;font-size:12px;">⚠ ' . $des . '</span>';
                                    } elseif ( $des <= 18 ) {
                                        echo '<span title="Biais modéré (score ' . $des . '/24)" style="color:#92400E;font-size:12px;">~ ' . $des . '</span>';
                                    } else {
                                        echo '<span title="Fiable (score ' . $des . '/24)" style="color:#15803D;font-size:12px;">✓ ' . $des . '</span>';
                                    }
                                else :
                                    echo '—';
                                endif;
                                ?>
                            </td>
                            <td>
                                <button type="button"
                                    class="pemo-btn-icon-del pemo-delete-one"
                                    data-id="<?php echo intval( $row->id ); ?>"
                                    data-name="<?php echo esc_attr( $row->prenom . ' (' . $row->email . ')' ); ?>"
                                    title="Supprimer ce profil">🗑</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <script>
        (function() {
            var AJAX_URL = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
            var NONCE    = '<?php echo esc_js( wp_create_nonce( 'pemo_delete_nonce' ) ); ?>';

            function deleteIds(ids, onDone) {
                var fd = new FormData();
                fd.append('action', 'pemo_delete_session');
                fd.append('nonce', NONCE);
                ids.forEach(function(id) { fd.append('ids[]', id); });
                fetch(AJAX_URL, { method: 'POST', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(r) { onDone(r.success, r.data && r.data.message ? r.data.message : ''); })
                    .catch(function() { onDone(false, 'Erreur réseau.'); });
            }

            function removeRows(ids) {
                ids.forEach(function(id) {
                    var tr = document.querySelector('tr[data-id="' + id + '"]');
                    if (tr) tr.remove();
                });
            }

            /* ── Suppression individuelle ─────────────────── */
            document.querySelectorAll('.pemo-delete-one').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var id   = this.dataset.id;
                    var name = this.dataset.name;
                    if (!confirm('Supprimer le profil de ' + name + ' ?\n\nCette action est irréversible.')) return;
                    deleteIds([id], function(ok, msg) {
                        if (ok) { removeRows([id]); }
                        else { alert('Erreur : ' + msg); }
                    });
                });
            });

            /* ── Cases à cocher ───────────────────────────── */
            var cbAll    = document.querySelector('.pemo-cb-all');
            var bulkBar  = document.getElementById('pemo-bulk-bar');
            var bulkCnt  = document.getElementById('pemo-bulk-count');
            var bulkDel  = document.getElementById('pemo-bulk-delete');
            var bulkCanc = document.getElementById('pemo-bulk-cancel');

            function getChecked() {
                return Array.from(document.querySelectorAll('.pemo-cb-row:checked')).map(function(cb) { return cb.value; });
            }
            function updateBulkBar() {
                var checked = getChecked();
                var n = checked.length;
                if (n > 0) {
                    bulkCnt.textContent = n + ' profil' + (n > 1 ? 's' : '') + ' sélectionné' + (n > 1 ? 's' : '');
                    bulkBar.classList.add('visible');
                    // Surligner les lignes
                    document.querySelectorAll('tr[data-id]').forEach(function(tr) {
                        var cb = tr.querySelector('.pemo-cb-row');
                        tr.classList.toggle('pemo-row-selected', cb && cb.checked);
                    });
                } else {
                    bulkBar.classList.remove('visible');
                    document.querySelectorAll('tr.pemo-row-selected').forEach(function(tr) { tr.classList.remove('pemo-row-selected'); });
                }
            }

            if (cbAll) {
                cbAll.addEventListener('change', function() {
                    document.querySelectorAll('.pemo-cb-row').forEach(function(cb) { cb.checked = cbAll.checked; });
                    updateBulkBar();
                });
            }
            document.querySelectorAll('.pemo-cb-row').forEach(function(cb) {
                cb.addEventListener('change', function() {
                    var allCbs = document.querySelectorAll('.pemo-cb-row');
                    var allChecked = Array.from(allCbs).every(function(c) { return c.checked; });
                    if (cbAll) cbAll.checked = allChecked;
                    updateBulkBar();
                });
            });

            /* ── Suppression en masse ─────────────────────── */
            if (bulkDel) {
                bulkDel.addEventListener('click', function() {
                    var ids = getChecked();
                    if (!ids.length) return;
                    if (!confirm('Supprimer ' + ids.length + ' profil(s) sélectionné(s) ?\n\nCette action est irréversible.')) return;
                    deleteIds(ids, function(ok, msg) {
                        if (ok) {
                            removeRows(ids);
                            bulkBar.classList.remove('visible');
                            if (cbAll) cbAll.checked = false;
                        } else {
                            alert('Erreur : ' + msg);
                        }
                    });
                });
            }
            if (bulkCanc) {
                bulkCanc.addEventListener('click', function() {
                    document.querySelectorAll('.pemo-cb-row').forEach(function(cb) { cb.checked = false; });
                    if (cbAll) cbAll.checked = false;
                    updateBulkBar();
                });
            }
        })();
        </script>
        <?php
    }

    // ── PAGE : Analytiques ────────────────────────────────────────────────────

    public static function page_analytics() {
        $dims      = PE_Calculator::get_dimensions();
        $familles  = PE_Calculator::get_familles();
        $averages  = PE_DB::get_dim_averages();
        $completed = PE_DB::count_completed();

        $fam_colors = array(
            1 => '#E8541A',
            2 => '#F59E0B',
            3 => '#3B82F6',
            4 => '#16A34A',
        );
        ?>
        <div class="wrap">
            <h1>📈 Analytiques — Test IE</h1>

            <?php if ( $completed === 0 ) : ?>
            <div class="notice notice-info"><p>Aucun test complété pour l'instant. Les analytiques seront disponibles dès le premier test finalisé.</p></div>
            <?php else : ?>

            <p style="color:#64748B;font-size:13px;margin:0 0 20px;">Basé sur <strong><?php echo $completed; ?></strong> test<?php echo $completed > 1 ? 's' : ''; ?> complété<?php echo $completed > 1 ? 's' : ''; ?>.</p>

            <!-- Scores moyens par dimension -->
            <div class="pemo-section">
                <h3>📊 Score moyen par dimension</h3>
                <p style="font-size:12px;color:#94A3B8;margin:-10px 0 16px;">Score sur 20 — moyenne de tous les participants</p>

                <?php foreach ( $familles as $fam_id => $fam ) :
                    $bar_color = $fam_colors[ $fam_id ] ?? '#E8541A';
                ?>
                    <p style="font-size:11px;font-weight:700;color:#64748B;text-transform:uppercase;letter-spacing:.06em;margin:18px 0 10px;">
                        <?php echo esc_html( $fam['emoji'] . ' ' . $fam['label'] ); ?>
                    </p>
                    <?php foreach ( $dims as $dim_id => $dim ) :
                        if ( $dim['famille'] !== $fam_id ) continue;
                        $col     = 'dim_' . $dim_id;
                        $avg     = $averages[ $col ] ?? 0;
                        $avg_fmt = number_format( $avg, 1 );
                        $pct     = round( ( max( 0, $avg - 5 ) / 15 ) * 100 );
                    ?>
                    <div class="pemo-dim-bar-row">
                        <span class="pemo-dim-bar-lbl" title="<?php echo esc_attr( $dim['label'] ); ?>"><?php echo esc_html( $dim['label'] ); ?></span>
                        <div class="pemo-dim-bar-track">
                            <div class="pemo-dim-bar-fill" style="width:<?php echo $pct; ?>%;background:<?php echo $bar_color; ?>;"></div>
                        </div>
                        <span class="pemo-dim-bar-val" style="color:<?php echo $bar_color; ?>;"><?php echo $avg_fmt; ?>/20</span>
                    </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>

            <!-- Distribution QE -->
            <?php
            $dist       = PE_DB::get_qe_distribution();
            $total_dist = array_sum( $dist );
            $dist_data  = array(
                'faible'     => array( 'QE Faible',     '#FEE2E2', '#B91C1C', '80–120' ),
                'modere'     => array( 'QE Modéré',     '#FEF3C7', '#92400E', '121–200' ),
                'eleve'      => array( 'QE Élevé',      '#DCFCE7', '#15803D', '201–280' ),
                'tres_eleve' => array( 'QE Très élevé', '#EDE9FE', '#5B21B6', '281–320' ),
            );
            ?>
            <div class="pemo-section">
                <h3>🎯 Répartition des niveaux QE</h3>
                <div style="display:flex;flex-direction:column;gap:10px;">
                <?php foreach ( $dist_data as $key => $meta ) :
                    $cnt = $dist[ $key ] ?? 0;
                    $pct = $total_dist > 0 ? round( ( $cnt / $total_dist ) * 100 ) : 0;
                ?>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <span style="width:130px;font-size:12px;font-weight:700;color:<?php echo $meta[2]; ?>;"><?php echo $meta[0]; ?></span>
                        <span style="font-size:10px;color:#94A3B8;width:60px;"><?php echo $meta[3]; ?></span>
                        <div style="flex:1;background:#e2e8f0;border-radius:999px;height:14px;overflow:hidden;">
                            <div style="width:<?php echo $pct; ?>%;background:<?php echo $meta[2]; ?>;height:14px;border-radius:999px;"></div>
                        </div>
                        <span style="width:50px;font-size:13px;font-weight:700;text-align:right;color:<?php echo $meta[2]; ?>;"><?php echo $pct; ?>%</span>
                        <span style="width:30px;font-size:12px;color:#64748B;">(<?php echo $cnt; ?>)</span>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>

            <!-- Dimensions les plus et moins développées -->
            <?php if ( ! empty( $averages ) ) :
                $dim_avgs = array();
                foreach ( $dims as $dim_id => $dim ) {
                    $col = 'dim_' . $dim_id;
                    $dim_avgs[ $dim_id ] = $averages[ $col ] ?? 0;
                }
                arsort( $dim_avgs );
                $top3_ids = array_slice( array_keys( $dim_avgs ), 0, 3, true );
                $bot3_ids = array_slice( array_keys( array_reverse( $dim_avgs, true ) ), 0, 3, true );
            ?>
            <div style="display:flex;gap:16px;">
                <div class="pemo-section" style="flex:1;">
                    <h3>🏆 Meilleures dimensions (moyenne)</h3>
                    <?php foreach ( $top3_ids as $dim_id ) :
                        $col   = 'dim_' . $dim_id;
                        $avg   = number_format( $averages[ $col ] ?? 0, 1 );
                        $fam   = $dims[ $dim_id ]['famille'];
                        $color = $fam_colors[ $fam ] ?? '#E8541A';
                    ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                        <span style="font-size:13px;color:#334155;"><?php echo esc_html( $dims[ $dim_id ]['label'] ); ?></span>
                        <span style="font-size:14px;font-weight:800;color:<?php echo $color; ?>;"><?php echo $avg; ?>/20</span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="pemo-section" style="flex:1;">
                    <h3>🎯 Axes de progression (moyenne)</h3>
                    <?php foreach ( $bot3_ids as $dim_id ) :
                        $col   = 'dim_' . $dim_id;
                        $avg   = number_format( $averages[ $col ] ?? 0, 1 );
                        $fam   = $dims[ $dim_id ]['famille'];
                        $color = $fam_colors[ $fam ] ?? '#E8541A';
                    ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                        <span style="font-size:13px;color:#334155;"><?php echo esc_html( $dims[ $dim_id ]['label'] ); ?></span>
                        <span style="font-size:14px;font-weight:800;color:<?php echo $color; ?>;"><?php echo $avg; ?>/20</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php endif; ?>
        </div>
        <?php
    }

    // ── PAGE : Réglages ────────────────────────────────────────────────────────

    public static function page_reglages() {
        if ( isset( $_POST['pemo_save'] ) ) {
            check_admin_referer( 'pemo_save_settings' );
            $fields = array(
                'pemo_smtp_host', 'pemo_smtp_user', 'pemo_smtp_pass',
                'pemo_smtp_port', 'pemo_smtp_secure', 'pemo_admin_email',
                'pemo_rdv_url', 'pemo_site_name', 'pemo_color_primary',
            );
            foreach ( $fields as $f ) {
                if ( isset( $_POST[ $f ] ) ) {
                    if ( $f === 'pemo_privacy_url' ) {
                        update_option( $f, esc_url_raw( wp_unslash( $_POST[ $f ] ) ) );
                    } else {
                        update_option( $f, sanitize_text_field( wp_unslash( $_POST[ $f ] ) ) );
                    }
                }
            }
            update_option( 'pemo_relances_actives', isset( $_POST['pemo_relances_actives'] ) ? 1 : 0 );
            echo '<div class="notice notice-success"><p>Réglages sauvegardés.</p></div>';
        }
        $v = function( $key, $default = '' ) {
            return esc_attr( get_option( $key, $default ) );
        };
        ?>
        <div class="wrap">
            <h1>Réglages — Praxis IE</h1>
            <form method="post">
                <?php wp_nonce_field( 'pemo_save_settings' ); ?>
                <input type="hidden" name="pemo_save" value="1">
                <div class="pemo-section">
                    <h3>SMTP OVH</h3>
                    <table class="form-table">
                        <tr><th>Hôte SMTP</th><td><input name="pemo_smtp_host" value="<?php echo $v('pemo_smtp_host','ssl0.ovh.net'); ?>" class="regular-text" placeholder="ssl0.ovh.net"></td></tr>
                        <tr><th>Utilisateur</th><td><input name="pemo_smtp_user" value="<?php echo $v('pemo_smtp_user'); ?>" class="regular-text" placeholder="contact@praxis-accompagnement.com"></td></tr>
                        <tr><th>Mot de passe</th><td><input type="password" name="pemo_smtp_pass" value="<?php echo $v('pemo_smtp_pass'); ?>" class="regular-text"></td></tr>
                        <tr><th>Port</th><td><input name="pemo_smtp_port" value="<?php echo $v('pemo_smtp_port','465'); ?>" class="small-text"></td></tr>
                        <tr><th>Sécurité</th><td>
                            <select name="pemo_smtp_secure">
                                <?php foreach ( array('ssl','tls','none') as $opt ) : ?>
                                <option value="<?php echo $opt; ?>" <?php selected( get_option('pemo_smtp_secure','ssl'), $opt ); ?>><?php echo strtoupper($opt); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td></tr>
                        <tr><th></th><td>
                            <button type="button" id="pemo-test-smtp" class="pemo-btn-secondary">🔌 Tester la connexion SMTP</button>
                            <span id="pemo-smtp-result" style="margin-left:12px;font-size:13px;"></span>
                        </td></tr>
                    </table>
                </div>
                <div class="pemo-section">
                    <h3>Général</h3>
                    <table class="form-table">
                        <tr><th>Email admin</th><td><input name="pemo_admin_email" value="<?php echo $v('pemo_admin_email', get_option('admin_email')); ?>" class="regular-text"></td></tr>
                        <tr><th>URL Prise de RDV</th><td><input name="pemo_rdv_url" value="<?php echo $v('pemo_rdv_url', home_url('/contact')); ?>" class="regular-text"></td></tr>
                        <tr>
                            <th>Page de confidentialité</th>
                            <td>
                                <?php
                                $priv_url = PE_Privacy::get_url();
                                if ( $priv_url ) :
                                ?>
                                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                                    <a href="<?php echo esc_url( $priv_url ); ?>" target="_blank"
                                       style="font-weight:600;color:#15803D;">
                                        ✅ <?php echo esc_html( $priv_url ); ?>
                                    </a>
                                    <button type="button" id="pemo-recreate-privacy" class="pemo-btn-secondary">🔄 Mettre à jour le contenu</button>
                                    <span id="pemo-privacy-result" style="font-size:13px;"></span>
                                </div>
                                <p class="description">Cette page a été créée automatiquement par le plugin. Cliquez sur "Mettre à jour" pour régénérer son contenu avec vos réglages actuels (email, nom du site).</p>
                                <?php else : ?>
                                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                                    <span style="color:#B91C1C;font-weight:600;">⚠️ Page non trouvée</span>
                                    <button type="button" id="pemo-recreate-privacy" class="pemo-btn-primary">✨ Créer la page maintenant</button>
                                    <span id="pemo-privacy-result" style="font-size:13px;"></span>
                                </div>
                                <p class="description">La page de politique de confidentialité n'existe pas encore ou a été supprimée. Cliquez sur le bouton pour la créer automatiquement.</p>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr><th>Nom du site</th><td><input name="pemo_site_name" value="<?php echo $v('pemo_site_name', get_bloginfo('name')); ?>" class="regular-text"></td></tr>
                        <tr><th>Couleur principale</th><td><input type="color" name="pemo_color_primary" value="<?php echo $v('pemo_color_primary','#E8541A'); ?>"></td></tr>
                        <tr><th>Relances email</th><td><label><input type="checkbox" name="pemo_relances_actives" value="1" <?php checked( get_option('pemo_relances_actives',1), 1 ); ?>> Activer les relances J+3 et J+8</label></td></tr>
                    </table>
                </div>
                <?php submit_button( 'Sauvegarder' ); ?>
            </form>
            <script>
            document.getElementById('pemo-test-smtp').addEventListener('click', function() {
                var btn = this, res = document.getElementById('pemo-smtp-result');
                btn.disabled = true; res.textContent = 'Envoi en cours...';
                var fd = new FormData();
                fd.append('action','pemo_test_smtp');
                fd.append('nonce', '<?php echo wp_create_nonce('pemo_nonce'); ?>');
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {method:'POST',body:fd})
                    .then(function(r){return r.json();})
                    .then(function(r){
                        res.textContent = r.success ? '✅ ' + r.data.message : '❌ ' + (r.data && r.data.message ? r.data.message : 'Erreur');
                        res.style.color = r.success ? '#15803D' : '#B91C1C';
                    })
                    .catch(function(){ res.textContent = '❌ Erreur réseau'; res.style.color = '#B91C1C'; })
                    .finally(function(){ btn.disabled = false; });
            });
            // Bouton recréer/créer la page de confidentialité
            var privBtn = document.getElementById('pemo-recreate-privacy');
            if (privBtn) {
                privBtn.addEventListener('click', function() {
                    var btn = this, res = document.getElementById('pemo-privacy-result');
                    btn.disabled = true; res.textContent = 'Création en cours…'; res.style.color = '#64748B';
                    var fd = new FormData();
                    fd.append('action', 'pemo_recreate_privacy');
                    fd.append('nonce', '<?php echo wp_create_nonce('pemo_nonce'); ?>');
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {method:'POST',body:fd})
                        .then(function(r){return r.json();})
                        .then(function(r){
                            if (r.success) {
                                res.textContent = '✅ ' + r.data.message;
                                res.style.color = '#15803D';
                                // Recharger après 1.5s pour afficher l'URL
                                setTimeout(function(){ location.reload(); }, 1500);
                            } else {
                                res.textContent = '❌ ' + (r.data && r.data.message ? r.data.message : 'Erreur');
                                res.style.color = '#B91C1C';
                                btn.disabled = false;
                            }
                        })
                        .catch(function(){ res.textContent = '❌ Erreur réseau'; res.style.color = '#B91C1C'; btn.disabled = false; });
                });
            }
            </script>
        </div>
        <?php
    }

    // ── PAGE : Logs ────────────────────────────────────────────────────────────

    public static function page_logs() {
        $logs = PE_Logger::get_recent( '', 100 );
        ?>
        <div class="wrap">
            <h1>Logs — Praxis IE</h1>
            <?php if ( isset( $_GET['clear'] ) ) {
                check_admin_referer( 'pemo_clear_logs' );
                PE_Logger::clear();
                echo '<div class="notice notice-success"><p>Logs effacés.</p></div>';
            } ?>
            <div class="pemo-actions">
                <a href="<?php echo esc_url( wp_nonce_url( admin_url('admin.php?page=pemo-logs&clear=1'), 'pemo_clear_logs' ) ); ?>" class="pemo-btn-secondary">🗑 Vider les logs</a>
            </div>
            <div class="pemo-section" style="padding:0;overflow:hidden;">
                <table class="pemo-results-table">
                    <thead><tr><th style="width:70px">Niveau</th><th style="width:90px">Contexte</th><th>Message</th><th style="width:140px">Date</th></tr></thead>
                    <tbody>
                    <?php foreach ( $logs as $log ) :
                        $level_colors = array( 'error' => '#FEE2E2', 'warning' => '#FEF3C7', 'info' => '#EFF6FF' );
                        $bg = $level_colors[ $log->level ] ?? '';
                    ?>
                        <tr style="background:<?php echo $bg; ?>">
                            <td><strong><?php echo esc_html( strtoupper( $log->level ) ); ?></strong></td>
                            <td style="color:#64748B;"><?php echo esc_html( $log->context ); ?></td>
                            <td><?php echo esc_html( $log->message ); ?></td>
                            <td style="font-size:11px;color:#94A3B8;"><?php echo esc_html( $log->created_at ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // ── Handlers AJAX ──────────────────────────────────────────────────────────

    public static function handle_recreate_privacy() {
        check_ajax_referer( 'pemo_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Non autorisé' ), 403 );
        }

        $result = PE_Privacy::create_or_update_page();

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        $url = get_permalink( $result );
        wp_send_json_success( array(
            'message' => 'Page créée/mise à jour avec succès.',
            'url'     => $url,
            'page_id' => $result,
        ) );
    }

    public static function handle_delete_session() {
        check_ajax_referer( 'pemo_delete_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Non autorisé' ), 403 );
        }

        // Accepte un ou plusieurs IDs
        $raw_ids = isset( $_POST['ids'] ) ? (array) $_POST['ids'] : array();
        $ids     = array_filter( array_map( 'absint', $raw_ids ) );

        if ( empty( $ids ) ) {
            wp_send_json_error( array( 'message' => 'Aucun identifiant fourni.' ) );
        }

        $count = PE_DB::delete_sessions( $ids );
        wp_send_json_success( array( 'message' => $count . ' profil(s) supprimé(s).', 'count' => $count ) );
    }

    public static function handle_test_smtp() {
        check_ajax_referer( 'pemo_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array('message' => 'Non autorisé'), 403 );
        $to   = get_option( 'pemo_admin_email', get_option( 'admin_email' ) );
        $sent = wp_mail( $to, '[Praxis IE] Test SMTP', 'Test de configuration SMTP réussi.' );
        $sent ? wp_send_json_success( array('message' => 'Email envoyé à ' . $to) )
              : wp_send_json_error( array('message' => 'Échec — vérifiez vos réglages SMTP.') );
    }

    public static function handle_export_csv() {
        check_admin_referer( 'pemo_export_csv' );
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé', 403 );

        $dims = PE_Calculator::get_dimensions();
        $rows = PE_DB::get_all_results_for_export( 1000 );

        // En-têtes CSV
        $headers = array( 'ID', 'Prénom', 'Email', 'Score global', 'Niveau QE', 'Fiabilité /24', 'Date test' );
        foreach ( $dims as $dim ) {
            $headers[] = $dim['label'];
        }

        header( 'Content-Type: text/csv; charset=UTF-8' );
        header( 'Content-Disposition: attachment; filename="praxiemo-resultats-' . gmdate( 'Y-m-d' ) . '.csv"' );
        header( 'Pragma: no-cache' );

        $out = fopen( 'php://output', 'w' );
        fprintf( $out, chr(0xEF) . chr(0xBB) . chr(0xBF) ); // BOM UTF-8 pour Excel

        fputcsv( $out, $headers, ';' );

        foreach ( $rows as $row ) {
            list( , $niveau ) = self::score_to_badge( intval( $row->score_global ) );
            $des_interp = PE_Calculator::interpret_desirabilite( intval( $row->score_desirabilite ?? 0 ) );
            $line = array(
                $row->id,
                $row->prenom,
                $row->email,
                intval( $row->score_global ),
                $niveau,
                intval( $row->score_desirabilite ?? 0 ),
                $row->completed_at,
            );
            foreach ( $dims as $dim_id => $dim ) {
                $col    = 'dim_' . $dim_id;
                $line[] = intval( $row->$col ?? 0 );
            }
            fputcsv( $out, $line, ';' );
        }
        fclose( $out );
        exit;
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private static function stat_card( $label, $value, $color ) {
        echo '<div class="pemo-stat-card" style="border-color:' . esc_attr( $color ) . ';">'
           . '<div class="pemo-stat-val" style="color:' . esc_attr( $color ) . ';">' . esc_html( $value ) . '</div>'
           . '<div class="pemo-stat-lbl">' . esc_html( $label ) . '</div>'
           . '</div>';
    }

    /**
     * Retourne [badge_class, niveau_label] selon le score global.
     * @param int $score
     * @return array
     */
    public static function score_to_badge( $score ) {
        if ( ! $score ) return array( '', '—' );
        if ( $score <= 120 ) return array( 'badge-faible',     'QE Faible' );
        if ( $score <= 200 ) return array( 'badge-modere',     'QE Modéré' );
        if ( $score <= 280 ) return array( 'badge-eleve',      'QE Élevé' );
        return array( 'badge-tres-eleve', 'QE Très élevé' );
    }
}
