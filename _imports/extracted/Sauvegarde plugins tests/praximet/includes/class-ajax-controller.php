<?php
/**
 * PraxiMet – Contrôleur AJAX
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class PraxiMet_Ajax_Controller {

    const RATE_LIMIT = 20;

    public static function init() {
        add_action( 'wp_ajax_nopriv_praximet_submit',          [ __CLASS__, 'handle_submit' ] );
        add_action( 'wp_ajax_praximet_submit',                  [ __CLASS__, 'handle_submit' ] );
        add_action( 'wp_ajax_nopriv_praximet_supprimer_profil', [ __CLASS__, 'handle_supprimer_profil' ] );
        add_action( 'wp_ajax_praximet_supprimer_profil',        [ __CLASS__, 'handle_supprimer_profil' ] );
    }

    public static function handle_submit() {

        if ( ! check_ajax_referer( 'praximet_submit', 'praximet_nonce', false ) ) {
            self::erreur( 'Requête invalide. Veuillez recharger la page et réessayer.' );
        }

        if ( ! self::verifier_rate_limit() ) {
            self::erreur( 'Trop de tentatives. Merci de réessayer dans une heure.' );
        }

        $prenom       = sanitize_text_field( wp_unslash( $_POST['praximet_prenom'] ?? '' ) );
        $nom          = sanitize_text_field( wp_unslash( $_POST['praximet_nom']    ?? '' ) );
        $email        = sanitize_email(      wp_unslash( $_POST['praximet_email']  ?? '' ) );
        $rgpd         = isset( $_POST['praximet_rgpd'] ) ? 1 : 0;
        $reponses_raw = wp_unslash( $_POST['praximet_reponses'] ?? '' );

        if ( empty( $prenom ) ) self::erreur( 'Votre prénom est requis.' );
        if ( empty( $nom ) )    self::erreur( 'Votre nom est requis.' );
        if ( empty( $email ) || ! is_email( $email ) ) self::erreur( 'Veuillez entrer une adresse email valide.' );
        if ( ! $rgpd )          self::erreur( 'Vous devez accepter la politique de confidentialité.' );
        if ( empty( $reponses_raw ) ) self::erreur( 'Réponses au quiz manquantes. Veuillez recommencer.' );

        $reponses = json_decode( $reponses_raw, true );
        if ( ! is_array( $reponses ) || empty( $reponses ) ) {
            self::erreur( 'Format des réponses invalide. Veuillez recommencer.' );
        }

        require_once PRAXIMET_PATH . 'data/questions-riasec.php';
        $questions          = praximet_get_questions();
        $ids_attendus       = array_column( $questions, 'id' );
        $ids_attendus_lower = array_map( 'strtolower', $ids_attendus );

        $reponses_valides = [];
        foreach ( $reponses as $id => $val ) {
            $id_clean = preg_replace( '/[^a-zA-Z0-9]/', '', (string) $id );
            $id_lower = strtolower( $id_clean );
            $pos      = array_search( $id_lower, $ids_attendus_lower, true );
            if ( $pos !== false ) {
                $id_canonique                    = $ids_attendus[ $pos ];
                $reponses_valides[$id_canonique] = ( (int) $val === 1 ) ? 1 : 0;
            }
        }

        if ( count( $reponses_valides ) !== count( $ids_attendus ) ) {
            self::erreur( 'Le quiz n\'est pas complet. Veuillez répondre à toutes les questions.' );
        }

        require_once PRAXIMET_PATH . 'includes/class-riasec-engine.php';
        $calcul = PraxiMet_Riasec_Engine::calculer_scores( $reponses_valides );
        $scores = $calcul['scores'];
        $code   = $calcul['code'];

        require_once PRAXIMET_PATH . 'includes/class-lead-manager.php';
        if ( PraxiMet_Lead_Manager::email_existe( $email ) ) {
            error_log( 'PraxiMet – Email déjà existant resoumis : ' . $email );
        }

        $source_page = esc_url_raw( wp_unslash( $_POST['praximet_source'] ?? '' ) );

        $lead_id = PraxiMet_Lead_Manager::sauvegarder([
            'nom'         => $nom,
            'prenom'      => $prenom,
            'email'       => $email,
            'telephone'   => '',
            'rgpd'        => $rgpd,
            'scores'      => $scores,
            'code'        => $code,
            'source_page' => $source_page,
        ]);

        if ( ! $lead_id ) {
            self::erreur( 'Une erreur est survenue lors de l\'enregistrement. Veuillez réessayer.' );
        }

        require_once PRAXIMET_PATH . 'includes/class-email-manager.php';
        PraxiMet_Email_Manager::envoyer_confirmation_candidat([
            'prenom' => $prenom,
            'email'  => $email,
            'code'   => $code,
        ]);
        PraxiMet_Email_Manager::envoyer_notification_conseiller([
            'prenom'    => $prenom,
            'nom'       => $nom,
            'email'     => $email,
            'telephone' => '',
            'code'      => $code,
            'scores'    => $scores,
            'lead_id'   => $lead_id,
        ]);

        $delai_heures = (int) get_option( 'praximet_delai_relance', 48 );
        wp_schedule_single_event(
            time() + ( $delai_heures * HOUR_IN_SECONDS ),
            'praximet_relance_cron',
            [ $lead_id ]
        );

        $resultat     = PraxiMet_Riasec_Engine::get_resultat_complet( $code );
        $scores_sd    = PraxiMet_Riasec_Engine::calculer_scores_sous_domaines( $reponses_valides );
        $calendly_url = esc_url( get_option( 'praximet_calendly_url', '' ) );

        wp_send_json_success([
            'lead_id'      => $lead_id,
            'code'         => $code,
            'profil'       => $resultat['profil'],
            'scores'       => $scores,
            'scores_sd'    => $scores_sd,
            'calendly_url' => $calendly_url,
            'prenom'       => $prenom,
        ]);
    }

    public static function handle_supprimer_profil() {
        if ( ! check_ajax_referer( 'praximet_submit', 'praximet_nonce', false ) ) {
            wp_send_json_error([ 'message' => 'Requête invalide.' ]);
        }
        $lead_id = isset( $_POST['lead_id'] ) ? (int) $_POST['lead_id'] : 0;
        if ( ! $lead_id ) wp_send_json_error([ 'message' => 'Identifiant manquant.' ]);
        require_once PRAXIMET_PATH . 'includes/class-lead-manager.php';
        $ok = PraxiMet_Lead_Manager::supprimer( $lead_id );
        $ok
            ? wp_send_json_success([ 'message' => 'Vos données ont été supprimées.' ])
            : wp_send_json_error([ 'message' => 'Une erreur est survenue.' ]);
    }

    private static function erreur( string $message ) {
        wp_send_json_error([ 'message' => $message ]);
    }

    private static function verifier_rate_limit() {
        $ip    = self::get_ip();
        $key   = 'praximet_rl_' . md5( $ip );
        $count = (int) get_transient( $key );
        if ( $count >= self::RATE_LIMIT ) return false;
        set_transient( $key, $count + 1, 10 * MINUTE_IN_SECONDS );
        return true;
    }

    private static function get_ip() {
        foreach ( ['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $h ) {
            if ( ! empty( $_SERVER[$h] ) ) {
                $ip = trim( explode( ',', $_SERVER[$h] )[0] );
                if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) return $ip;
            }
        }
        return '0.0.0.0';
    }
}
