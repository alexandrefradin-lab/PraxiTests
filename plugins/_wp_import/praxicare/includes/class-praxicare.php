<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PraxiCare {

    public function init() {
        add_shortcode( 'praxicare', array( $this, 'render_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_praxicare_save',        'praxicare_ajax_save' );
        add_action( 'wp_ajax_nopriv_praxicare_save', 'praxicare_ajax_save' );

        // AJAX admin : test relance
        add_action( 'wp_ajax_praxicare_test_relance',   array( $this, 'ajax_test_relance' ) );
        // AJAX admin : toggle relance_active
        add_action( 'wp_ajax_praxicare_toggle_relance', array( $this, 'ajax_toggle_relance' ) );
        // AJAX admin : supprimer un résultat
        add_action( 'wp_ajax_praxicare_delete',         array( $this, 'ajax_delete' ) );

        // Page de réglages admin
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public static function activate() {
        global $wpdb;
        $table   = $wpdb->prefix . 'praxicare_results';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            prenom VARCHAR(100) NOT NULL,
            email VARCHAR(200) NOT NULL,
            score_demandes TINYINT UNSIGNED NOT NULL,
            score_latitude TINYINT UNSIGNED NOT NULL,
            score_soutien  TINYINT UNSIGNED NOT NULL,
            score_ee       TINYINT UNSIGNED NOT NULL,
            score_dp       TINYINT UNSIGNED NOT NULL,
            score_ap       TINYINT UNSIGNED NOT NULL,
            profil         VARCHAR(100) NOT NULL,
            relance_2j     DATETIME DEFAULT NULL,
            relance_8j     DATETIME DEFAULT NULL,
            relance_15j    DATETIME DEFAULT NULL,
            created_at     DATETIME DEFAULT CURRENT_TIMESTAMP
        ) {$charset};";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        // Ajouter les colonnes relance si la table existe déjà (mise à jour)
        $cols = $wpdb->get_col( "SHOW COLUMNS FROM {$table}" );
        if ( ! in_array( 'relance_2j', $cols ) ) {
            $wpdb->query( "ALTER TABLE {$table} ADD COLUMN relance_2j DATETIME DEFAULT NULL" );
        }
        if ( ! in_array( 'relance_8j', $cols ) ) {
            $wpdb->query( "ALTER TABLE {$table} ADD COLUMN relance_8j DATETIME DEFAULT NULL" );
        }
        if ( ! in_array( 'relance_15j', $cols ) ) {
            $wpdb->query( "ALTER TABLE {$table} ADD COLUMN relance_15j DATETIME DEFAULT NULL" );
        }
        if ( ! in_array( 'relance_active', $cols ) ) {
            $wpdb->query( "ALTER TABLE {$table} ADD COLUMN relance_active TINYINT(1) NOT NULL DEFAULT 1" );
        }

        // Activer le cron de relance
        praxicare_schedule_cron();
    }

    public function add_admin_menu() {
        add_menu_page(
            'PraxiCare',
            'PraxiCare',
            'manage_options',
            'praxicare-results',
            array( $this, 'render_results_page' ),
            'dashicons-heart',
            30
        );
        add_submenu_page(
            'praxicare-results',
            'Résultats',
            'Résultats',
            'manage_options',
            'praxicare-results',
            array( $this, 'render_results_page' )
        );
        add_submenu_page(
            'praxicare-results',
            'Réglages SMTP',
            'Réglages',
            'manage_options',
            'praxicare-settings',
            array( $this, 'render_settings_page' )
        );
        add_submenu_page(
            'praxicare-results',
            'Emails de relance',
            'Relances',
            'manage_options',
            'praxicare-relances',
            array( $this, 'render_relances_page' )
        );
    }

    public function register_settings() {
        register_setting( 'praxicare_settings', 'praxicare_smtp_host' );
        register_setting( 'praxicare_settings', 'praxicare_smtp_user' );
        register_setting( 'praxicare_settings', 'praxicare_smtp_pass' );
        register_setting( 'praxicare_settings', 'praxicare_smtp_port' );
        register_setting( 'praxicare_settings', 'praxicare_smtp_secure' );
        register_setting( 'praxicare_settings', 'praxicare_admin_email' );

        // Relances — sujets et textes par niveau
        $niveaux = array( 'vert', 'jaune', 'orange', 'rouge', 'critique' );
        foreach ( array( '2j', '8j', '15j' ) as $j ) {
            foreach ( $niveaux as $n ) {
                register_setting( 'praxicare_relances', 'praxicare_sujet_' . $j . '_' . $n );
                register_setting( 'praxicare_relances', 'praxicare_intro_' . $j . '_' . $n );
            }
        }
    }

    public function render_results_page() {
        global $wpdb;
        $table = $wpdb->prefix . 'praxicare_results';

        // Export CSV
        if ( isset( $_GET['export'] ) && $_GET['export'] === 'csv' ) {
            check_admin_referer( 'praxicare_export' );
            $rows = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC", ARRAY_A );
            header( 'Content-Type: text/csv; charset=UTF-8' );
            header( 'Content-Disposition: attachment; filename="praxicare-resultats-' . date('Y-m-d') . '.csv"' );
            $out = fopen( 'php://output', 'w' );
            fprintf( $out, chr(0xEF).chr(0xBB).chr(0xBF) ); // BOM UTF-8
            fputcsv( $out, ['Date','Prénom','Email','Profil','Demandes','Latitude','Soutien','EE','DP','AP'], ';' );
            foreach ( $rows as $row ) {
                fputcsv( $out, [
                    $row['created_at'], $row['prenom'], $row['email'], $row['profil'],
                    $row['score_demandes'], $row['score_latitude'], $row['score_soutien'],
                    $row['score_ee'], $row['score_dp'], $row['score_ap'],
                ], ';' );
            }
            fclose( $out );
            exit;
        }

        // Vue détail
        if ( isset( $_GET['detail'] ) ) {
            $id  = absint( $_GET['detail'] );
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
            if ( $row ) {
                $this->render_detail_page( $row );
                return;
            }
        }

        // Filtre profil
        $filtre = isset( $_GET['profil'] ) ? sanitize_text_field( $_GET['profil'] ) : '';
        $where  = $filtre ? $wpdb->prepare( "WHERE profil = %s", $filtre ) : '';
        $rows   = $wpdb->get_results( "SELECT * FROM {$table} {$where} ORDER BY created_at DESC" );
        $total  = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );

        $niveaux = [
            'bien_etre'           => ['label'=>'Bien-être',            'color'=>'#2E7D32'],
            'engagement_sain'     => ['label'=>'Engagement sain',      'color'=>'#2E7D32'],
            'sous_stimulation'    => ['label'=>'Sous-stimulation',     'color'=>'#F59E0B'],
            'risque_situationnel' => ['label'=>'Risque situationnel',  'color'=>'#F59E0B'],
            'vigilance'           => ['label'=>'Vigilance',            'color'=>'#F59E0B'],
            'risque_cumule'       => ['label'=>'Risque cumulé',        'color'=>'#E65100'],
            'tension_isolee'      => ['label'=>'Tension isolée',       'color'=>'#E65100'],
            'fragilite'           => ['label'=>'Fragilité',            'color'=>'#E65100'],
            'epuisement_interne'  => ['label'=>'Épuisement interne',   'color'=>'#E65100'],
            'souffrance_installee'=> ['label'=>'Souffrance installée', 'color'=>'#E65100'],
            'alarme'              => ['label'=>'Alarme',               'color'=>'#DC2626'],
            'souffrance_averee'   => ['label'=>'Souffrance avérée',    'color'=>'#DC2626'],
            'urgence'             => ['label'=>'Urgence',              'color'=>'#B91C1C'],
        ];
        ?>
        <div class="wrap">
            <h1 style="display:flex;align-items:center;gap:12px;">❤️ PraxiCare — Résultats <span style="font-size:14px;font-weight:400;color:#666;"><?php echo absint($total); ?> test(s) au total</span></h1>

            <div style="display:flex;gap:12px;margin:16px 0;flex-wrap:wrap;">
                <a href="<?php echo admin_url('admin.php?page=praxicare-results'); ?>" class="button <?php echo !$filtre ? 'button-primary' : ''; ?>">Tous</a>
                <?php foreach ( $niveaux as $id => $n ) : ?>
                    <a href="<?php echo admin_url('admin.php?page=praxicare-results&profil=' . $id); ?>"
                       class="button <?php echo $filtre === $id ? 'button-primary' : ''; ?>"
                       style="border-left:4px solid <?php echo $n['color']; ?>">
                        <?php echo esc_html($n['label']); ?>
                    </a>
                <?php endforeach; ?>
                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=praxicare-results&export=csv'), 'praxicare_export'); ?>" class="button" style="margin-left:auto;">⬇️ Exporter CSV</a>
            </div>

            <table class="wp-list-table widefat fixed striped" style="border-radius:8px;overflow:hidden;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th style="width:140px;">Profil</th>
                        <th>Demandes</th>
                        <th>Latitude</th>
                        <th>Soutien</th>
                        <th>EE</th>
                        <th>DP</th>
                        <th>AP</th>
                        <th>J+2</th>
                        <th>J+8</th>
                        <th>J+15</th>
                        <th title="Décochez pour désactiver les relances automatiques (client déjà suivi)">Relances</th>
                        <th>Détail</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if ( empty($rows) ) : ?>
                    <tr><td colspan="11" style="text-align:center;padding:24px;color:#999;">Aucun résultat</td></tr>
                <?php else : ?>
                    <?php foreach ( $rows as $row ) :
                        $n = $niveaux[$row->profil] ?? ['label'=>$row->profil,'color'=>'#999'];
                    ?>
                    <tr>
                        <td><?php echo esc_html( date('d/m/Y H:i', strtotime($row->created_at)) ); ?></td>
                        <td><?php echo esc_html( $row->prenom ); ?></td>
                        <td><a href="mailto:<?php echo esc_attr($row->email); ?>"><?php echo esc_html($row->email); ?></a></td>
                        <td style="width:140px;"><span style="display:inline-block;max-width:130px;background:<?php echo $n['color']; ?>;color:#fff;padding:3px 10px;border-radius:999px;font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;vertical-align:middle;"><?php echo esc_html($n['label']); ?></span></td>
                        <td><?php echo absint($row->score_demandes); ?>/36</td>
                        <td><?php echo absint($row->score_latitude); ?>/36</td>
                        <td><?php echo absint($row->score_soutien); ?>/32</td>
                        <td><?php echo absint($row->score_ee); ?>/27</td>
                        <td><?php echo absint($row->score_dp); ?>/15</td>
                        <td><?php echo absint($row->score_ap); ?>/24</td>
                        <td><?php echo !empty($row->relance_2j)  ? '<span style="color:#2E7D32">✓</span>' : '<span style="color:#ccc">—</span>'; ?></td>
                        <td><?php echo !empty($row->relance_8j)  ? '<span style="color:#2E7D32">✓</span>' : '<span style="color:#ccc">—</span>'; ?></td>
                        <td><?php echo !empty($row->relance_15j) ? '<span style="color:#2E7D32">✓</span>' : '<span style="color:#ccc">—</span>'; ?></td>
                        <td style="text-align:center;">
                            <?php $active = !isset($row->relance_active) || $row->relance_active; ?>
                            <input type="checkbox"
                                   class="pc-toggle-relance"
                                   data-id="<?php echo absint($row->id); ?>"
                                   <?php checked( $active ); ?>
                                   title="<?php echo $active ? 'Relances actives, cliquez pour désactiver' : 'Relances désactivées, cliquez pour réactiver'; ?>"
                                   style="width:16px;height:16px;cursor:pointer;accent-color:#E8541A;">
                        </td>
                        <td style="white-space:nowrap;">
                            <a href="<?php echo admin_url('admin.php?page=praxicare-results&detail=' . $row->id); ?>" class="button button-small">Détail</a>
                        </td>
                        <td style="text-align:center;">
                            <button class="pc-delete-btn"
                                    data-id="<?php echo absint($row->id); ?>"
                                    data-prenom="<?php echo esc_attr($row->prenom); ?>"
                                    title="Supprimer ce profil"
                                    style="background:none;border:none;cursor:pointer;font-size:16px;opacity:0.4;transition:opacity .2s;"
                                    onmouseover="this.style.opacity='1'"
                                    onmouseout="this.style.opacity='0.4'">🗑</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <script>
        document.querySelectorAll('.pc-toggle-relance').forEach(function(cb) {
            cb.addEventListener('change', function() {
                var id     = this.dataset.id;
                var active = this.checked ? 1 : 0;
                var self   = this;
                self.disabled = true;

                var fd = new FormData();
                fd.append('action', 'praxicare_toggle_relance');
                fd.append('nonce',  '<?php echo wp_create_nonce("praxicare_toggle_relance"); ?>');
                fd.append('id',     id);
                fd.append('active', active);

                fetch('<?php echo admin_url("admin-ajax.php"); ?>', { method: 'POST', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        self.disabled = false;
                        if (!res.success) {
                            self.checked = !self.checked;
                            alert('Erreur : ' + (res.data ? res.data.message : 'inconnue'));
                        } else {
                            self.title = self.checked
                                ? 'Relances actives, cliquez pour désactiver'
                                : 'Relances désactivées, cliquez pour réactiver';
                            self.closest('tr').style.opacity = self.checked ? '1' : '0.5';
                        }
                    })
                    .catch(function() { self.disabled = false; self.checked = !self.checked; });
            });
            if (!cb.checked) cb.closest('tr').style.opacity = '0.5';
        });

        document.querySelectorAll('.pc-delete-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id     = this.dataset.id;
                var prenom = this.dataset.prenom;
                if (!confirm('Supprimer définitivement le profil de ' + prenom + ' ? Cette action est irréversible.')) return;

                var self = this;
                self.disabled = true;
                self.textContent = '…';

                var fd = new FormData();
                fd.append('action', 'praxicare_delete');
                fd.append('nonce',  '<?php echo wp_create_nonce("praxicare_delete"); ?>');
                fd.append('id',     id);

                fetch('<?php echo admin_url("admin-ajax.php"); ?>', { method: 'POST', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        if (res.success) {
                            var row = self.closest('tr');
                            row.style.transition = 'opacity .3s';
                            row.style.opacity = '0';
                            setTimeout(function() { row.remove(); }, 320);
                        } else {
                            self.disabled = false;
                            self.textContent = '🗑';
                            alert('Erreur : ' + (res.data ? res.data.message : 'inconnue'));
                        }
                    })
                    .catch(function() {
                        self.disabled = false;
                        self.textContent = '🗑';
                    });
            });
        });
        </script>
        <?php
    }

    public function render_detail_page( $row ) {
        $niveaux = [
            'bien_etre'           => ['label'=>'Bien-être',            'color'=>'#2E7D32'],
            'engagement_sain'     => ['label'=>'Engagement sain',      'color'=>'#2E7D32'],
            'sous_stimulation'    => ['label'=>'Sous-stimulation',     'color'=>'#F59E0B'],
            'risque_situationnel' => ['label'=>'Risque situationnel',  'color'=>'#F59E0B'],
            'vigilance'           => ['label'=>'Vigilance',            'color'=>'#F59E0B'],
            'risque_cumule'       => ['label'=>'Risque cumulé',        'color'=>'#E65100'],
            'tension_isolee'      => ['label'=>'Tension isolée',       'color'=>'#E65100'],
            'fragilite'           => ['label'=>'Fragilité',            'color'=>'#E65100'],
            'epuisement_interne'  => ['label'=>'Épuisement interne',   'color'=>'#E65100'],
            'souffrance_installee'=> ['label'=>'Souffrance installée', 'color'=>'#E65100'],
            'alarme'              => ['label'=>'Alarme',               'color'=>'#DC2626'],
            'souffrance_averee'   => ['label'=>'Souffrance avérée',    'color'=>'#DC2626'],
            'urgence'             => ['label'=>'Urgence',              'color'=>'#B91C1C'],
        ];
        $n = $niveaux[$row->profil] ?? ['label'=>$row->profil,'color'=>'#999'];
        $profil_data = praxicare_get_profil(
            $row->score_demandes, $row->score_latitude, $row->score_soutien,
            $row->score_ee, $row->score_dp, $row->score_ap
        );
        ?>
        <div class="wrap" id="praxicare-detail">
            <style>
                @media print {
                    #adminmenumain, #wpadminbar, #wpfooter, .no-print { display:none !important; }
                    #wpcontent { margin:0 !important; }
                    .wrap { padding:0 !important; }
                }
            </style>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;" class="no-print">
                <a href="<?php echo admin_url('admin.php?page=praxicare-results'); ?>" class="button">← Retour</a>
                <button onclick="window.print()" class="button button-primary">🖨️ Imprimer / PDF</button>
            </div>

            <div style="max-width:700px;background:#fff;border-radius:12px;padding:32px;border:1px solid #ddd;">
                <div style="text-align:center;margin-bottom:24px;border-bottom:2px solid #E8541A;padding-bottom:16px;">
                    <h1 style="margin:0;color:#1E2A3A;font-size:22px;">PraxiCare — Rapport de diagnostic</h1>
                    <p style="margin:4px 0 0;color:#666;font-size:13px;">Praxis Accompagnement · praxis-accompagnement.com</p>
                </div>

                <table style="width:100%;margin-bottom:24px;font-size:14px;">
                    <tr><td style="color:#666;width:140px;">Prénom</td><td><strong><?php echo esc_html($row->prenom); ?></strong></td></tr>
                    <tr><td style="color:#666;">Email</td><td><?php echo esc_html($row->email); ?></td></tr>
                    <tr><td style="color:#666;">Date du test</td><td><?php echo esc_html(date('d/m/Y à H:i', strtotime($row->created_at))); ?></td></tr>
                </table>

                <div style="background:<?php echo $n['color']; ?>15;border-left:4px solid <?php echo $n['color']; ?>;border-radius:8px;padding:16px 20px;margin-bottom:24px;">
                    <p style="margin:0;font-size:12px;color:#666;text-transform:uppercase;letter-spacing:1px;">Profil</p>
                    <p style="margin:6px 0 0;font-size:18px;font-weight:700;color:#1E2A3A;"><?php echo esc_html($profil_data['emoji'] . ' ' . $profil_data['titre']); ?></p>
                    <p style="margin:8px 0 0;font-size:14px;color:#444;line-height:1.6;"><?php echo esc_html($profil_data['texte']); ?></p>
                </div>

                <h3 style="color:#1E2A3A;margin-bottom:12px;">📈 Modèle de Karasek</h3>
                <?php
                $svgW = 400; $svgH = 280;
                $pad  = 40;
                $cW   = $svgW - $pad * 2;
                $cH   = $svgH - $pad * 2;
                $kx   = round(($row->score_demandes - 9) / 27 * 100);
                $ky   = round(($row->score_latitude  - 9) / 27 * 100);
                $mx_s = $pad + round(13 / 27 * $cW);
                $my_s = $pad + round((1 - 12 / 27) * $cH);
                $px_s = $pad + round($kx / 100 * $cW);
                $py_s = $pad + round((1 - $ky / 100) * $cH);
                $jobStrain_d = $row->score_demandes >= 22 && $row->score_latitude <= 21;
                $isoStrain_d = $jobStrain_d && $row->score_soutien <= 21;
                $actif_d     = $row->score_demandes >= 22 && $row->score_latitude > 21;
                $detendu_d   = $row->score_demandes < 22  && $row->score_latitude > 21;
                $qa = $isoStrain_d ? 'iso' : ($jobStrain_d ? 'strain' : ($actif_d ? 'actif' : ($detendu_d ? 'detendu' : 'passif')));
                $isActive = function($key) use ($qa) { return $key === $qa || ($key === 'strain' && $qa === 'iso'); };
                $qfill = ['passif'=>'rgba(249,168,37,.10)','detendu'=>'rgba(46,125,50,.08)','strain'=>'rgba(198,40,40,.10)','actif'=>'rgba(232,82,26,.08)'];
                $qfillA= ['passif'=>'rgba(249,168,37,.22)','detendu'=>'rgba(46,125,50,.20)','strain'=>'rgba(198,40,40,.22)','actif'=>'rgba(232,82,26,.20)'];
                $qbord = ['passif'=>'rgba(249,168,37,.5)','detendu'=>'rgba(46,125,50,.4)','strain'=>'rgba(198,40,40,.5)','actif'=>'rgba(232,82,26,.4)'];
                $wL = $mx_s - $pad; $wR = $pad + $cW - $mx_s;
                $hT = $my_s - $pad; $hB = $pad + $cH - $my_s;
                ?>
                <div style="margin-bottom:24px;border-radius:8px;border:1px solid #eee;overflow:hidden;">
                <svg viewBox="0 0 <?php echo $svgW; ?> <?php echo $svgH + 18; ?>" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:<?php echo $svgW; ?>px;display:block;">
                  <!-- SVG : Y=0 en haut → haut = latitude haute -->
                  <!-- Haut-gauche = détendu, haut-droite = actif, bas-gauche = passif, bas-droite = strain -->
                  <rect x="<?php echo $pad; ?>" y="<?php echo $pad; ?>" width="<?php echo $wL; ?>" height="<?php echo $hT; ?>" fill="<?php echo $isActive('detendu') ? $qfillA['detendu'] : $qfill['detendu']; ?>" stroke="<?php echo $isActive('detendu') ? $qbord['detendu'] : 'none'; ?>" stroke-width="1.5"/>
                  <rect x="<?php echo $mx_s; ?>" y="<?php echo $pad; ?>" width="<?php echo $wR; ?>" height="<?php echo $hT; ?>" fill="<?php echo $isActive('actif') ? $qfillA['actif'] : $qfill['actif']; ?>" stroke="<?php echo $isActive('actif') ? $qbord['actif'] : 'none'; ?>" stroke-width="1.5"/>
                  <rect x="<?php echo $pad; ?>" y="<?php echo $my_s; ?>" width="<?php echo $wL; ?>" height="<?php echo $hB; ?>" fill="<?php echo $isActive('passif') ? $qfillA['passif'] : $qfill['passif']; ?>" stroke="<?php echo $isActive('passif') ? $qbord['passif'] : 'none'; ?>" stroke-width="1.5"/>
                  <rect x="<?php echo $mx_s; ?>" y="<?php echo $my_s; ?>" width="<?php echo $wR; ?>" height="<?php echo $hB; ?>" fill="<?php echo $isActive('strain') ? $qfillA['strain'] : $qfill['strain']; ?>" stroke="<?php echo $isActive('strain') ? $qbord['strain'] : 'none'; ?>" stroke-width="1.5"/>
                  <line x1="<?php echo $mx_s; ?>" y1="<?php echo $pad; ?>" x2="<?php echo $mx_s; ?>" y2="<?php echo $pad+$cH; ?>" stroke="rgba(0,0,0,.15)" stroke-width="1" stroke-dasharray="4,4"/>
                  <line x1="<?php echo $pad; ?>" y1="<?php echo $my_s; ?>" x2="<?php echo $pad+$cW; ?>" y2="<?php echo $my_s; ?>" stroke="rgba(0,0,0,.15)" stroke-width="1" stroke-dasharray="4,4"/>
                  <text x="<?php echo $pad+6; ?>" y="<?php echo $pad+14; ?>" font-size="10" fill="#1E2A3A" font-weight="<?php echo $isActive('detendu') ? 'bold' : 'normal'; ?>">Travail détendu</text>
                  <text x="<?php echo $pad+6; ?>" y="<?php echo $pad+26; ?>" font-size="9" fill="#6B7280">Peu de charge, bonne autonomie</text>
                  <text x="<?php echo $mx_s+6; ?>" y="<?php echo $pad+14; ?>" font-size="10" fill="#1E2A3A" font-weight="<?php echo $isActive('actif') ? 'bold' : 'normal'; ?>">Travail actif</text>
                  <text x="<?php echo $mx_s+6; ?>" y="<?php echo $pad+26; ?>" font-size="9" fill="#6B7280">Forte charge, bonne autonomie</text>
                  <text x="<?php echo $pad+6; ?>" y="<?php echo $my_s+14; ?>" font-size="10" fill="#1E2A3A" font-weight="<?php echo $isActive('passif') ? 'bold' : 'normal'; ?>">Travail passif</text>
                  <text x="<?php echo $pad+6; ?>" y="<?php echo $my_s+26; ?>" font-size="9" fill="#6B7280">Peu de charge, peu d'autonomie</text>
                  <text x="<?php echo $mx_s+6; ?>" y="<?php echo $my_s+14; ?>" font-size="10" fill="#1E2A3A" font-weight="<?php echo $isActive('strain') ? 'bold' : 'normal'; ?>">Travail sous tension</text>
                  <text x="<?php echo $mx_s+6; ?>" y="<?php echo $my_s+26; ?>" font-size="9" fill="#6B7280">Forte charge, peu d'autonomie</text>
                  <circle cx="<?php echo $px_s; ?>" cy="<?php echo $py_s; ?>" r="9" fill="#E8541A" stroke="#fff" stroke-width="2"/>
                  <text x="<?php echo $pad+$cW/2; ?>" y="<?php echo $svgH+14; ?>" font-size="10" fill="#6B7280" text-anchor="middle">Demandes psychologiques (<?php echo absint($row->score_demandes); ?>/36)</text>
                  <text x="12" y="<?php echo $pad+$cH/2; ?>" font-size="10" fill="#6B7280" text-anchor="middle" transform="rotate(-90,12,<?php echo $pad+$cH/2; ?>)">Latitude (<?php echo absint($row->score_latitude); ?>/36)</text>
                </svg>
                </div>

                <h3 style="color:#1E2A3A;margin:20px 0 12px;">🧠 Inventaire MBI Praxis</h3>
                <?php
                $mbi_dims = [
                    [
                        'label' => 'Épuisement émotionnel',
                        'desc'  => "Sentiment d'être vidé(e) par le travail",
                        'score' => $row->score_ee,
                        'max'   => 27,
                        'sm'    => 10,
                        'se'    => 18,
                    ],
                    [
                        'label' => 'Détachement affectif',
                        'desc'  => 'Distance émotionnelle envers les autres',
                        'score' => $row->score_dp,
                        'max'   => 15,
                        'sm'    => 4,
                        'se'    => 9,
                    ],
                    [
                        'label' => "Manque d'accomplissement",
                        'desc'  => 'Sentiment de ne plus être efficace',
                        'score' => $row->score_ap,
                        'max'   => 24,
                        'sm'    => 9,
                        'se'    => 16,
                    ],
                ];
                ?>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:24px;">
                <?php foreach ( $mbi_dims as $md ) :
                    $pct_m  = round( ($md['score'] / $md['max']) * 100 );
                    $col_m  = $md['score'] <= $md['sm'] ? '#2E7D32' : ($md['score'] <= $md['se'] ? '#E65100' : '#C62828');
                    $bg_m   = $md['score'] <= $md['sm'] ? 'rgba(46,125,50,.07)'  : ($md['score'] <= $md['se'] ? 'rgba(230,81,0,.07)'  : 'rgba(198,40,40,.07)');
                    $brd_m  = $md['score'] <= $md['sm'] ? 'rgba(46,125,50,.25)' : ($md['score'] <= $md['se'] ? 'rgba(230,81,0,.25)' : 'rgba(198,40,40,.25)');
                    $stat_m = $md['score'] <= $md['sm'] ? 'Faible' : ($md['score'] <= $md['se'] ? 'Modéré' : 'Élevé');
                ?>
                <div style="background:<?php echo $bg_m; ?>;border:0.5px solid <?php echo $brd_m; ?>;border-radius:12px;padding:16px;">
                    <p style="font-size:13px;font-weight:600;color:#1E2A3A;margin:0 0 4px;line-height:1.4;"><?php echo esc_html($md['label']); ?></p>
                    <p style="font-size:11px;color:#6B7280;margin:0 0 12px;line-height:1.4;"><?php echo esc_html($md['desc']); ?></p>
                    <div style="background:rgba(0,0,0,.08);border-radius:999px;height:6px;margin-bottom:10px;overflow:hidden;">
                        <div style="background:<?php echo $col_m; ?>;width:<?php echo $pct_m; ?>%;height:6px;border-radius:999px;"></div>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:20px;font-weight:500;color:<?php echo $col_m; ?>;"><?php echo absint($md['score']); ?><span style="font-size:12px;color:#6B7280;font-weight:400;"> / <?php echo $md['max']; ?></span></span>
                        <span style="font-size:12px;font-weight:600;color:<?php echo $col_m; ?>;background:<?php echo str_replace('.07)', '.15)', $bg_m); ?>;border:0.5px solid <?php echo $brd_m; ?>;padding:3px 10px;border-radius:999px;"><?php echo $stat_m; ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>

                <h3 style="color:#1E2A3A;margin:24px 0 12px;">🔍 Analyse détaillée par dimension</h3>
                <?php
                $hasSup_d = true; // la BDD ne stocke pas has_superior ; on assume avec supérieur pour le libellé soutien
                $seuil_soutien_bas = 16; $seuil_soutien_haut = 21; $max_soutien = 32;
                $dims_analyse = [
                    [
                        'titre'  => 'Charge de travail',
                        'score'  => $row->score_demandes,
                        'max'    => 36,
                        'badge'  => $row->score_demandes >= 28 ? 'Très élevée' : ($row->score_demandes >= 22 ? 'Élevée' : ($row->score_demandes >= 15 ? 'Modérée' : 'Faible')),
                        'color'  => $row->score_demandes >= 28 ? '#C62828' : ($row->score_demandes >= 22 ? '#E65100' : '#2E7D32'),
                        'texte'  => $row->score_demandes >= 28
                            ? 'Votre charge de travail est très élevée (' . $row->score_demandes . '/36). Vous travaillez vite, intensément, sous pression constante, avec peu de temps pour souffler. Ce niveau de sollicitation prolongé use les ressources physiques et mentales.'
                            : ($row->score_demandes >= 22
                                ? 'Votre charge de travail est au-dessus du seuil critique (' . $row->score_demandes . '/36). Vous ressentez une pression régulière, des demandes qui s\'accumulent, parfois des interruptions ou des délais difficiles à tenir.'
                                : ($row->score_demandes >= 15
                                    ? 'Votre charge de travail est dans une zone équilibrée (' . $row->score_demandes . '/36). Vous avez une activité soutenue sans être débordé(e). C\'est un bon indicateur, à condition que ce niveau reste stable.'
                                    : 'Votre charge de travail est faible (' . $row->score_demandes . '/36). Peu de pression, peu d\'intensité. Si ce n\'est pas choisi, cela peut générer un sentiment d\'inutilité.')),
                    ],
                    [
                        'titre'  => 'Autonomie et latitude décisionnelle',
                        'score'  => $row->score_latitude,
                        'max'    => 36,
                        'badge'  => $row->score_latitude <= 15 ? 'Très faible' : ($row->score_latitude <= 21 ? 'Faible' : ($row->score_latitude <= 28 ? 'Bonne' : 'Très bonne')),
                        'color'  => $row->score_latitude <= 15 ? '#C62828' : ($row->score_latitude <= 21 ? '#E65100' : '#2E7D32'),
                        'texte'  => $row->score_latitude <= 15
                            ? 'Votre autonomie est très faible (' . $row->score_latitude . '/36). Vous avez très peu de marge pour décider, organiser ou influencer votre travail. Ce niveau de contrôle minimal génère un sentiment d\'impuissance qui s\'installe progressivement.'
                            : ($row->score_latitude <= 21
                                ? 'Votre latitude décisionnelle est en dessous du seuil (' . $row->score_latitude . '/36). Vous avez peu de liberté pour organiser votre travail à votre façon. Ce manque d\'autonomie amplifie les effets d\'une charge élevée.'
                                : ($row->score_latitude <= 28
                                    ? 'Vous disposez d\'une bonne autonomie (' . $row->score_latitude . '/36). Vous pouvez influencer votre travail, prendre des initiatives, développer vos compétences. C\'est un facteur de protection important contre l\'épuisement.'
                                    : 'Votre niveau d\'autonomie est très élevé (' . $row->score_latitude . '/36). Vous avez une grande liberté dans l\'organisation et les décisions, un atout réel.')),
                    ],
                    [
                        'titre'  => 'Soutien social',
                        'score'  => $row->score_soutien,
                        'max'    => $max_soutien,
                        'badge'  => $row->score_soutien <= $seuil_soutien_bas ? 'Très faible' : ($row->score_soutien <= $seuil_soutien_haut ? 'Faible' : 'Bon'),
                        'color'  => $row->score_soutien <= $seuil_soutien_bas ? '#C62828' : ($row->score_soutien <= $seuil_soutien_haut ? '#E65100' : '#2E7D32'),
                        'texte'  => $row->score_soutien <= $seuil_soutien_bas
                            ? 'Votre niveau de soutien est très faible (' . $row->score_soutien . '/' . $max_soutien . '). Ni votre hiérarchie ni vos collègues ne constituent un appui solide. L\'isolement dans un contexte de pression est l\'un des facteurs les plus aggravants.'
                            : ($row->score_soutien <= $seuil_soutien_haut
                                ? 'Votre soutien social est en dessous du seuil (' . $row->score_soutien . '/' . $max_soutien . '). Les relations avec la hiérarchie ou les collègues ne sont pas suffisamment soutenantes. L\'absence de relais humain finit par peser.'
                                : 'Vous bénéficiez d\'un bon soutien social (' . $row->score_soutien . '/' . $max_soutien . '). Votre manager et/ou vos collègues constituent un appui réel. C\'est un facteur de protection majeur.'),
                    ],
                    [
                        'titre'  => 'Épuisement émotionnel',
                        'score'  => $row->score_ee,
                        'max'    => 27,
                        'badge'  => $row->score_ee >= 19 ? 'Élevé' : ($row->score_ee >= 11 ? 'Modéré' : 'Faible'),
                        'color'  => $row->score_ee >= 19 ? '#C62828' : ($row->score_ee >= 11 ? '#E65100' : '#2E7D32'),
                        'texte'  => $row->score_ee >= 22
                            ? 'Votre épuisement émotionnel est très élevé (' . $row->score_ee . '/27). Vous vous sentez à bout physiquement et émotionnellement. Ce niveau d\'usure ne se rattrape pas avec un week-end. Il signale un besoin de récupération profond et durable.'
                            : ($row->score_ee >= 19
                                ? 'Votre épuisement émotionnel dépasse le seuil critique (' . $row->score_ee . '/27). Vous ressentez une fatigue persistante que le repos habituel ne suffit plus à effacer. Votre réserve émotionnelle est sérieusement entamée.'
                                : ($row->score_ee >= 11
                                    ? 'Votre épuisement émotionnel est modéré (' . $row->score_ee . '/27). Vous ressentez de la fatigue, des moments où vous êtes à plat, sans que ce soit constant. C\'est une zone de vigilance à ne pas ignorer.'
                                    : 'Votre niveau d\'épuisement émotionnel est faible (' . $row->score_ee . '/27). Vous ne montrez pas de signe d\'usure significative sur le plan émotionnel.')),
                    ],
                    [
                        'titre'  => 'Détachement affectif',
                        'score'  => $row->score_dp,
                        'max'    => 15,
                        'badge'  => $row->score_dp >= 10 ? 'Élevé' : ($row->score_dp >= 5 ? 'Modéré' : 'Faible'),
                        'color'  => $row->score_dp >= 10 ? '#C62828' : ($row->score_dp >= 5 ? '#E65100' : '#2E7D32'),
                        'texte'  => $row->score_dp >= 10
                            ? 'Votre détachement affectif est élevé (' . $row->score_dp . '/15). Vous avez développé une distance notable envers les personnes avec qui vous travaillez. C\'est souvent une protection inconsciente contre la surcharge émotionnelle, mais elle peut renforcer l\'isolement.'
                            : ($row->score_dp >= 5
                                ? 'Votre détachement affectif est modéré (' . $row->score_dp . '/15). Vous remarquez parfois une certaine froideur dans vos interactions. C\'est une zone à surveiller, surtout si elle s\'accompagne d\'épuisement.'
                                : 'Votre niveau de détachement affectif est faible (' . $row->score_dp . '/15). Vous restez impliqué(e) émotionnellement dans vos relations professionnelles. C\'est un signe de bon ancrage relationnel.'),
                    ],
                    [
                        'titre'  => "Sentiment d'accomplissement",
                        'score'  => $row->score_ap,
                        'max'    => 24,
                        'badge'  => $row->score_ap >= 17 ? 'Manque élevé' : ($row->score_ap >= 10 ? 'Manque modéré' : 'Bon'),
                        'color'  => $row->score_ap >= 17 ? '#C62828' : ($row->score_ap >= 10 ? '#E65100' : '#2E7D32'),
                        'texte'  => $row->score_ap >= 17
                            ? 'Votre sentiment d\'accomplissement est très altéré (' . $row->score_ap . '/24 de manque). Vous avez du mal à percevoir votre utilité et à vous sentir efficace. Ce n\'est pas un manque de compétences, c\'est souvent le signe que le contexte ne vous permet plus d\'exprimer ce dont vous êtes capable.'
                            : ($row->score_ap >= 10
                                ? 'Votre sentiment d\'accomplissement est partiellement altéré (' . $row->score_ap . '/24 de manque). Vous avez des moments de doute sur votre efficacité ou l\'utilité de votre travail. Ce n\'est pas constant, mais c\'est un signal qui mérite attention.'
                                : 'Votre sentiment d\'accomplissement est préservé (' . $row->score_ap . '/24 de manque). Vous avez globalement le sentiment de faire un travail utile et d\'être efficace. C\'est un facteur de résilience important.'),
                    ],
                ];
                foreach ( $dims_analyse as $d ) :
                    $pct_d = round( ($d['score'] / $d['max']) * 100 );
                    $col_d = $d['color'];
                    $bg_d  = $col_d === '#C62828' ? 'rgba(198,40,40,.06)' : ($col_d === '#E65100' ? 'rgba(230,81,0,.06)' : 'rgba(46,125,50,.06)');
                    $brd_d = $col_d === '#C62828' ? 'rgba(198,40,40,.2)'  : ($col_d === '#E65100' ? 'rgba(230,81,0,.2)'  : 'rgba(46,125,50,.2)');
                ?>
                <div style="background:<?php echo $bg_d; ?>;border:1px solid <?php echo $brd_d; ?>;border-radius:10px;padding:14px 16px;margin-bottom:10px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <span style="font-size:13px;font-weight:600;color:#1E2A3A;"><?php echo esc_html($d['titre']); ?></span>
                        <span style="font-size:11px;font-weight:600;color:<?php echo $col_d; ?>;background:<?php echo $bg_d; ?>;border:1px solid <?php echo $brd_d; ?>;padding:2px 10px;border-radius:999px;"><?php echo esc_html($d['badge']); ?></span>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                        <div style="flex:1;background:rgba(0,0,0,.08);border-radius:999px;height:6px;overflow:hidden;">
                            <div style="background:<?php echo $col_d; ?>;width:<?php echo $pct_d; ?>%;height:6px;border-radius:999px;"></div>
                        </div>
                        <span style="font-size:13px;font-weight:600;color:<?php echo $col_d; ?>;white-space:nowrap;"><?php echo absint($d['score']); ?> / <?php echo $d['max']; ?></span>
                    </div>
                    <p style="margin:0;font-size:13px;color:#444;line-height:1.6;"><?php echo esc_html($d['texte']); ?></p>
                </div>
                <?php endforeach; ?>

                <h3 style="color:#1E2A3A;margin:24px 0 12px;">📋 Préconisations</h3>
                <ul style="padding-left:20px;font-size:14px;line-height:1.8;color:#333;">
                    <?php foreach ( $profil_data['preconisations'] as $p ) :
                        $texte = is_array($p) ? $p['texte'] : $p;
                    ?>
                        <li><?php echo esc_html($texte); ?></li>
                    <?php endforeach; ?>
                </ul>

                <p style="margin-top:24px;padding-top:16px;border-top:1px solid #eee;font-size:12px;color:#999;text-align:center;">
                    ⚕️ Ce rapport est un outil d'aide à la prise de conscience. Il ne constitue pas un diagnostic médical.
                </p>
            </div>
        </div>
        <?php
    }

    public function ajax_delete() {
        check_ajax_referer( 'praxicare_delete', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Accès refusé' );

        $id = absint( $_POST['id'] ?? 0 );
        if ( ! $id ) {
            wp_send_json_error( array( 'message' => 'ID invalide.' ) );
        }

        global $wpdb;
        $table  = $wpdb->prefix . 'praxicare_results';
        $result = $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );

        if ( $result !== false && $result > 0 ) {
            wp_send_json_success( array( 'message' => 'Profil supprimé.' ) );
        } else {
            wp_send_json_error( array( 'message' => 'Suppression échouée ou ID introuvable.' ) );
        }
    }

    public function ajax_toggle_relance() {
        check_ajax_referer( 'praxicare_toggle_relance', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Accès refusé' );

        $id     = absint( $_POST['id'] ?? 0 );
        $active = intval( $_POST['active'] ?? 1 ) ? 1 : 0;

        if ( ! $id ) {
            wp_send_json_error( array( 'message' => 'ID invalide.' ) );
        }

        global $wpdb;
        $table  = $wpdb->prefix . 'praxicare_results';
        $result = $wpdb->update(
            $table,
            array( 'relance_active' => $active ),
            array( 'id' => $id ),
            array( '%d' ),
            array( '%d' )
        );

        if ( $result !== false ) {
            wp_send_json_success( array( 'active' => $active ) );
        } else {
            wp_send_json_error( array( 'message' => 'Mise à jour BDD échouée.' ) );
        }
    }

    public function ajax_test_relance() {
        check_ajax_referer( 'praxicare_test_relance', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Accès refusé' );

        $jours  = intval( $_POST['jours'] ?? 2 );
        $niveau = sanitize_text_field( $_POST['niveau'] ?? 'jaune' );
        $email  = sanitize_email( $_POST['email'] ?? '' );

        if ( ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => 'Email invalide.' ) );
        }

        // Construire un faux row pour simuler la relance
        $fake_row = (object) array(
            'prenom'         => 'Test',
            'email'          => $email,
            'score_demandes' => 22,
            'score_latitude' => 18,
            'score_soutien'  => 18,
            'score_ee'       => 17,
            'score_dp'       => 5,
            'score_ap'       => 10,
            'profil'         => 'risque_cumule',
        );

        // Forcer le niveau en surchargeant la fonction de profil via un profil artificiel
        $fake_profil = praxicare_get_profil(
            $fake_row->score_demandes, $fake_row->score_latitude, $fake_row->score_soutien,
            $fake_row->score_ee, $fake_row->score_dp, $fake_row->score_ap
        );
        // Injecter le niveau souhaité pour le test
        $fake_profil['niveau'] = $niveau;

        $admin_email = get_option( 'praxicare_admin_email', get_option( 'admin_email' ) );
        $rdv_url     = 'https://calendly.com/alex-fradin/15min';
        $site_name   = get_bloginfo( 'name' );

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'Reply-To: ' . $site_name . ' <' . $admin_email . '>',
        );

        switch ( $jours ) {
            case 2:
                $sujet = '[TEST J+2] ' . praxicare_sujet_j2( 'Test', $niveau );
                $body  = praxicare_body_j2( 'Test', $fake_profil, $rdv_url, $site_name );
                break;
            case 8:
                $sujet = '[TEST J+8] ' . praxicare_sujet_j8( 'Test', $niveau );
                $body  = praxicare_body_j8( 'Test', $fake_profil, $rdv_url, $site_name );
                break;
            case 15:
                $sujet = '[TEST J+15] ' . praxicare_sujet_j15( 'Test', $niveau );
                $body  = praxicare_body_j15( 'Test', $fake_profil, $rdv_url, $site_name );
                break;
            default:
                wp_send_json_error( array( 'message' => 'Numéro de relance invalide.' ) );
                return;
        }

        $sent = wp_mail( $email, $sujet, $body, $headers );
        if ( $sent ) {
            wp_send_json_success( array( 'message' => 'Email de test envoyé à ' . $email ) );
        } else {
            wp_send_json_error( array( 'message' => 'Échec de l\'envoi. Vérifiez la configuration SMTP.' ) );
        }
    }

    public function render_relances_page() {
        $niveaux = array(
            'vert'     => array( 'label' => 'Bien-être / Engagement sain', 'color' => '#2E7D32' ),
            'jaune'    => array( 'label' => 'Vigilance / Sous-stimulation', 'color' => '#F59E0B' ),
            'orange'   => array( 'label' => 'Fragilité / Risque cumulé / Tension',  'color' => '#E65100' ),
            'rouge'    => array( 'label' => 'Alarme / Souffrance avérée', 'color' => '#DC2626' ),
            'critique' => array( 'label' => 'Urgence critique', 'color' => '#B91C1C' ),
        );

        $emails = array(
            '2j'  => array( 'label' => 'J+2 — Ancrage émotionnel', 'desc' => 'Valider le vécu, créer le lien humain. Pas de vente sauf orange/rouge/critique.' ),
            '8j'  => array( 'label' => 'J+8 — Projection cognitive', 'desc' => 'Deux chemins dans 3 mois : continuer comme avant vs agir.' ),
            '15j' => array( 'label' => 'J+15 — Passage à l\'acte', 'desc' => 'Preuve sociale douce, lever les résistances, CTA final.' ),
        );

        // Sujets par défaut (hardcodés dans relance-functions.php)
        $default_sujets = array(
            '2j' => array(
                'vert'     => 'Prénom, une question pour vous depuis avant-hier…',
                'jaune'    => 'Prénom, est-ce que ça vous a parlé ?',
                'orange'   => 'Prénom, ce que vous avez mis en mots méritait de l\'être',
                'rouge'    => 'Prénom, merci d\'avoir pris ce temps pour vous',
                'critique' => 'Prénom, je pense à vous',
            ),
            '8j' => array(
                'vert'     => 'Prénom, dans 3 mois, où en serez-vous ?',
                'jaune'    => 'Prénom, deux chemins, un choix',
                'orange'   => 'Prénom, imaginez dans 3 mois…',
                'rouge'    => 'Prénom, dans 3 mois : la même chose, ou autre chose ?',
                'critique' => 'Prénom, une image pour vous aider à décider',
            ),
            '15j' => array(
                'vert'     => 'Prénom, une dernière chose avant de vous laisser tranquille',
                'jaune'    => 'Prénom, ce que font les gens qui changent vraiment',
                'orange'   => 'Prénom, il y a 15 jours vous avez mis des mots dessus',
                'rouge'    => 'Prénom, 15 jours. Est-ce que quelque chose a changé ?',
                'critique' => 'Prénom, je voulais juste prendre de vos nouvelles',
            ),
        );

        if ( isset( $_POST['praxicare_relances_save'] ) ) {
            check_admin_referer( 'praxicare_relances_save' );
            foreach ( array( '2j', '8j', '15j' ) as $j ) {
                foreach ( array_keys( $niveaux ) as $n ) {
                    $key_sujet = 'praxicare_sujet_' . $j . '_' . $n;
                    $key_intro = 'praxicare_intro_' . $j . '_' . $n;
                    if ( isset( $_POST[ $key_sujet ] ) ) {
                        update_option( $key_sujet, sanitize_text_field( wp_unslash( $_POST[ $key_sujet ] ) ) );
                    }
                    if ( isset( $_POST[ $key_intro ] ) ) {
                        update_option( $key_intro, sanitize_textarea_field( wp_unslash( $_POST[ $key_intro ] ) ) );
                    }
                }
            }
            echo '<div class="notice notice-success"><p>✅ Textes de relance enregistrés.</p></div>';
        }
        ?>
        <div class="wrap">
            <h1 style="display:flex;align-items:center;gap:12px;">📧 PraxiCare — Emails de relance</h1>
            <p style="color:#666;max-width:700px;">3 emails automatiques sont envoyés à chaque utilisateur après son test. Chaque email a 5 versions selon le niveau de profil. Vous pouvez personnaliser les sujets et l'intro de chaque version, ou tester l'envoi.</p>

            <div style="display:flex;gap:16px;margin:20px 0 0;flex-wrap:wrap;">
                <?php foreach ( $emails as $j => $e ) : ?>
                <div style="background:#fff;border:1px solid #dde;border-radius:8px;padding:12px 16px;flex:1;min-width:200px;">
                    <p style="margin:0;font-weight:700;color:#002345;font-size:14px;"><?php echo esc_html( $e['label'] ); ?></p>
                    <p style="margin:4px 0 0;font-size:12px;color:#888;"><?php echo esc_html( $e['desc'] ); ?></p>
                </div>
                <?php endforeach; ?>
            </div>

            <form method="post" style="margin-top:24px;">
                <?php wp_nonce_field( 'praxicare_relances_save' ); ?>
                <input type="hidden" name="praxicare_relances_save" value="1">

                <?php foreach ( $emails as $j => $email_info ) : ?>
                <h2 style="margin:32px 0 8px;padding-bottom:8px;border-bottom:2px solid #E8541A;color:#002345;"><?php echo esc_html( $email_info['label'] ); ?></h2>

                <table class="widefat" style="border-radius:8px;overflow:hidden;margin-bottom:8px;">
                    <thead>
                        <tr style="background:#002345;">
                            <th style="color:#fff;width:180px;">Niveau de profil</th>
                            <th style="color:#fff;">Objet de l'email <small style="font-weight:400;opacity:.7;">(Prénom sera remplacé automatiquement)</small></th>
                            <th style="color:#fff;">Intro / Premier paragraphe <small style="font-weight:400;opacity:.7;">(optionnel, laissez vide pour le texte par défaut)</small></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ( $niveaux as $n => $niv ) :
                        $key_sujet   = 'praxicare_sujet_' . $j . '_' . $n;
                        $key_intro   = 'praxicare_intro_' . $j . '_' . $n;
                        $val_sujet   = get_option( $key_sujet, '' );
                        $val_intro   = get_option( $key_intro, '' );
                        $placeholder_sujet = $default_sujets[ $j ][ $n ] ?? '';
                    ?>
                    <tr>
                        <td style="vertical-align:top;padding-top:12px;">
                            <span style="display:inline-block;background:<?php echo esc_attr($niv['color']); ?>;color:#fff;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;"><?php echo esc_html($niv['label']); ?></span>
                        </td>
                        <td style="vertical-align:top;">
                            <input type="text" name="<?php echo esc_attr($key_sujet); ?>"
                                   value="<?php echo esc_attr($val_sujet); ?>"
                                   placeholder="<?php echo esc_attr($placeholder_sujet); ?>"
                                   class="large-text" style="border-radius:6px;">
                        </td>
                        <td style="vertical-align:top;">
                            <textarea name="<?php echo esc_attr($key_intro); ?>"
                                      rows="3" class="large-text"
                                      placeholder="Texte par défaut si vide…"
                                      style="border-radius:6px;font-size:13px;"><?php echo esc_textarea($val_intro); ?></textarea>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endforeach; ?>

                <?php submit_button( '💾 Enregistrer tous les textes de relance' ); ?>
            </form>

            <hr style="margin:32px 0;">

            <h2 style="color:#002345;">🧪 Tester l'envoi d'une relance</h2>
            <p style="color:#666;">Envoie un email de test immédiatement à l'adresse de votre choix, avec le niveau et la relance souhaités. Le prénom sera "Test".</p>

            <div style="background:#fff;border:1px solid #dde;border-radius:8px;padding:20px;max-width:600px;">
                <table class="form-table" style="margin:0;">
                    <tr>
                        <th style="width:140px;">Email destinataire</th>
                        <td><input type="email" id="pc-test-email" class="regular-text" placeholder="votre@email.com" value="<?php echo esc_attr( get_option('praxicare_admin_email', get_option('admin_email')) ); ?>"></td>
                    </tr>
                    <tr>
                        <th>Relance</th>
                        <td>
                            <select id="pc-test-jours">
                                <option value="2">J+2 — Ancrage émotionnel</option>
                                <option value="8">J+8 — Projection cognitive</option>
                                <option value="15">J+15 — Passage à l'acte</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>Niveau de profil</th>
                        <td>
                            <select id="pc-test-niveau">
                                <?php foreach ( $niveaux as $n => $niv ) : ?>
                                <option value="<?php echo esc_attr($n); ?>"><?php echo esc_html($niv['label']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <div style="margin-top:16px;">
                    <button id="pc-test-btn" class="button button-primary">📤 Envoyer le test</button>
                    <span id="pc-test-result" style="margin-left:12px;font-size:13px;"></span>
                </div>
            </div>
        </div>

        <script>
        document.getElementById('pc-test-btn').addEventListener('click', function() {
            var btn    = this;
            var email  = document.getElementById('pc-test-email').value;
            var jours  = document.getElementById('pc-test-jours').value;
            var niveau = document.getElementById('pc-test-niveau').value;
            var result = document.getElementById('pc-test-result');

            btn.disabled = true;
            btn.textContent = 'Envoi…';
            result.textContent = '';
            result.style.color = '#666';

            var data = new FormData();
            data.append('action', 'praxicare_test_relance');
            data.append('nonce',  '<?php echo wp_create_nonce("praxicare_test_relance"); ?>');
            data.append('email',  email);
            data.append('jours',  jours);
            data.append('niveau', niveau);

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', { method: 'POST', body: data })
                .then(r => r.json())
                .then(function(res) {
                    btn.disabled = false;
                    btn.textContent = '📤 Envoyer le test';
                    if (res.success) {
                        result.textContent = '✅ ' + res.data.message;
                        result.style.color = '#2E7D32';
                    } else {
                        result.textContent = '❌ ' + res.data.message;
                        result.style.color = '#DC2626';
                    }
                })
                .catch(function() {
                    btn.disabled = false;
                    btn.textContent = '📤 Envoyer le test';
                    result.textContent = '❌ Erreur réseau';
                    result.style.color = '#DC2626';
                });
        });
        </script>
        <?php
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>PraxiCare — Réglages SMTP</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'praxicare_settings' ); ?>
                <table class="form-table">
                    <tr><th>Hôte SMTP</th><td><input type="text" name="praxicare_smtp_host" value="<?php echo esc_attr( get_option('praxicare_smtp_host', 'ssl0.ovh.net') ); ?>" class="regular-text"></td></tr>
                    <tr><th>Utilisateur SMTP</th><td><input type="text" name="praxicare_smtp_user" value="<?php echo esc_attr( get_option('praxicare_smtp_user', 'contact@praxis-accompagnement.com') ); ?>" class="regular-text"></td></tr>
                    <tr><th>Mot de passe SMTP</th><td><input type="password" name="praxicare_smtp_pass" value="<?php echo esc_attr( get_option('praxicare_smtp_pass', '') ); ?>" class="regular-text"></td></tr>
                    <tr><th>Port</th><td><input type="number" name="praxicare_smtp_port" value="<?php echo esc_attr( get_option('praxicare_smtp_port', '465') ); ?>" class="small-text"></td></tr>
                    <tr><th>Sécurité</th><td>
                        <select name="praxicare_smtp_secure">
                            <?php foreach ( ['ssl','tls','none'] as $opt ) : ?>
                                <option value="<?php echo $opt; ?>" <?php selected( get_option('praxicare_smtp_secure','ssl'), $opt ); ?>><?php echo strtoupper($opt); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td></tr>
                    <tr><th>Email admin (copie à chaque test)</th><td><input type="email" name="praxicare_admin_email" value="<?php echo esc_attr( get_option('praxicare_admin_email', get_option('admin_email')) ); ?>" class="regular-text"></td></tr>
                </table>
                <?php submit_button( 'Enregistrer' ); ?>
            </form>
        </div>
        <?php
    }

    public function enqueue_assets() {
        global $post;
        if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'praxicare' ) ) {
            wp_enqueue_style(
                'praxicare-style',
                PRAXICARE_URL . 'assets/css/style.css',
                array(),
                PRAXICARE_VERSION
            );
            wp_enqueue_script(
                'chartjs',
                'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
                array(),
                '4.4.0',
                true
            );
            wp_enqueue_script(
                'praxicare-main',
                PRAXICARE_URL . 'assets/js/main.js',
                array( 'chartjs' ),
                PRAXICARE_VERSION,
                true
            );
            wp_localize_script( 'praxicare-main', 'praxicare_ajax', array(
                'url'   => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'praxicare_nonce' ),
            ) );
        }
    }

    public function render_shortcode( $atts ) {
        ob_start();
        include PRAXICARE_PATH . 'templates/page-intro.php';
        return ob_get_clean();
    }
}
