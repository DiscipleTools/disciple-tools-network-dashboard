<?php
/**
 * Scheduled Cron Service
 */

if (!wp_next_scheduled('dt_network_dashboard_collect_remote_sites')) {
    wp_schedule_event(strtotime('tomorrow 5am'), 'daily', 'dt_network_dashboard_collect_remote_sites');
}
add_action('dt_network_dashboard_collect_remote_sites', 'dt_network_dashboard_collect_remote_sites');

function dt_network_dashboard_collect_remote_sites() {
    $limit = 20;  // set max loops before spawning new cron

    $file = 'remote';
    if ( ! dt_is_todays_log( $file ) ) {
        dt_reset_log($file);

        dt_save_log($file, '', false);
        dt_save_log($file, '*********************************************', false);
        dt_save_log($file, 'RECENT SNAPSHOT LOGS', false);
        dt_save_log($file, 'Timestamp: ' . date( 'Y-m-d', time()), false);
        dt_save_log($file, '*********************************************', false);
        dt_save_log($file, '', false);
    }

    // Get list of sites
    $sites = DT_Network_Dashboard_Queries::remote_sites_needing_snapshot_refreshed();
    if ( empty( $sites ) ){
        dt_save_log( $file, 'No remote sites found to trigger.', false );
        return false;
    }

    if ( count( $sites ) > $limit ) {
        /* if more than the limit of sites, spawn another event to process. This will keep spawning until all sites are reduced.*/
        wp_schedule_single_event( strtotime('+20 minutes'), 'dt_network_dashboard_collect_remote_sites' );
    }

    // Loop sites through a second async task, so that each will become and individual async process.
    $i = 0;
    foreach ( $sites as $site ) {
        try {
            $task = new DT_Get_Single_Site_Snapshot();
            $task->launch(
                [
                    'site_post_id' => $site,
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

function dt_load_async_site_snapshot() {
    if ( isset( $_POST['_wp_nonce'] )
        && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wp_nonce'] ) ) )
        && isset( $_POST['action'] )
        && sanitize_key( wp_unslash( $_POST['action'] ) ) == 'dt_async_site_snapshot' ) {
        try {
            $object = new DT_Get_Single_Site_Snapshot();
            $object->get_site_snapshot();
        } catch ( Exception $e ) {
            dt_write_log( __METHOD__ . ': Failed to call site snapshot' );
            return new WP_Error( __METHOD__, 'Failed to send email with Async' );
        }
    }
    return 1;
}
add_action( 'init', 'dt_load_async_site_snapshot' );

/**
 * Class DT_Get_Single_Site_Snapshot
 *
 * Headless async service that retrieves the single site snapshot
 */
class DT_Get_Single_Site_Snapshot extends Disciple_Tools_Async_Task
{
    protected $action = 'site_snapshot';
    protected function prepare_data( $data ) {
        return $data;
    }

    public function get_site_snapshot() {
        if ( isset( $_POST[0]['site_post_id'] ) ) {
            dt_get_site_snapshot( sanitize_key( wp_unslash( $_POST[0]['site_post_id'] ) ) );
        }
        else {
            dt_write_log( __METHOD__ . ' : Failed on post array' );
        }
    }

    protected function run_action() {}
}

/**
 * @param $site_post_id
 *
 * @return bool
 */
function dt_get_site_snapshot( $site_post_id ) {
    update_post_meta( $site_post_id, 'snapshot_fail', true );

    $file = 'remote';
    dt_save_log( $file, 'START ID: ' . $site_post_id );

    $site = Site_Link_System::get_site_connection_vars( $site_post_id, 'post_id' );
    if ( is_wp_error( $site ) ) {
        delete_post_meta( $site_post_id, 'snapshot' );
        delete_post_meta( $site_post_id, 'snapshot_date' );
        update_post_meta( $site_post_id, 'snapshot_fail', $site );

        dt_save_log( $file, 'FAIL ID: ' . $site_post_id . ' (Failed to get valid site link connection details)' );
        return false;
    }

    // Send remote request
    $args = [
        'method' => 'POST',
        'body' => [
            'transfer_token' => $site['transfer_token'],
        ]
    ];
    $result = wp_remote_post( 'https://' . $site['url'] . '/wp-json/dt-public/v1/network_dashboard/live_stats', $args );
    if ( is_wp_error( $result ) ) {
        // retry connection in 3 seconds
        sleep( 10 );
        dt_save_log( $file, 'RETRY ID: ' . $site_post_id . ' (WP_Remote_Post Error)' );
        $result = wp_remote_post( 'https://' . $site['url'] . '/wp-json/dt-public/v1/network_dashboard/live_stats', $args );
    }
    if ( is_wp_error( $result ) ) {
        update_post_meta( $site_post_id, 'snapshot_fail', maybe_serialize( $result ) );

        dt_save_log( $file, 'FAIL ID: ' . $site_post_id . ' (Failed in connection to remote site.)' );
        dt_save_log( $file, maybe_serialize( $result ) );
        return false;
    }

    $snapshot = json_decode( $result['body'], true );
    if ( $snapshot['status'] == 'FAIL' ) {
        // retry connection in 3 seconds
        sleep( 10 );
        dt_save_log( $file, 'RETRY ID: ' . $site_post_id . ' (Payload = FAIL)' );
        $result = wp_remote_post( 'https://' . $site['url'] . '/wp-json/dt-public/v1/network_dashboard/live_stats', $args );
        if ( is_wp_error( $result ) ) {
            update_post_meta( $site_post_id, 'snapshot_fail', maybe_serialize( $result ) );

            dt_save_log( $file, 'FAIL ID: ' . $site_post_id . ' (Failed in connection to remote site.)' );
            dt_save_log( $file, maybe_serialize( $result ) );
            return false;
        }
        $snapshot = json_decode( $result['body'], true );
    }
    if ( $snapshot['status'] == 'FAIL' ) {
        update_post_meta( $site_post_id, 'snapshot_fail', $result );

        dt_save_log( $file, 'FAIL ID: ' . $site_post_id . ' (Connection success, but data collection failed in remote site.)' );
        dt_save_log( $file, maybe_serialize( $snapshot ) );
        return false;
    }

    if ( isset( $snapshot['timestamp'] ) ) {
        $timestamp = $snapshot['timestamp'];
    } else {
        $timestamp = current_time( 'timestamp' );
    }

    /* SAVE PROCESS */

    if ( ! get_post_meta( $site_post_id, 'partner_id', true ) && isset( $snapshot['partner_id'] ) ) {
        update_post_meta( $site_post_id, 'partner_id', $snapshot['partner_id'] );
    }
    if ( ! get_post_meta( $site_post_id, 'partner_name', true ) && isset( $snapshot['profile']['partner_name'] ) ) {
        update_post_meta( $site_post_id, 'partner_name', $snapshot['profile']['partner_name'] );
    }
    if ( ! get_post_meta( $site_post_id, 'partner_description', true ) && isset( $snapshot['profile']['partner_description'] ) ) {
        update_post_meta( $site_post_id, 'partner_description', $snapshot['profile']['partner_description'] );
    }
    if ( ! get_post_meta( $site_post_id, 'partner_url', true ) && isset( $snapshot['profile']['partner_url'] ) ) {
        update_post_meta( $site_post_id, 'partner_url', $snapshot['profile']['partner_url'] );
    }

    update_post_meta( $site_post_id, 'snapshot', $snapshot );
    update_post_meta( $site_post_id, 'snapshot_date', $timestamp );
    update_post_meta( $site_post_id, 'snapshot_fail', false );

    dt_save_log( $file, 'SUCCESS ID: ' . $site_post_id );

    return true;
}
