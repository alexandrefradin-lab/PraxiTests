<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PP_Relances {

    /** Nom unique du hook cron — référence centrale pour tout le plugin. */
    const CRON_HOOK = 'pp_cron_relances';

    public static function init() {
        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
            wp_schedule_event( time(), 'daily', self::CRON_HOOK );
        }
        add_action( self::CRON_HOOK, array( __CLASS__, 'run' ) );
    }

    /** Déplanifie le cron — appelé à la désactivation du plugin. */
    public static function unschedule() {
        $ts = wp_next_scheduled( self::CRON_HOOK );
        if ( $ts ) wp_unschedule_event( $ts, self::CRON_HOOK );
    }

    public static function run() {
        // Respecter le réglage admin
        if ( ! get_option( 'pp_relances_actives', 1 ) ) {
            PP_Logger::info( 'relances', 'Relances désactivées dans les réglages — cron ignoré.' );
            return;
        }

        $total = 0;
        foreach ( array( 3, 8 ) as $jours ) {
            $rows = PP_DB::get_pending_relances( $jours );
            foreach ( $rows as $row ) {
                $sent = PP_Mailer::envoyer_relance( $row, $jours );
                PP_DB::mark_relance( $row->id, $jours );
                if ( $sent ) $total++;
            }
        }

        if ( $total > 0 ) {
            PP_Logger::info( 'relances', "Cron relances : {$total} email(s) envoyé(s)." );
        }
    }
}
