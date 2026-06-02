<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PP_Admin {

    public static function init() {
        add_action( 'admin_menu',            array( __CLASS__, 'add_menus' ) );
        add_action( 'admin_init',            array( __CLASS__, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
        add_action( 'wp_ajax_pp_save_option',          array( __CLASS__, 'save_option' ) );
        add_action( 'wp_ajax_pp_toggle_relance_bloquee', array( __CLASS__, 'toggle_relance_bloquee' ) );
    }

    public static function add_menus() {
        add_menu_page('PraxiMum','PraxiMum','manage_options',
            'pp-dashboard', array(__CLASS__,'page_dashboard'),'dashicons-id-alt',56);
        add_submenu_page('pp-dashboard','Tableau de bord','Dashboard','manage_options',
            'pp-dashboard', array(__CLASS__,'page_dashboard'));
        add_submenu_page('pp-dashboard','Résultats','Résultats','manage_options',
            'pp-resultats', array(__CLASS__,'page_resultats'));
        add_submenu_page('pp-dashboard','Mode batch','Mode batch','manage_options',
            'pp-batch',     array(__CLASS__,'page_batch'));
        add_submenu_page('pp-dashboard','Réglages','Réglages','manage_options',
            'pp-reglages',  array(__CLASS__,'page_reglages'));
        add_submenu_page('pp-dashboard','Codes accès','Codes accès','manage_options',
            'pp-codes',     array(__CLASS__,'page_codes'));
        add_submenu_page('pp-dashboard','État du plugin','🩺 État','manage_options',
            'pp-health',    array(__CLASS__,'page_health'));
    }

    public static function register_settings() {
        $opts = array(
            'pp_admin_email'         => 'sanitize_email',
            'pp_rdv_url'             => 'esc_url_raw',
            'pp_politique_url'       => 'esc_url_raw',
            'pp_merci_url'           => 'esc_url_raw',
            'pp_test_url'            => 'esc_url_raw',
            'pp_site_name_override'  => 'sanitize_text_field',
            'pp_color_primary'       => array( __CLASS__, 'sanitize_hex_color' ),
            'pp_color_secondary'     => array( __CLASS__, 'sanitize_hex_color' ),
            'pp_logo_url'            => 'esc_url_raw',
            'pp_texte_intro'         => 'wp_kses_post',
            'pp_texte_merci'         => 'wp_kses_post',
            'pp_texte_rdv_cta'       => 'sanitize_text_field',
            'pp_texte_email_intro'   => 'wp_kses_post',
            'pp_relances_actives'    => 'intval',
            'pp_texte_relance_3j'    => 'wp_kses_post',
            'pp_texte_relance_8j'    => 'wp_kses_post',
            'pp_ga_event'            => 'sanitize_text_field',
            'pp_meta_pixel_id'       => 'sanitize_text_field',
            'pp_meta_event'          => 'sanitize_text_field',
            'pp_gtm_event'           => 'sanitize_text_field',
            'pp_retention_mois'      => 'intval',
            'pp_smtp_host'           => 'sanitize_text_field',
            'pp_smtp_port'           => 'intval',
            'pp_smtp_user'           => 'sanitize_email',
            'pp_smtp_pass'           => array( __CLASS__, 'sanitize_smtp_pass' ),
            'pp_smtp_secure'         => 'sanitize_text_field',
            'pp_smtp_from'           => 'sanitize_email',
        );
        foreach ( $opts as $key => $cb ) {
            register_setting( 'pp_settings', $key, array('sanitize_callback'=>$cb) );
        }
    }

    /** AJAX : sauvegarde d'une option individuelle (utilisée par batch). */
    public static function toggle_relance_bloquee() {
        if ( ! current_user_can('manage_options') ) wp_die('Accès refusé.');
        check_ajax_referer('pp_toggle_relance', 'nonce');
        $id     = intval( $_POST['id'] ?? 0 );
        $valeur = intval( $_POST['valeur'] ?? 0 );
        if ( $id > 0 ) {
            PP_DB::set_relance_bloquee( $id, $valeur );
            wp_send_json_success();
        }
        wp_send_json_error();
    }

    public static function save_option() {
        if ( ! current_user_can('manage_options') ) wp_die('Accès refusé.');
        check_ajax_referer('pp_save_option','nonce');
        $key   = sanitize_key($_POST['key'] ?? '');
        $value = sanitize_text_field($_POST['value'] ?? '');
        $allowed = array('pp_test_url');
        if ( in_array($key,$allowed) ) update_option($key, esc_url_raw($value));
        wp_send_json_success();
    }

    /** Ne pas écraser le mot de passe si le champ est laissé vide. */
    public static function sanitize_smtp_pass( $val ) {
        $val = trim( $val );
        if ( $val === '' ) return get_option( 'pp_smtp_pass', '' );
        return $val;
    }

    public static function sanitize_hex_color($val) {
        return preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/',$val) ? $val : '';
    }

    public static function enqueue_assets($hook) {
        if ( strpos($hook,'pp-') === false && strpos($hook,'personnalite') === false ) return;
        wp_enqueue_style('pp-admin', PP_PLUGIN_URL.'assets/css/admin.css', array(), PP_VERSION);
        if ( strpos($hook,'pp-reglages') !== false ) {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
            wp_add_inline_script('wp-color-picker',
                'jQuery(function($){ $(".pp-color-picker").wpColorPicker(); });');
        }
    }

    // ── Pages ───────────────────────────────────────────────────
    public static function page_dashboard() { include PP_PLUGIN_DIR . 'admin/views/dashboard.php'; }

    public static function page_resultats() {
        if ( isset($_GET['detail']) ) {
            $row = PP_DB::get_one(intval($_GET['detail']));
            if ( $row ) { include PP_PLUGIN_DIR . 'admin/views/detail.php'; return; }
        }
        include PP_PLUGIN_DIR . 'admin/views/resultats.php';
    }

    public static function page_batch()  { include PP_PLUGIN_DIR . 'admin/views/batch.php'; }
    public static function page_codes()  { include PP_PLUGIN_DIR . 'admin/views/codes.php'; }
    public static function page_health() { PP_Health::render_admin(); }

    public static function page_reglages() {
        if ( isset($_GET['settings-updated']) ) :
            echo '<div class="notice notice-success is-dismissible"><p>✅ Réglages enregistrés.</p></div>';
        endif; ?>
        <div class="wrap" id="pp-reglages-wrap">
          <h1>Réglages — PraxiMum</h1>
          <form method="post" action="options.php">
            <?php settings_fields('pp_settings'); ?>
            <table class="form-table pp-settings-table" style="width:100%;border-collapse:collapse;">
              <?php self::section('🏢 Identité & Contact'); ?>
              <?php self::field_text('pp_admin_email','Email admin (copie des résultats)',get_option('admin_email'),'email'); ?>
              <?php self::field_url('pp_rdv_url','URL prise de rendez-vous',home_url('/contact')); ?>
              <?php self::field_url('pp_test_url','URL de la page du test',home_url('/'),
                  'Page contenant le shortcode [test_personnalite]. Utilisée par le mode batch.'); ?>
              <?php self::field_url('pp_politique_url','URL politique de confidentialité',home_url('/politique-de-confidentialite'),
                  'Créez une page WordPress avec le shortcode [pp_politique] puis copiez son URL ici.'); ?>
              <?php self::field_url('pp_merci_url','URL page "Merci" (après soumission)','',
                  'Laissez vide pour rester sur la page des résultats.'); ?>
              <?php self::section('🎨 Marque blanche'); ?>
              <?php self::field_text('pp_site_name_override','Nom affiché dans les mails','',
                  'Laissez vide pour utiliser le nom du site WordPress.'); ?>
              <?php self::field_color('pp_color_primary','Couleur principale','#E8541A'); ?>
              <?php self::field_color('pp_color_secondary','Couleur secondaire','#1E2A3A'); ?>
              <?php self::field_url('pp_logo_url','URL du logo',''); ?>
              <?php self::section('✏️ Textes personnalisables'); ?>
              <?php self::field_textarea('pp_texte_intro','Introduction du test',
                  "Ceci est un outil de clarification, il ne remplace pas un accompagnement humain et ne constitue pas un diagnostic. Il est composé de 128 questions couvrant 5 grandes dimensions et 30 facettes. Comptez environ 12 minutes."); ?>
              <?php self::field_text('pp_texte_rdv_cta','Texte du bouton bilan de compétences','Découvrir le bilan de compétences →',
                  'text', 'Affiché sur le CTA en haut du test et après les résultats.'); ?>
              <?php self::field_textarea('pp_texte_email_intro','Intro email résultats',
                  'voici le résumé de votre profil de personnalité Big Five.'); ?>
              <?php self::field_textarea('pp_texte_merci','Message page "Merci"',
                  'Merci d\'avoir complété votre test. Vos résultats vous ont été envoyés par email.'); ?>
              <?php self::section('📬 Relances automatiques'); ?>
              <?php self::field_checkbox('pp_relances_actives','Activer les relances J+3 et J+8',get_option('pp_relances_actives',1)); ?>
              <?php self::field_textarea('pp_texte_relance_3j','Texte relance J+3',
                  "Il y a 3 jours, vous avez découvert votre profil de personnalité. Avez-vous envie d'aller plus loin dans la connaissance de vous-même ? Un bilan de compétences peut vous aider à transformer cette clarté en un projet concret."); ?>
              <?php self::field_textarea('pp_texte_relance_8j','Texte relance J+8',
                  "Voilà 8 jours que vous avez découvert votre profil. Peut-être avez-vous des questions qui sont apparues depuis — sur vos talents, vos axes de développement, ou votre prochain cap professionnel ? C'est exactement ce qu'on explore ensemble dans un bilan de compétences."); ?>
              <?php self::section('🔒 RGPD & Conservation'); ?>
              <?php self::field_text('pp_retention_mois','Durée de conservation (mois)','24',
                  'number','Durée avant suppression automatique des données (0 = désactivé).'); ?>
              <?php self::section('📊 Tracking & Analytics'); ?>
              <?php self::field_text('pp_ga_event','Événement Google Analytics 4','test_personnalite_complete'); ?>
              <?php self::field_text('pp_gtm_event','Événement GTM (dataLayer)','pp_test_complete'); ?>
              <?php self::field_text('pp_meta_pixel_id','Meta Pixel ID','',
                  'Laissez vide pour ne pas utiliser.'); ?>
              <?php self::field_text('pp_meta_event','Événement Meta Pixel','Lead'); ?>
              <?php self::section('📧 Envoi des emails (SMTP)'); ?>
              <?php self::field_text('pp_smtp_from','Adresse expéditeur','contact@praxis-accompagnement.com',
                  'email','Adresse qui apparaît dans le champ "De :" des emails envoyés.'); ?>
              <?php self::field_text('pp_smtp_host','Serveur SMTP','ssl0.ovh.net',
                  'text','Pour OVH : ssl0.ovh.net'); ?>
              <?php self::field_text('pp_smtp_port','Port SMTP','465',
                  'number','465 (SSL) ou 587 (TLS)'); ?>
              <?php
              $secure_val = get_option('pp_smtp_secure','ssl');
              echo '<tr><th style="padding:12px 16px;text-align:left;font-weight:600;font-size:13px;">Chiffrement</th><td style="padding:12px 16px;">';
              echo '<select name="pp_smtp_secure" style="height:36px;padding:0 8px;">';
              foreach (['ssl'=>'SSL (port 465)','tls'=>'TLS (port 587)','none'=>'Aucun'] as $v=>$l) {
                  echo '<option value="'.esc_attr($v).'"'.selected($secure_val,$v,false).'>'.esc_html($l).'</option>';
              }
              echo '</select></td></tr>';
              ?>
              <?php self::field_text('pp_smtp_user','Identifiant SMTP','contact@praxis-accompagnement.com',
                  'text','Généralement identique à l\'adresse email.'); ?>
              <tr>
                <th style="padding:12px 16px;text-align:left;font-weight:600;font-size:13px;">Mot de passe SMTP</th>
                <td style="padding:12px 16px;">
                  <input type="password" name="pp_smtp_pass" value="" autocomplete="new-password"
                    style="width:100%;max-width:400px;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;"
                    placeholder="Laissez vide pour ne pas modifier">
                  <?php if ( get_option('pp_smtp_pass','') ) echo '<span style="color:#16a34a;font-size:12px;margin-left:8px;">✅ Mot de passe enregistré</span>'; ?>
                </td>
              </tr>
            </table>
            <?php submit_button('Enregistrer les réglages','primary large'); ?>
          </form>
          <div style="margin-top:28px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:20px;">
            <h2 style="margin-top:0;font-size:15px;">🔧 Shortcodes disponibles</h2>
            <table style="border-collapse:collapse;">
              <tr><td style="padding:4px 16px 4px 0;font-family:monospace;font-size:13px;">[test_personnalite]</td>
                  <td style="font-size:13px;color:#475569;">Intègre le test complet dans n'importe quelle page.</td></tr>
              <tr><td style="padding:4px 16px 4px 0;font-family:monospace;font-size:13px;">[pp_profil token="xxx"]</td>
                  <td style="font-size:13px;color:#475569;">Affiche un profil spécifique dans une page.</td></tr>
            </table>
            <p style="margin:12px 0 0;font-size:13px;color:#64748b;">
              ℹ️ Après activation, allez dans <strong>Réglages → Permaliens</strong> et cliquez "Enregistrer" pour activer les URLs <code>/profil/[token]</code>.
            </p>
          </div>
        </div>
        <?php
    }

    // ── Helpers ────────────────────────────────────────────────
    private static function section($title) { ?>
        <tr><td colspan="2" style="padding:20px 0 8px 0;">
          <h2 style="margin:0;font-size:14px;font-weight:700;color:#1e293b;border-bottom:2px solid #e2e8f0;padding-bottom:8px;"><?php echo $title; ?></h2>
        </td></tr>
    <?php }
    private static function field_text($id,$label,$default='',$type='text',$desc='') { ?>
        <tr>
          <th style="text-align:left;padding:8px 16px 8px 0;font-size:13px;width:260px;vertical-align:top;"><?php echo esc_html($label); ?></th>
          <td style="padding:6px 0;">
            <input type="<?php echo $type; ?>" name="<?php echo $id; ?>"
                   value="<?php echo esc_attr(get_option($id,$default)); ?>"
                   class="regular-text" style="width:100%;max-width:440px;">
            <?php if($desc) echo '<p class="description">'.esc_html($desc).'</p>'; ?>
          </td>
        </tr>
    <?php }
    private static function field_url($id,$label,$default='',$desc='') { self::field_text($id,$label,$default,'url',$desc); }
    private static function field_color($id,$label,$default='#E8541A') { ?>
        <tr>
          <th style="text-align:left;padding:8px 16px 8px 0;font-size:13px;width:260px;"><?php echo esc_html($label); ?></th>
          <td style="padding:6px 0;">
            <input type="text" name="<?php echo $id; ?>"
                   value="<?php echo esc_attr(get_option($id,$default)); ?>"
                   class="pp-color-picker" data-default-color="<?php echo esc_attr($default); ?>">
          </td>
        </tr>
    <?php }
    private static function field_textarea($id,$label,$default='',$desc='') { ?>
        <tr>
          <th style="text-align:left;padding:8px 16px 8px 0;font-size:13px;width:260px;vertical-align:top;"><?php echo esc_html($label); ?></th>
          <td style="padding:6px 0;">
            <textarea name="<?php echo $id; ?>" rows="3"
                      style="width:100%;max-width:440px;font-size:13px;"><?php echo esc_textarea(get_option($id,$default)); ?></textarea>
            <?php if($desc) echo '<p class="description">'.esc_html($desc).'</p>'; ?>
          </td>
        </tr>
    <?php }
    private static function field_checkbox($id,$label,$default=1,$desc='') { ?>
        <tr>
          <th style="text-align:left;padding:8px 16px 8px 0;font-size:13px;width:260px;"><?php echo esc_html($label); ?></th>
          <td style="padding:6px 0;">
            <label><input type="checkbox" name="<?php echo $id; ?>" value="1"
                   <?php checked(get_option($id,$default),1); ?>> Activer</label>
            <?php if($desc) echo '<p class="description">'.esc_html($desc).'</p>'; ?>
          </td>
        </tr>
    <?php }
}
