<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PE_Relances {

    const CRON_HOOK = 'pemo_cron_relances';

    public static function init() {
        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
            wp_schedule_event( time(), 'daily', self::CRON_HOOK );
        }
        add_action( self::CRON_HOOK, array( __CLASS__, 'run' ) );
    }

    public static function unschedule() {
        $ts = wp_next_scheduled( self::CRON_HOOK );
        if ( $ts ) wp_unschedule_event( $ts, self::CRON_HOOK );
    }

    public static function run() {
        if ( ! get_option( 'pemo_relances_actives', 1 ) ) {
            PE_Logger::info( 'relances', 'Relances désactivées dans les réglages — cron ignoré.' );
            return;
        }
        $total = 0;
        foreach ( array( 3, 8 ) as $jours ) {
            $rows = PE_DB::get_pending_relances( $jours );
            foreach ( $rows as $row ) {
                $sent = PE_Mailer::envoyer_relance( $row, $jours );
                PE_DB::mark_relance( $row->id, $jours );
                if ( $sent ) $total++;
            }
        }
        if ( $total > 0 ) {
            PE_Logger::info( 'relances', "Cron relances : {$total} email(s) envoyé(s)." );
        }
    }
}
