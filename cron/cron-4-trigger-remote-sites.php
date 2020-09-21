<?php
/**
 * Scheduled Cron Service
 */

new DT_Network_Dashboard_Cron_Sites_Trigger_Scheduler();
try {
    new DT_Network_Dashboard_Cron_Sites_Trigger_Async();
} catch (Exception $e) {
    dt_write_log($e);
}

// run directly from external cron
add_action( 'dt_network_dashboard_external_cron', 'dt_network_dashboard_remote_sites_trigger' );


// Begin Schedule daily cron build
class DT_Network_Dashboard_Cron_Sites_Trigger_Scheduler
{

    public function __construct()
    {
        if (!wp_next_scheduled('dt_network_dashboard_trigger_sites')) {
            wp_schedule_event(strtotime('tomorrow 4am'), 'daily', 'dt_network_dashboard_trigger_sites');
        }
        add_action('dt_network_dashboard_trigger_sites', [$this, 'action']);
    }

    public static function action()
    {
        do_action("dt_network_dashboard_trigger_sites");
    }
}

class DT_Network_Dashboard_Cron_Sites_Trigger_Async extends Disciple_Tools_Async_Task
{

    protected $action = 'dt_network_dashboard_trigger_sites';

    protected function prepare_data($data)
    {
        return $data;
    }

    protected function run_action()
    {
        dt_network_dashboard_remote_sites_trigger();
    }
}


function dt_network_dashboard_remote_sites_trigger() {
    $file = 'trigger';
    dt_reset_log( $file );

    dt_save_log( $file, '', false );
    dt_save_log( $file, '*********************************************', false );
    dt_save_log( $file, 'RECENT SITE TRIGGER LOGS', false );
    dt_save_log( $file, 'Timestamp: ' . current_time( 'mysql' ), false );
    dt_save_log( $file, '*********************************************', false );
    dt_save_log( $file, '', false );

    // Get list of sites
    $sites = DT_Network_Dashboard_Queries::site_link_list();

    $status = [
        'success' => 0,
        'fail' => 0,
    ];

    // Loop sites and call their wp-cron.php service to run.
    foreach ( $sites as $site ) {
        try {
            $site_post_id = $site['id'] ?? 0;

            $file = 'trigger';
            dt_save_log( $file, 'START ID: ' . $site_post_id );

            $site = Site_Link_System::get_site_connection_vars( $site_post_id, 'post_id' );
            if ( is_wp_error( $site ) ) {
                dt_save_log( $file, 'FAIL ID: ' . $site_post_id . ' (Failed to get valid site link connection details)' );
                return false;
            }

            // Send remote request
            $args = [
                'method' => 'GET',
            ];
            $result = wp_remote_get( 'https://' . $site['url'] . '/wp-cron.php', $args );
            if ( is_wp_error( $result ) ) {
                dt_save_log( $file, 'FAIL ID: ' . $site_post_id . ' (Failed in connection to remote site.)' );
                dt_save_log( $file, maybe_serialize( $result ) );
                return false;
            }

            dt_save_log( $file, 'SUCCESS ID: ' . $site_post_id );

            $status['success'] = $status['success'] + 1;
        } catch ( Exception $e ) {
            dt_write_log( $e );
            $status['fail'] = $status['fail'] + 1;
        }
    }
    $status['timestamp'] = current_time( 'mysql' );
    dt_write_log($status);
    return $status;
}


/**
 * Class DT_Get_Single_Site_Snapshot_Trigger
 *
 * Headless async service that retrieves the single site snapshot
 */
class DT_Get_Single_Site_Snapshot_Trigger extends Disciple_Tools_Async_Task
{
    protected $action = 'site_trigger';

    protected function prepare_data($data)
    {
        return $data;
    }

    public function get_site_trigger()
    {
        if (isset($_POST[0]['site_post_id'])) {
            dt_get_site_trigger(sanitize_key(wp_unslash($_POST[0]['site_post_id'])));
        } else {
            dt_write_log(__METHOD__ . ' : Failed on post array');
        }
    }

    protected function run_action()
    {
    }
}

function dt_load_async_site_trigger()
{
    if (isset($_POST['_wp_nonce'])
        && wp_verify_nonce(sanitize_key(wp_unslash($_POST['_wp_nonce'])))
        && isset($_POST['action'])
        && sanitize_key(wp_unslash($_POST['action'])) == 'dt_async_site_trigger') {
        try {
            $remote_post = new DT_Get_Single_Site_Snapshot_Trigger();
            $remote_post->get_site_trigger();
        } catch (Exception $e) {
            dt_write_log(__METHOD__ . ': Failed to call site snapshot');
            return new WP_Error(__METHOD__, 'Failed to run process with Async');
        }
    }
    return 1;
}
add_action('init', 'dt_load_async_site_trigger');

function dt_get_site_trigger( $site_post_id ){

    $file = 'trigger';
    dt_save_log( $file, 'START ID: ' . $site_post_id );

    $site = Site_Link_System::get_site_connection_vars( $site_post_id, 'post_id' );
    if ( is_wp_error( $site ) ) {
        dt_save_log( $file, 'FAIL ID: ' . $site_post_id . ' (Failed to get valid site link connection details)' );
        return false;
    }

    // Send remote request
    $args = [
        'method' => 'GET',
    ];
    $result = wp_remote_get( 'https://' . $site['url'] . '/wp-cron.php', $args );
    if ( is_wp_error( $result ) ) {
        dt_save_log( $file, 'FAIL ID: ' . $site_post_id . ' (Failed in connection to remote site.)' );
        dt_save_log( $file, maybe_serialize( $result ) );
        return false;
    }

    $snapshot = json_decode( $result['body'], true );
    if ( $snapshot['status'] == 'FAIL' ) {
        dt_save_log( $file, 'FAIL ID: ' . $site_post_id . ' (Connection success, but data collection failed in remote site.)' );
        dt_save_log( $file, maybe_serialize( $snapshot ) );
        return false;
    }

    dt_save_log( $file, 'SUCCESS ID: ' . $site_post_id );

    return true;
}