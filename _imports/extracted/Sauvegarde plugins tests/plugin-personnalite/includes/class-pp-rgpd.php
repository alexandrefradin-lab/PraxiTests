<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * RGPD — Droit à l'effacement, export des données, délai de conservation.
 * URL publique : /supprimer-mes-donnees/[token]
 * URL publique : /mes-donnees/[token] (export JSON)
 */
class PP_RGPD {

    public static function init() {
        add_action( 'init',              array(__CLASS__, 'add_rewrite') );
        add_filter( 'query_vars',        array(__CLASS__, 'add_query_vars') );
        add_action( 'template_redirect', array(__CLASS__, 'handle_requests') );
        add_action( 'wp_ajax_pp_demande_suppression',        array(__CLASS__, 'handle_demande') );
        add_action( 'wp_ajax_nopriv_pp_demande_suppression', array(__CLASS__, 'handle_demande') );
        // Purge automatique des données après le délai de conservation
        add_action( 'pp_purge_expired', array(__CLASS__, 'purge_expired') );
        if ( ! wp_next_scheduled('pp_purge_expired') ) {
            wp_schedule_event( time(), 'daily', 'pp_purge_expired' );
        }
    }

    public static function add_rewrite() {
        add_rewrite_rule('^supprimer-mes-donnees/([a-f0-9]{32})/?$',
            'index.php?pp_delete_token=$matches[1]', 'top');
        add_rewrite_rule('^mes-donnees/([a-f0-9]{32})/?$',
            'index.php?pp_export_token=$matches[1]', 'top');
    }

    public static function add_query_vars($vars) {
        $vars[] = 'pp_delete_token';
        $vars[] = 'pp_export_token';
        return $vars;
    }

    public static function handle_requests() {
        $del = get_query_var('pp_delete_token', '');
        $exp = get_query_var('pp_export_token', '');
        if ( $del ) { self::render_delete_page($del); exit; }
        if ( $exp ) { self::render_export($exp);      exit; }
    }

    /** Page de confirmation de suppression. */
    private static function render_delete_page($token) {
        $row = PP_DB::get_by_token($token);
        if ( ! $row ) {
            wp_die('Profil introuvable ou déjà supprimé.', 'Non trouvé', array('response'=>404));
        }

        $nonce    = wp_create_nonce('pp_nonce');
        $ajax_url = admin_url('admin-ajax.php');
        $done     = isset($_GET['done']);

        get_header();
        ?>
        <div style="max-width:520px;margin:60px auto;padding:0 16px 80px;font-family:'Segoe UI',system-ui,sans-serif;text-align:center;">
          <?php if ($done) : ?>
          <div style="font-size:56px;margin-bottom:16px;">✅</div>
          <h1 style="font-size:22px;font-weight:800;color:#1e293b;margin-bottom:12px;">Données supprimées</h1>
          <p style="color:#64748b;font-size:15px;line-height:1.7;">
            Vos données personnelles et résultats ont été définitivement supprimés de nos systèmes.
            Aucune relance ne vous sera envoyée.
          </p>
          <?php else : ?>
          <div style="font-size:56px;margin-bottom:16px;">🗑️</div>
          <h1 style="font-size:22px;font-weight:800;color:#1e293b;margin-bottom:12px;">Supprimer mes données</h1>
          <p style="color:#64748b;font-size:15px;line-height:1.7;margin-bottom:24px;">
            Vous êtes sur le point de supprimer définitivement toutes vos données
            (prénom, email, réponses, résultats) associées au test réalisé par
            <strong><?php echo esc_html($row->prenom); ?></strong>.
            Cette action est irréversible.
          </p>
          <div style="background:#fff3f3;border:1.5px solid #fecaca;border-radius:12px;padding:16px;margin-bottom:24px;text-align:left;font-size:13px;color:#DC2626;">
            ⚠️ Cette action supprime définitivement : prénom, email, toutes vos réponses, scores et résultats.
          </div>
          <button id="pp-del-btn" onclick="ppConfirmerSuppression()"
                  style="background:#DC2626;color:#fff;border:none;padding:14px 32px;border-radius:999px;font-size:15px;font-weight:700;cursor:pointer;margin-bottom:12px;width:100%;">
            Supprimer définitivement mes données
          </button>
          <br>
          <a href="<?php echo esc_url(home_url('/profil/'.$token)); ?>"
             style="color:#64748b;font-size:13px;">← Retour à mon profil</a>
          <?php endif; ?>
        </div>
        <script>
        function ppConfirmerSuppression() {
          var btn = document.getElementById('pp-del-btn');
          btn.textContent = '⏳ Suppression…'; btn.disabled = true;
          var fd = new FormData();
          fd.append('action','pp_demande_suppression');
          fd.append('nonce','<?php echo esc_js($nonce); ?>');
          fd.append('token','<?php echo esc_js($token); ?>');
          fetch('<?php echo esc_js($ajax_url); ?>',{method:'POST',body:fd})
            .then(function(r){return r.json();})
            .then(function(d){
              if(d.success){
                window.location.href = window.location.pathname + '?done=1';
              } else {
                btn.textContent='Supprimer définitivement mes données'; btn.disabled=false;
                alert(d.data.message||'Erreur');
              }
            });
        }
        </script>
        <?php
        get_footer();
    }

