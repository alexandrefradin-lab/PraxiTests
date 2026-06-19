<?php
/**
 * PraxiMet – Gestionnaire d'emails
 * Envoie les emails de confirmation, notification et relance
 * Utilise wp_mail() — compatible avec tous les plugins SMTP WordPress
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class PraxiMet_Email_Manager {

    /**
     * Branche le SMTP une seule fois au chargement du plugin.
     * À appeler depuis praximet.php au plugins_loaded.
     */
    public static function init() {
        add_action( 'phpmailer_init', [ __CLASS__, 'configurer_smtp' ] );
    }

    /**
     * Configure PHPMailer avec les paramètres SMTP sauvegardés dans les options.
     */
    public static function configurer_smtp( $phpmailer ) {
        $host   = get_option( 'praximet_smtp_host', '' );
        $user   = get_option( 'praximet_smtp_user', '' );
        $pass   = get_option( 'praximet_smtp_pass', '' );
        $port   = (int) get_option( 'praximet_smtp_port', 465 );
        $secure = get_option( 'praximet_smtp_secure', 'ssl' );
        $from   = get_option( 'praximet_smtp_from', 'contact@praxis-accompagnement.com' );

        // Toujours forcer l'expéditeur même sans SMTP
        if ( ! empty( $from ) ) {
            $phpmailer->From     = $from;
            $phpmailer->FromName = get_bloginfo( 'name' );
        }

        if ( empty( $host ) || empty( $user ) || empty( $pass ) ) {
            error_log( 'PraxiMet SMTP — paramètres incomplets. Envoi via mail() PHP. host=' . $host );
            return;
        }

        $phpmailer->isSMTP();
        $phpmailer->Host       = $host;
        $phpmailer->SMTPAuth   = true;
        $phpmailer->Username   = $user;
        $phpmailer->Password   = $pass;
        $phpmailer->SMTPSecure = $secure === 'none' ? '' : $secure;
        $phpmailer->Port       = $port;
        $phpmailer->From       = ! empty( $from ) ? $from : $user;
        $phpmailer->FromName   = get_bloginfo( 'name' );

        // Log erreurs SMTP dans wp-content/debug.log si WP_DEBUG_LOG est activé
        $phpmailer->SMTPDebug  = 0;
        $phpmailer->Debugoutput = function( $msg, $level ) {
            if ( $level > 0 ) error_log( 'PraxiMet SMTP debug: ' . $msg );
        };
    }



    /**
     * Envoie l'email de confirmation au candidat avec son profil RIASEC.
     *
     * @param array $data  prenom, email, code
     */
    public static function envoyer_confirmation_candidat( array $data ) {

        $prenom       = $data['prenom'];
        $email        = $data['email'];
        $code         = $data['code'];
        $calendly_url = get_option( 'praximet_calendly_url', '' );

        // Construit le profil complet pour le template
        require_once PRAXIMET_PATH . 'includes/class-riasec-engine.php';
        $resultat = PraxiMet_Riasec_Engine::get_resultat_complet( $code );
        $profil   = $resultat['profil'];

        $sujet = sprintf(
            'Votre profil RIASEC %s — %s',
            esc_html( $code ),
            get_bloginfo( 'name' )
        );

        $corps = self::render_template( 'email-candidat.php', compact(
            'prenom', 'code', 'profil', 'calendly_url'
        ));

        if ( empty( $corps ) ) {
            error_log( 'PraxiMet – Template email-candidat.php introuvable.' );
            return;
        }

        self::envoyer( $email, $sujet, $corps );
    }

    /**
     * Envoie la notification au conseiller avec les coordonnées et le profil du lead.
     *
     * @param array $data  prenom, nom, email, telephone, code, scores, lead_id
     */
    public static function envoyer_notification_conseiller( array $data ) {

        $prenom    = $data['prenom'];
        $nom       = $data['nom'];
        $email     = $data['email'];
        $telephone = $data['telephone'];
        $code      = $data['code'];
        $scores    = $data['scores'];
        $lead_id   = $data['lead_id'];

        $email_conseiller = get_option( 'praximet_email_conseiller', get_option('admin_email') );

        $sujet = sprintf(
            '[PraxiMet] Nouveau lead : %s %s — Code %s',
            $prenom,
            $nom,
            $code
        );

        $corps = self::render_template( 'email-conseiller.php', compact(
            'prenom', 'nom', 'email', 'telephone', 'code', 'scores', 'lead_id'
        ));

        if ( empty( $corps ) ) {
            error_log( 'PraxiMet – Template email-conseiller.php introuvable.' );
            return;
        }

        self::envoyer( $email_conseiller, $sujet, $corps );
    }

    /**
     * Envoie l'email de relance au candidat si aucun RDV n'a été pris.
     * Appelé par le cron manager.
     *
     * @param int $lead_id
     */
    public static function envoyer_relance( int $lead_id ) {
        global $wpdb;

        $table = $wpdb->prefix . 'praximet_leads';

        // Récupère le lead
        $lead = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $lead_id ),
            ARRAY_A
        );

        if ( ! $lead ) {
            error_log( "PraxiMet – Relance : lead #$lead_id introuvable." );
            return;
        }

        // Ne relance pas si RDV déjà pris ou lead archivé
        if ( $lead['rdv_pris'] || $lead['statut'] === 'archive' ) {
            return;
        }

        // Ne relance pas si déjà envoyée
        if ( $lead['relance_envoyee'] ) {
            return;
        }

        $prenom       = $lead['prenom'];
        $email        = $lead['email'];
        $code         = $lead['code_riasec'];
        $calendly_url = get_option( 'praximet_calendly_url', '' );

        $sujet = sprintf(
            '%s, avez-vous eu le temps de réfléchir à votre projet ?',
            $prenom
        );

        $corps = self::render_template( 'email-relance.php', compact(
            'prenom', 'code', 'calendly_url'
        ));

        if ( empty( $corps ) ) {
            error_log( 'PraxiMet – Template email-relance.php introuvable.' );
            return;
        }

        $sent = self::envoyer( $email, $sujet, $corps );

        // Marque la relance comme envoyée
        if ( $sent ) {
            $wpdb->update(
                $table,
                [
                    'relance_envoyee' => 1,
                    'updated_at'      => current_time('mysql'),
                ],
                [ 'id' => $lead_id ],
                [ '%d', '%s' ],
                [ '%d' ]
            );
        }
    }

    // ── Helpers privés ────────────────────────────────────────────────

    /**
     * Envoie un email HTML via wp_mail().
     *
     * @param string $to
     * @param string $sujet
     * @param string $corps  HTML
     * @return bool
     */
    private static function envoyer( string $to, string $sujet, string $corps ) {

        if ( ! is_email( $to ) ) {
            error_log( "PraxiMet – Adresse email invalide : $to" );
            return false;
        }

        $from_name  = get_bloginfo( 'name' );
        $from_email = get_option( 'praximet_smtp_from', 'contact@praxis-accompagnement.com' );
        if ( empty( $from_email ) ) $from_email = 'contact@praxis-accompagnement.com';

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>',
            'Reply-To: ' . $from_email,
        ];

        $sent = wp_mail( $to, $sujet, $corps, $headers );

        if ( ! $sent ) {
            global $phpmailer;
            $err = '';
            if ( isset( $phpmailer ) && is_object( $phpmailer ) ) {
                $err = $phpmailer->ErrorInfo ?? 'inconnue';
            }
            error_log( "PraxiMet – ÉCHEC envoi à : $to | Sujet : $sujet | PHPMailer : $err" );
        } else {
            error_log( "PraxiMet – Email OK → $to | $sujet" );
        }

        return $sent;
    }

    /**
     * Charge un template PHP et retourne le HTML généré.
     * Les variables passées dans $vars sont extraites dans le scope du template.
     *
     * @param string $fichier  Nom du fichier dans /templates/
     * @param array  $vars     Variables à injecter
     * @return string          HTML généré
     */
    private static function render_template( string $fichier, array $vars = [] ) {

        $path = PRAXIMET_PATH . 'templates/' . $fichier;

        if ( ! file_exists( $path ) ) {
            return '';
        }

        // Extrait les variables dans le scope local du template
        extract( $vars, EXTR_SKIP );

        ob_start();
        include $path;
        return ob_get_clean();
    }
}
