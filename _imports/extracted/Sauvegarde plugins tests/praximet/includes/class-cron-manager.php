<?php
/**
 * PraxiMet – Gestionnaire de tâches planifiées (WP-Cron)
 * Gère les relances automatiques après le délai configuré
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class PraxiMet_Cron_Manager {

    /**
     * Enregistre les hooks WP-Cron.
     * Appelé au bootstrap du plugin.
     */
    public static function init() {
        // Exécution de la relance pour un lead spécifique
        add_action( 'praximet_relance_cron', [ __CLASS__, 'executer_relance' ] );
    }

    /**
     * Exécute la relance pour un lead donné.
     * Appelé automatiquement par WP-Cron au moment planifié.
     *
     * @param int $lead_id
     */
    public static function executer_relance( int $lead_id ) {
        require_once PRAXIMET_PATH . 'includes/class-email-manager.php';
        PraxiMet_Email_Manager::envoyer_relance( $lead_id );
    }

    /**
     * Annule la relance planifiée pour un lead.
     * Appelé quand le statut passe à 'rdv_pris' ou 'archive'.
     *
     * @param int $lead_id
     */
    public static function annuler_relance( int $lead_id ) {
        $timestamp = wp_next_scheduled( 'praximet_relance_cron', [ $lead_id ] );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'praximet_relance_cron', [ $lead_id ] );
        }
    }

    /**
     * Vérifie si une relance est encore planifiée pour un lead.
     *
     * @param int $lead_id
     * @return bool
     */
    public static function relance_planifiee( int $lead_id ) {
        return (bool) wp_next_scheduled( 'praximet_relance_cron', [ $lead_id ] );
    }
}