    /** Export JSON des données (droit d'accès RGPD). */
    private static function render_export($token) {
        $row = PP_DB::get_by_token($token);
        if ( ! $row ) wp_die('Profil introuvable.', 404);

        $data = array(
            'prenom'       => $row->prenom,
            'email'        => $row->email,
            'date_test'    => $row->date_soumis,
            'archetype'    => $row->archetype_nom,
            'scores'       => array(
                'O' => $row->score_O, 'C' => $row->score_C,
                'E' => $row->score_E, 'A' => $row->score_A, 'N' => $row->score_N,
            ),
            'consentement' => $row->consentement ? 'oui' : 'non',
            'source'       => $row->source,
        );
        header('Content-Type: application/json; charset=UTF-8');
        header('Content-Disposition: attachment; filename="mes-donnees-' . date('Y-m-d') . '.json"');
        echo wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /** Traite la demande de suppression AJAX. */
    public static function handle_demande() {
        if ( ! check_ajax_referer('pp_nonce','nonce',false) ) {
            wp_send_json_error(array('message'=>'Erreur de sécurité.'));
        }
        $token = sanitize_text_field($_POST['token'] ?? '');
        if ( ! preg_match('/^[a-f0-9]{32}$/', $token) ) {
            wp_send_json_error(array('message'=>'Token invalide.'));
        }
        $row = PP_DB::get_by_token($token);
        if ( ! $row ) {
            wp_send_json_error(array('message'=>'Profil introuvable.'));
        }
        self::supprimer($row->id, $token);
        wp_send_json_success();
    }

    /**
     * Supprime toutes les données d'un utilisateur.
     *
     * @param int    $id     ID de la ligne pp_resultats
     * @param string $token  Token du profil
     * @param bool   $notify Envoyer une notification admin (true pour suppression manuelle, false pour purge batch)
     */
    public static function supprimer( $id, $token, $notify = true ) {
        global $wpdb;
        $wpdb->delete( $wpdb->prefix . PP_DB::TABLE, array( 'id' => $id ) );
        // Nettoyer le PDF si existant
        $upload = wp_upload_dir();
        $pdf    = trailingslashit( $upload['basedir'] ) . 'pp-rapports/rapport-' . $token . '.pdf';
        if ( file_exists( $pdf ) ) {
            // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
            @unlink( $pdf );
        }
        // Notification admin — seulement sur suppression individuelle (pas lors des purges automatiques)
        if ( $notify ) {
            $admin = get_option( 'pp_admin_email', get_option( 'admin_email' ) );
            if ( $admin && is_email( $admin ) ) {
                wp_mail(
                    $admin,
                    '[' . get_bloginfo( 'name' ) . '] Suppression de données RGPD',
                    "Un utilisateur a exercé son droit à l'effacement.
ID : {$id}
Date : " . current_time( 'mysql' )
                );
            }
        }
    }

    /** Purge automatique après le délai de conservation (défaut : 2 ans). */
    public static function purge_expired() {
        $delai_mois = intval(get_option('pp_retention_mois', 24));
        if ($delai_mois <= 0) return;
        global $wpdb;
        $t    = $wpdb->prefix . PP_DB::TABLE;
        $date = date('Y-m-d H:i:s', strtotime("-{$delai_mois} months"));
        $rows = $wpdb->get_results(
            $wpdb->prepare( "SELECT id, token FROM {$t} WHERE date_soumis <= %s", $date )
        );
        $count = count( $rows );
        foreach ( $rows as $r ) {
            self::supprimer( $r->id, $r->token, false ); // false = pas de notification individuelle
        }
        if ( $count > 0 ) {
            PP_Logger::info( 'rgpd', "Purge automatique : {$count} profil(s) supprimé(s)." );
            // Un seul email récapitulatif pour toute la purge
            $admin = get_option( 'pp_admin_email', get_option( 'admin_email' ) );
            if ( $admin && is_email( $admin ) ) {
                wp_mail(
                    $admin,
                    '[' . get_bloginfo( 'name' ) . "] Purge RGPD automatique : {$count} profil(s)",
                    "La purge automatique a supprimé {$count} profil(s) arrivé(s) à expiration.
Date : " . current_time( 'mysql' ) . "
Durée de conservation configurée : " . intval( get_option( 'pp_retention_mois', 24 ) ) . " mois."
                );
            }
        }
    }
}
