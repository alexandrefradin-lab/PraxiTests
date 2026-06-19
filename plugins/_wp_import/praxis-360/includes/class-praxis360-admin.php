<?php
/**
 * Back-office Praxis 360 : menu admin, réglages, liste/création de campagnes, rapport.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Praxis360_Admin {

    protected static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', array( $this, 'menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
    }

    public function menu() {
        add_menu_page(
            'Praxis 360', 'Praxis 360', 'manage_options',
            'praxis360', array( $this, 'page_campaigns' ), 'dashicons-groups', 30
        );
        add_submenu_page( 'praxis360', 'Campagnes', 'Campagnes', 'manage_options', 'praxis360', array( $this, 'page_campaigns' ) );
        add_submenu_page( 'praxis360', 'Nouvelle campagne', 'Nouvelle campagne', 'manage_options', 'praxis360-new', array( $this, 'page_new' ) );
        add_submenu_page( 'praxis360', 'Réglages', 'Réglages', 'manage_options', 'praxis360-settings', array( $this, 'page_settings' ) );
        add_submenu_page( null, 'Rapport', 'Rapport', 'manage_options', 'praxis360-report', array( $this, 'page_report' ) );
    }

    public function register_settings() {
        register_setting( 'praxis360_settings_group', 'praxis360_settings', array( $this, 'sanitize_settings' ) );
        register_setting( 'praxis360_settings_group', 'praxis360_page_url', 'esc_url_raw' );
    }

    public function sanitize_settings( $input ) {
        $out = array();
        $out['smtp_host']   = isset( $input['smtp_host'] ) ? sanitize_text_field( $input['smtp_host'] ) : 'ssl0.ovh.net';
        $out['smtp_port']   = isset( $input['smtp_port'] ) ? absint( $input['smtp_port'] ) : 465;
        $out['smtp_secure'] = isset( $input['smtp_secure'] ) ? sanitize_text_field( $input['smtp_secure'] ) : 'ssl';
        $out['smtp_user']   = isset( $input['smtp_user'] ) ? sanitize_text_field( $input['smtp_user'] ) : '';
        $out['smtp_pass']   = isset( $input['smtp_pass'] ) ? $input['smtp_pass'] : '';
        $out['from_email']  = isset( $input['from_email'] ) ? sanitize_email( $input['from_email'] ) : get_option( 'admin_email' );
        $out['from_name']   = isset( $input['from_name'] ) ? sanitize_text_field( $input['from_name'] ) : 'Praxis Accompagnement';
        $out['admin_email'] = isset( $input['admin_email'] ) ? sanitize_email( $input['admin_email'] ) : get_option( 'admin_email' );
        return $out;
    }

    public function assets( $hook ) {
        if ( false === strpos( $hook, 'praxis360' ) ) {
            return;
        }
        wp_enqueue_style( 'praxis360-admin', PRAXIS360_URL . 'assets/css/style.css', array(), PRAXIS360_VERSION );
        wp_enqueue_script( 'praxis360-admin', PRAXIS360_URL . 'assets/js/admin.js', array(), PRAXIS360_VERSION, true );
        wp_localize_script( 'praxis360-admin', 'PRAXIS360_ADMIN', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'p360_admin' ),
        ) );
    }

    /** Liste des campagnes. */
    public function page_campaigns() {
        $campaigns = Praxis360_DB::get_campaigns();
        $labels    = Praxis360_Items::relations();
        ?>
        <div class="wrap">
            <h1>Praxis 360 — Campagnes
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=praxis360-new' ) ); ?>" class="page-title-action">Nouvelle campagne</a>
            </h1>
            <table class="wp-list-table widefat fixed striped">
                <thead><tr>
                    <th>Sujet</th><th>Statut</th><th>Réponses</th><th>Créée le</th><th>Actions</th>
                </tr></thead>
                <tbody>
                <?php if ( empty( $campaigns ) ) : ?>
                    <tr><td colspan="5">Aucune campagne pour l'instant.</td></tr>
                <?php else : foreach ( $campaigns as $c ) :
                    $resps = Praxis360_DB::get_respondents( $c->id );
                    $done  = 0; $total = 0;
                    foreach ( $resps as $r ) { $total++; if ( 'completed' === $r->status ) { $done++; } }
                    $report_url = admin_url( 'admin.php?page=praxis360-report&cid=' . $c->id );
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html( $c->subject_name ); ?></strong><br><small><?php echo esc_html( $c->subject_email ); ?></small></td>
                        <td><?php echo esc_html( $c->status ); ?></td>
                        <td><?php echo (int) $done; ?> / <?php echo (int) $total; ?></td>
                        <td><?php echo esc_html( mysql2date( 'd/m/Y', $c->created_at ) ); ?></td>
                        <td>
                            <a href="<?php echo esc_url( $report_url ); ?>" class="button">Rapport</a>
                            <button class="button p360-remind" data-cid="<?php echo (int) $c->id; ?>">Relancer</button>
                            <button class="button p360-close" data-cid="<?php echo (int) $c->id; ?>">Clôturer</button>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /** Formulaire de création de campagne. */
    public function page_new() {
        ?>
        <div class="wrap">
            <h1>Nouvelle campagne 360</h1>
            <div id="p360-new-app">
                <table class="form-table">
                    <tr>
                        <th><label for="p360-subject-name">Nom du sujet</label></th>
                        <td><input type="text" id="p360-subject-name" class="regular-text" placeholder="Prénom de la personne évaluée"></td>
                    </tr>
                    <tr>
                        <th><label for="p360-subject-email">Email du sujet</label></th>
                        <td><input type="email" id="p360-subject-email" class="regular-text" placeholder="email@exemple.com"></td>
                    </tr>
                    <tr>
                        <th><label for="p360-deadline">Date limite (optionnel)</label></th>
                        <td><input type="date" id="p360-deadline"></td>
                    </tr>
                </table>

                <h2>Évaluateurs</h2>
                <p>Ajoutez au moins 3 évaluateurs par catégorie (pairs, collaborateurs) pour garantir l'anonymat des moyennes.</p>
                <table class="widefat" id="p360-evaluators">
                    <thead><tr><th>Nom</th><th>Email</th><th>Relation</th><th></th></tr></thead>
                    <tbody></tbody>
                </table>
                <p><button class="button" id="p360-add-eval">+ Ajouter un évaluateur</button></p>

                <p>
                    <button class="button button-primary" id="p360-create">Créer la campagne et envoyer les invitations</button>
                </p>
                <div id="p360-create-msg"></div>
            </div>
        </div>
        <?php
    }

    /** Réglages SMTP OVH + page de passation. */
    public function page_settings() {
        $s = Praxis360_Mailer::settings();
        ?>
        <div class="wrap">
            <h1>Praxis 360 — Réglages</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'praxis360_settings_group' ); ?>
                <h2>Page de passation</h2>
                <table class="form-table">
                    <tr>
                        <th><label>URL de la page contenant le shortcode</label></th>
                        <td>
                            <input type="url" name="praxis360_page_url" class="regular-text" value="<?php echo esc_attr( get_option( 'praxis360_page_url', '' ) ); ?>" placeholder="<?php echo esc_attr( home_url( '/praxis-360/' ) ); ?>">
                            <p class="description">Créez une page WordPress contenant le shortcode <code>[praxis360]</code> et collez son URL ici.</p>
                        </td>
                    </tr>
                </table>
                <h2>SMTP OVH</h2>
                <table class="form-table">
                    <tr><th>Serveur</th><td><input type="text" name="praxis360_settings[smtp_host]" class="regular-text" value="<?php echo esc_attr( $s['smtp_host'] ); ?>"></td></tr>
                    <tr><th>Port</th><td><input type="number" name="praxis360_settings[smtp_port]" value="<?php echo esc_attr( $s['smtp_port'] ); ?>"></td></tr>
                    <tr><th>Sécurité</th><td><input type="text" name="praxis360_settings[smtp_secure]" value="<?php echo esc_attr( $s['smtp_secure'] ); ?>"> <span class="description">ssl pour le port 465</span></td></tr>
                    <tr><th>Identifiant</th><td><input type="text" name="praxis360_settings[smtp_user]" class="regular-text" value="<?php echo esc_attr( $s['smtp_user'] ); ?>" placeholder="contact@praxis-accompagnement.com"></td></tr>
                    <tr><th>Mot de passe</th><td><input type="password" name="praxis360_settings[smtp_pass]" class="regular-text" value="<?php echo esc_attr( $s['smtp_pass'] ); ?>"></td></tr>
                    <tr><th>Email expéditeur</th><td><input type="email" name="praxis360_settings[from_email]" class="regular-text" value="<?php echo esc_attr( $s['from_email'] ); ?>"></td></tr>
                    <tr><th>Nom expéditeur</th><td><input type="text" name="praxis360_settings[from_name]" class="regular-text" value="<?php echo esc_attr( $s['from_name'] ); ?>"></td></tr>
                    <tr><th>Email admin (notifications)</th><td><input type="email" name="praxis360_settings[admin_email]" class="regular-text" value="<?php echo esc_attr( $s['admin_email'] ); ?>"></td></tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /** Rapport admin (réutilise le template de restitution). */
    public function page_report() {
        $cid      = isset( $_GET['cid'] ) ? absint( $_GET['cid'] ) : 0;
        $campaign = Praxis360_DB::get_campaign( $cid );
        if ( ! $campaign ) {
            echo '<div class="wrap"><h1>Rapport</h1><p>Campagne introuvable.</p></div>';
            return;
        }
        $data         = Praxis360_Scoring::compute( $campaign->id );
        $open_answers = Praxis360_DB::get_open_answers_for_campaign( $campaign->id );
        wp_enqueue_style( 'praxis360-admin' );
        echo '<div class="wrap"><h1>Rapport 360 — ' . esc_html( $campaign->subject_name ) . '</h1>';
        echo '<p><a class="button" href="' . esc_url( Praxis360_Mailer::report_url( $campaign->subject_token ) ) . '" target="_blank">Voir la version partageable (lien sujet)</a></p>';
        include PRAXIS360_DIR . 'templates/page-results.php';
        echo '</div>';
    }
}
