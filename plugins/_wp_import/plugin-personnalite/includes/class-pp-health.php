<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Vérifications de santé du plugin — lancées à l'activation et via admin.
 * Détecte les problèmes avant qu'ils n'arrivent en production.
 */
class PP_Health {

    public static function check_all() {
        return array(
            'db'        => self::check_db(),
            'mail'      => self::check_mail_config(),
            'uploads'   => self::check_uploads_dir(),
            'cron'      => self::check_cron(),
            'php'       => self::check_php(),
            'options'   => self::check_required_options(),
        );
    }

    public static function check_db() {
        global $wpdb;
        $tables = array(
            $wpdb->prefix . PP_DB::TABLE,
            $wpdb->prefix . PP_Batch::TABLE,
            $wpdb->prefix . PP_Codes::TABLE,
            $wpdb->prefix . PP_Logger::TABLE,
        );
        $missing = array();
        foreach ($tables as $t) {
            $exists = $wpdb->get_var("SHOW TABLES LIKE '{$t}'");
            if (!$exists) $missing[] = $t;
        }
        if ($missing) {
            return array('ok'=>false, 'msg'=>'Tables manquantes : ' . implode(', ',$missing),
                         'action'=>'Désactivez et réactivez le plugin.');
        }
        // Vérifier les index critiques
        $idx = $wpdb->get_results("SHOW INDEX FROM {$wpdb->prefix}" . PP_DB::TABLE);
        $idx_names = array_column((array)$idx, 'Key_name');
        if (!in_array('email', $idx_names) || !in_array('date_soumis', $idx_names)) {
            return array('ok'=>false, 'msg'=>'Index manquants sur pp_resultats.',
                         'action'=>'Désactivez et réactivez le plugin pour recréer les index.');
        }
        return array('ok'=>true, 'msg'=>'Toutes les tables et index sont présents.');
    }

    public static function check_mail_config() {
        $admin = get_option('pp_admin_email','');
        if (!$admin || !is_email($admin)) {
            return array('ok'=>false,
                'msg'=>'Email admin non configuré ou invalide.',
                'action'=>'Renseignez un email valide dans Réglages → Identité & Contact.');
        }
        // Tester wp_mail avec une adresse fictive (juste la configuration, pas l'envoi réel)
        $smtp_ok = function_exists('wp_mail');
        return array('ok'=>$smtp_ok,
            'msg'=>$smtp_ok ? 'wp_mail disponible — email admin : ' . $admin : 'wp_mail indisponible.',
            'action'=>$smtp_ok ? null : 'Vérifiez votre configuration SMTP WordPress.');
    }

    public static function check_uploads_dir() {
        $upload = wp_upload_dir();
        if ($upload['error']) {
            return array('ok'=>false, 'msg'=>'Dossier uploads inaccessible : ' . $upload['error'],
                         'action'=>'Vérifiez les permissions du dossier wp-content/uploads.');
        }
        $pp_dir = trailingslashit($upload['basedir']) . 'pp-rapports/';
        if (file_exists($pp_dir) && !is_writable($pp_dir)) {
            return array('ok'=>false, 'msg'=>'Dossier pp-rapports/ non accessible en écriture.',
                         'action'=>'chmod 755 ' . $pp_dir);
        }
        return array('ok'=>true, 'msg'=>'Dossier uploads accessible.');
    }

    public static function check_cron() {
        $disabled = defined('DISABLE_WP_CRON') && DISABLE_WP_CRON;
        $relances  = wp_next_scheduled( PP_Relances::CRON_HOOK );
        $purge     = wp_next_scheduled( 'pp_purge_expired' );

        if ($disabled && !$relances) {
            return array('ok'=>false,
                'msg'=>'WP-Cron désactivé et aucun cron système configuré.',
                'action'=>'Configurez un cron système : */15 * * * * wget -q -O - ' . home_url('/?doing_wp_cron') . ' > /dev/null 2>&1');
        }
        if (!$relances) {
            return array('ok'=>false, 'msg'=>'Cron relances non planifié.',
                         'action'=>'Désactivez et réactivez le plugin.');
        }
        $next = wp_date( 'd/m H:i', $relances );
        return array('ok'=>true, 'msg'=>"Cron actif. Prochain passage relances : {$next}");
    }

    public static function check_php() {
        $issues = array();
        if (!function_exists('random_bytes'))  $issues[] = 'random_bytes() manquant (PHP < 7.0)';
        if (!function_exists('json_encode'))   $issues[] = 'json_encode() manquant';
        if (!extension_loaded('mysqli'))       $issues[] = 'Extension mysqli manquante';
        if (ini_get('max_execution_time') < 30 && ini_get('max_execution_time') != 0)
            $issues[] = 'max_execution_time trop court (' . ini_get('max_execution_time') . 's recommandé : 60s)';
        if ($issues) {
            return array('ok'=>false, 'msg'=>implode(', ', $issues), 'action'=>'Contactez votre hébergeur.');
        }
        return array('ok'=>true, 'msg'=>'PHP ' . PHP_VERSION . ' — toutes les extensions présentes.');
    }

