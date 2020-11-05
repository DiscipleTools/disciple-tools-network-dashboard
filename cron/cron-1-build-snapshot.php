<?php
/**
 * Scheduled Cron Service
 */

if ( !wp_next_scheduled( 'dt_network_dashboard_build_snapshot' )) {
    wp_schedule_event( strtotime( 'tomorrow 1am' ), 'daily', 'dt_network_dashboard_build_snapshot' );
}
add_action( 'dt_network_dashboard_build_snapshot', array( 'DT_Network_Dashboard_Snapshot', 'snapshot_report' ) );
