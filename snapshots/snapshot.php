<?php
/**
 * Core functions to power the network features of Disciple.Tools
 *
 * @class      Disciple_Tools_Notifications
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple_Tools
 */

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class DT_Network_Dashboard_Snapshot
{
    public static function snapshot_report( $force_refresh = false ) {
        /* Check last process */
        $time = get_option( 'dt_snapshot_report_timestamp' );
        if ( $time < ( time() - ( 24 * 60 * 60 ) ) ) {
            $force_refresh = true;
        }

        if ( !$force_refresh ) {
            return get_option( 'dt_snapshot_report' );
        }

        $profile = dt_network_site_profile();

        $report_data = array(
            'partner_id' => $profile['partner_id'],
            'profile' => $profile,
        );

        /**
         * Primary filter to hook and add elements to the snapshot
         *
         */
        $report_data = apply_filters( 'dt_network_dashboard_snapshot_report', $report_data );


        $report_data['hash'] = hash( 'sha256', maybe_serialize( $report_data ) );
        $report_data['date'] = current_time( 'timestamp' );
        $report_data['timestamp'] = time();
        $report_data['status'] = 'OK';

        if ( $report_data ) {
            update_option( 'dt_snapshot_report', $report_data, false );
            update_option( 'dt_snapshot_report_timestamp', time(), false );

            return $report_data;
        } else {
            return new WP_Error( __METHOD__, 'Failed to get report' );
        }
    }
}