    public static function check_required_options() {
        $missing = array();
        if (!get_option('pp_rdv_url'))    $missing[] = 'URL rendez-vous';
        if (!get_option('pp_politique_url')) $missing[] = 'URL politique confidentialité';
        if ($missing) {
            return array('ok'=>false,
                'msg'=>'Options non configurées : ' . implode(', ', $missing),
                'action'=>'Renseignez-les dans Réglages.');
        }
        return array('ok'=>true, 'msg'=>'Toutes les options obligatoires sont configurées.');
    }

    /** Vue admin — affiche le rapport de santé. */
    public static function render_admin() {
        $checks = self::check_all();
        $all_ok = !in_array(false, array_column(array_column($checks,'ok' ?? true), null), true);
        $all_ok = array_reduce($checks, fn($c,$v) => $c && ($v['ok']??true), true);
        ?>
        <div class="wrap" style="max-width:760px;">
          <h1>🩺 État du plugin</h1>
          <?php if ($all_ok): ?>
          <div style="background:#dcfce7;border:1.5px solid #86efac;border-radius:10px;padding:14px 20px;margin-bottom:20px;color:#16A34A;font-weight:700;">
            ✅ Tous les contrôles sont au vert — le plugin est prêt pour la production.
          </div>
          <?php else: ?>
          <div style="background:#fef2f2;border:1.5px solid #fca5a5;border-radius:10px;padding:14px 20px;margin-bottom:20px;color:#DC2626;font-weight:700;">
            ⚠️ Des problèmes ont été détectés — corrigez-les avant la mise en production.
          </div>
          <?php endif; ?>

          <div style="display:grid;gap:12px;">
          <?php foreach ($checks as $key => $r):
            $ok   = $r['ok'] ?? true;
            $icon = $ok ? '✅' : '❌';
            $bg   = $ok ? '#f0fdf4' : '#fef2f2';
            $bd   = $ok ? '#86efac' : '#fca5a5';
            $labels = array('db'=>'Base de données','mail'=>'Email','uploads'=>'Dossiers',
                            'cron'=>'Tâches planifiées','php'=>'Environnement PHP','options'=>'Configuration');
          ?>
          <div style="background:<?php echo $bg; ?>;border:1.5px solid <?php echo $bd; ?>;border-radius:10px;padding:14px 18px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
              <div>
                <div style="font-weight:700;font-size:14px;color:#1e293b;margin-bottom:3px;">
                  <?php echo $icon; ?> <?php echo $labels[$key] ?? $key; ?>
                </div>
                <div style="font-size:13px;color:#475569;"><?php echo esc_html($r['msg']); ?></div>
                <?php if (!$ok && !empty($r['action'])): ?>
                <div style="font-size:12px;color:#DC2626;margin-top:4px;font-weight:600;">
                  → <?php echo esc_html($r['action']); ?>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
          </div>

          <!-- Logs récents -->
          <h2 style="margin:28px 0 12px;font-size:15px;color:#1e293b;">Logs récents</h2>
          <?php
          $logs = PP_Logger::get_recent('', 50);
          if ($logs):
          $level_colors = array('debug'=>'#94a3b8','info'=>'#4F46E5','warning'=>'#D97706','error'=>'#DC2626','critical'=>'#7f1d1d');
          ?>
          <div style="background:#1e293b;border-radius:10px;padding:16px;font-family:monospace;font-size:12px;max-height:320px;overflow-y:auto;">
            <?php foreach ($logs as $log):
              $col = $level_colors[$log->level] ?? '#94a3b8';
            ?>
            <div style="margin-bottom:4px;line-height:1.5;">
              <span style="color:#64748b;"><?php echo date('d/m H:i:s',strtotime($log->created_at)); ?></span>
              <span style="color:<?php echo $col; ?>;font-weight:700;margin:0 6px;">[<?php echo strtoupper($log->level); ?>]</span>
              <span style="color:#e2e8f0;"><?php echo esc_html($log->context); ?></span>
              <span style="color:#94a3b8;"> — </span>
              <span style="color:#f1f5f9;"><?php echo esc_html($log->message); ?></span>
            </div>
            <?php endforeach; ?>
          </div>
          <div style="margin-top:10px;">
            <a href="<?php echo esc_url(add_query_arg(array('page'=>'pp-health','pp_clear_logs'=>1,'_wpnonce'=>wp_create_nonce('pp_clear_logs')), admin_url('admin.php'))); ?>"
               class="button" onclick="return confirm('Effacer tous les logs ?');">Vider les logs</a>
          </div>
          <?php else: ?>
          <p style="color:#94a3b8;font-size:13px;">Aucun log enregistré.</p>
          <?php endif; ?>
        </div>
        <?php
        // Traiter l'effacement des logs
        if (isset($_GET['pp_clear_logs']) && check_admin_referer('pp_clear_logs')) {
            PP_Logger::clear();
            echo '<script>window.location.href=window.location.href.split("?")[0]+"?page=pp-health";</script>';
        }
    }
}
