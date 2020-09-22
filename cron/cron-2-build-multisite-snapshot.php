<?php
/**
 * Scheduled Cron Service
 */

if ( dt_is_current_multisite_dashboard_approved() ) {

    if (!wp_next_scheduled('dt_network_dashboard_collect_multisite_snapshots')) {
        wp_schedule_event(strtotime('tomorrow 2am'), 'daily', 'dt_network_dashboard_collect_multisite_snapshots');
    }
    add_action('dt_network_dashboard_collect_multisite_snapshots', 'dt_network_dashboard_multisite_snapshot_async');
}

/**
 * Run collection process
 */
function dt_network_dashboard_multisite_snapshot_async() {
    $limit = 50; // set max loops before spawning new cron

    $file = 'multisite';

    if ( ! dt_is_todays_log( $file ) ) {
        dt_reset_log( $file );

        dt_save_log( $file, '', false );
        dt_save_log( $file, '*********************************************', false );
        dt_save_log( $file, 'MULTISITE SNAPSHOT LOGS', false );
        dt_save_log( $file, 'Timestamp: ' . date( 'Y-m-d', time() ), false );
        dt_save_log( $file, '*********************************************', false );
        dt_save_log( $file, '', false );
    }

    // Get list of sites
    $sites = DT_Network_Dashboard_Queries::multisite_sites_needing_snapshot_refreshed();
    if ( empty( $sites ) ){
        dt_save_log( $file, 'No sites found to collect.', false );
        return false;
    }

    if ( count( $sites ) > $limit ) {
        /* if more than the limit of sites, spawn another event to process. This will keep spawning until all sites are reduced.*/
        wp_schedule_single_event( strtotime('+20 minutes'), 'dt_network_dashboard_collect_multisite_snapshots' );
    }

    // Loop sites through a second async task, so that each will become and individual async process.
    $i = 0;
    foreach ( $sites as $site_id ) {
        try {
            $task = new DT_Get_Single_Multisite_Snapshot();
            $task->launch(
                [
                    'blog_id' => $site_id
                ]
            );
        } catch ( Exception $e ) {
            dt_write_log( $e );
        }

        if ( $i >= $limit ){
            break;
        }
        $i++;
    }

    return true;
}

/**
 * Class DT_Get_Single_Site_Snapshot
 *
 * Headless async service that retrieves the single site snapshot
 */
class DT_Get_Single_Multisite_Snapshot extends Disciple_Tools_Async_Task
{
    protected $action = 'multisite_snapshot';

    protected function prepare_data( $data ) {
        return $data;
    }

    public function get_multisite_snapshot() {

        if ( isset( $_POST[0]['blog_id'] ) ) {
            dt_network_dashboard_collect_multisite( $_POST[0]['blog_id'] );
        }
        else {
            dt_write_log( __METHOD__ . ' : Failed on post array' );
        }
    }

    protected function run_action() {}
}
function dt_load_async_multisite_snapshot() {
    if ( isset( $_POST['_wp_nonce'] )
        && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wp_nonce'] ) ) )
        && isset( $_POST['action'] )
        && sanitize_key( wp_unslash( $_POST['action'] ) ) == 'dt_async_multisite_snapshot' ) {
        try {
            $object = new DT_Get_Single_Multisite_Snapshot();
            $object->get_multisite_snapshot();
        } catch ( Exception $e ) {
            dt_write_log( __METHOD__ . ': Failed to call site snapshot' );
        }
    }
}
add_action( 'init', 'dt_load_async_multisite_snapshot' );

/**
 * @param $blog_id
 *
 * @return bool
 */
function dt_network_dashboard_collect_multisite( $blog_id ) {

    $file = 'multisite';
    dt_save_log( $file, 'START ID: ' . $blog_id );

    switch_to_blog( $blog_id );

    $snapshot = DT_Network_Dashboard_Snapshot_Report::snapshot_report( true );
    if ( $snapshot['status'] == 'FAIL' ) {
        // retry connection in 3 seconds
        sleep( 5 );
        dt_save_log( $file, 'RETRY ID: ' . $blog_id . ' (Payload = FAIL)' );
        $snapshot = DT_Network_Dashboard_Snapshot_Report::snapshot_report( true ); // @todo remove true after development
        if ( $snapshot['status'] == 'FAIL' ) {

            dt_save_log( $file, 'FAIL ID: ' . $blog_id . ' (Unable to run snapshot report for '.$blog_id.')' );
            dt_save_log( $file, maybe_serialize( $snapshot ) );
            restore_current_blog();
            return false;
        }
    }

    restore_current_blog();

    dt_save_log( $file, 'SUCCESS ID: ' . $blog_id );

    return true;
}
