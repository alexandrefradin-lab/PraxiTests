<?php
/**
 * Contrôleur principal Praxis 360 : shortcode, assets, routage frontend.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Praxis360 {

    protected static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_shortcode( 'praxis360', array( $this, 'render_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
        if ( is_admin() ) {
            Praxis360_Admin::instance();
        }
    }

    public function register_assets() {
        wp_register_style( 'praxis360', PRAXIS360_URL . 'assets/css/style.css', array(), PRAXIS360_VERSION );
        wp_register_script( 'praxis360', PRAXIS360_URL . 'assets/js/main.js', array(), PRAXIS360_VERSION, true );
    }

    /**
     * Shortcode [praxis360].
     * - ?p360_token=XXX  → passation
     * - ?p360_report=XXX → rapport du sujet
     * - sinon            → message d'information
     */
    public function render_shortcode( $atts ) {
        wp_enqueue_style( 'praxis360' );

        $token  = isset( $_GET['p360_token'] ) ? sanitize_text_field( wp_unslash( $_GET['p360_token'] ) ) : '';
        $report = isset( $_GET['p360_report'] ) ? sanitize_text_field( wp_unslash( $_GET['p360_report'] ) ) : '';

        if ( $token ) {
            return $this->render_passation( $token );
        }
        if ( $report ) {
            return $this->render_report( $report );
        }
        return '<div class="p360-card p360-info"><p>Cette page accueille les évaluations 360° Praxis. Vous y accédez via le lien personnel reçu par email.</p></div>';
    }

    /** Écran de passation pour un répondant identifié par token. */
    protected function render_passation( $token ) {
        $resp = Praxis360_DB::get_respondent_by_token( $token );
        if ( ! $resp ) {
            return '<div class="p360-card p360-info"><p>Ce lien n\'est pas valide ou a expiré. Merci de vérifier l\'adresse reçue par email.</p></div>';
        }
        if ( 'completed' === $resp->status ) {
            return '<div class="p360-card p360-info"><p><strong>Merci, vos réponses ont déjà été enregistrées.</strong> Vous pouvez fermer cette page.</p></div>';
        }

        $campaign = Praxis360_DB::get_campaign( $resp->campaign_id );
        if ( ! $campaign || 'closed' === $campaign->status ) {
            return '<div class="p360-card p360-info"><p>Cette évaluation est clôturée. Merci de votre intérêt.</p></div>';
        }

        $is_self      = ( 'self' === $resp->relation );
        $questions    = Praxis360_Items::build_questionnaire( $resp->relation, $campaign->subject_name );
        $open_qs      = Praxis360_Items::build_open_questions( $resp->relation, $campaign->subject_name );

        wp_enqueue_script( 'praxis360' );
        wp_localize_script( 'praxis360', 'PRAXIS360', array(
            'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
            'nonce'       => wp_create_nonce( 'p360_passation' ),
            'token'       => $token,
            'isSelf'      => $is_self,
            'subjectName' => $campaign->subject_name,
            'questions'   => $questions,
            'openQs'      => $open_qs,
            'scale'       => Praxis360_Items::scale(),
            'autoAdvance' => 280,
            'strings'     => array(
                'back'      => 'Retour',
                'na'        => 'Non observé',
                'pass'      => 'Passer cette question',
                'continue'  => 'Continuer',
                'start'     => $is_self ? 'Commencer' : 'Commencer l\'évaluation',
                'thanks'    => 'Merci pour votre contribution',
            ),
        ) );

        ob_start();
        include PRAXIS360_DIR . 'templates/page-intro.php';
        include PRAXIS360_DIR . 'templates/page-questions.php';
        return ob_get_clean();
    }

    /** Rapport de restitution (accessible au sujet via son subject_token). */
    protected function render_report( $report_token ) {
        $campaign = Praxis360_DB::get_campaign_by_token( $report_token );
        if ( ! $campaign ) {
            return '<div class="p360-card p360-info"><p>Rapport introuvable.</p></div>';
        }
        $data         = Praxis360_Scoring::compute( $campaign->id );
        $open_answers = Praxis360_DB::get_open_answers_for_campaign( $campaign->id );

        ob_start();
        include PRAXIS360_DIR . 'templates/page-results.php';
        return ob_get_clean();
    }
}
