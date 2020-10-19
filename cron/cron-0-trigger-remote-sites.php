<?php
/**
 * Scheduled Cron Service
 */

if (!wp_next_scheduled('dt_network_dashboard_trigger_sites_1am')) {
    wp_schedule_event(strtotime('tomorrow 1:05 am'), 'daily', 'dt_network_dashboard_trigger_sites_1am');
}
add_action('dt_network_dashboard_trigger_sites_1am', 'dt_network_dashboard_trigger_sites');

if (!wp_next_scheduled('dt_network_dashboard_trigger_sites_2am')) {
    wp_schedule_event(strtotime('tomorrow 2:05 am'), 'daily', 'dt_network_dashboard_trigger_sites_2am');
}
add_action('dt_network_dashboard_trigger_sites_2am', 'dt_network_dashboard_trigger_sites');

if (!wp_next_scheduled('dt_network_dashboard_trigger_sites_3am')) {
    wp_schedule_event(strtotime('tomorrow 3:05 am'), 'daily', 'dt_network_dashboard_trigger_sites_3am');
}
add_action('dt_network_dashboard_trigger_sites_3am', 'dt_network_dashboard_trigger_sites');

if (!wp_next_scheduled('dt_network_dashboard_trigger_sites_4am')) {
    wp_schedule_event(strtotime('tomorrow 4:05 am'), 'daily', 'dt_network_dashboard_trigger_sites_4am');
}
add_action('dt_network_dashboard_trigger_sites_4am', 'dt_network_dashboard_trigger_sites');

if (!wp_next_scheduled('dt_network_dashboard_trigger_sites_5am')) {
    wp_schedule_event(strtotime('tomorrow 5:05 am'), 'daily', 'dt_network_dashboard_trigger_sites_5am');
}
add_action('dt_network_dashboard_trigger_sites_5am', 'dt_network_dashboard_trigger_sites');

if (!wp_next_scheduled('dt_network_dashboard_trigger_sites_6am')) {
    wp_schedule_event(strtotime('tomorrow 6:05 am'), 'daily', 'dt_network_dashboard_trigger_sites_6am');
}
add_action('dt_network_dashboard_trigger_sites_6am', 'dt_network_dashboard_trigger_sites');

function dt_network_dashboard_trigger_sites() {
    $file = 'trigger';

    if ( ! dt_is_todays_log( $file ) ) {
        dt_reset_log( $file );

        dt_save_log( $file, '', false );
        dt_save_log( $file, '*********************************************', false );
        dt_save_log( $file, 'RECENT SITE TRIGGER LOGS', false );
        dt_save_log( $file, 'Timestamp: ' . current_time( 'mysql' ), false );
        dt_save_log( $file, '*********************************************', false );
        dt_save_log( $file, '', false );
    }

    // Get list of sites
    $sites = DT_Network_Dashboard_Site_Post_Type::all_sites();
    if ( empty( $sites ) ){
        dt_save_log( $file, 'No remote sites found to trigger.', false );
        return false;
    }

    $remotes = [];
    $multisites = [];
    foreach ( $sites as $site ) {
        if ( 'remote' === $site['type'] ){
            $remotes[] = ['name' => $site['name'], 'type_id' => $site['type_id'] ];
        }
        if ( 'multisite' === $site['type'] ){
            $multisites[] = ['name' => $site['name'], 'type_id' => $site['type_id'] ];
        }
    }

    if ( ! empty( $remotes ) ) {
        $remote_chunks = array_chunk( $remotes, 30 );
        foreach ( $remote_chunks as $list ) {
            try {
                $task = new DT_Network_Dashboard_Cron_Trigger_Remotes();
                $task->launch(
                    [
                        'list' => $list,
                    ]
                );
            } catch ( Exception $e ) {
                dt_write_log( $e );
            }
        }
    }

    if ( ! empty( $multisites ) && dt_network_dashboard_multisite_is_approved() ) {
        $multisite_chunks = array_chunk( $multisites, 30 );
        foreach ( $multisite_chunks as $list ) {
            try {
                $task = new DT_Network_Dashboard_Cron_Trigger_Multisite();
                $task->launch(
                    [
                        'list' => $list,
                    ]
                );
            } catch ( Exception $e ) {
                dt_write_log( $e );
            }
        }
    }

    return true;
}

class DT_Network_Dashboard_Cron_Trigger_Remotes extends Disciple_Tools_Async_Task
{
    protected $action = 'trigger_remotes';
    protected function prepare_data( $data ) {
        return $data;
    }

    public function get_trigger_remotes() {
        if ( isset( $_POST[0]['list'] ) ) {

            $file = 'trigger';
            $list = recursive_sanitize_text_field( $_POST[0]['list'] );

            foreach ( $list as $item ) {
                try {
                    $site_vars = Site_Link_System::get_site_connection_vars( $item['type_id'], 'post_id' );
                    if ( is_wp_error( $site_vars ) ) {
                        dt_save_log( $file, 'FAIL: ' . $item['name'] . ' (Failed to get valid site link connection details)' );
                        continue;
                    }

                    dt_save_log( $file, 'START: ' . $item['name'] . ' ('. $site_vars['url'] .')' );

                    // Send remote request
                    $args = [
                        'method' => 'GET',
                    ];
                    $result = wp_remote_get( 'https://' . $site_vars['url'] . '/wp-cron.php', $args );
                    if ( is_wp_error( $result ) ) {
                        dt_save_log( $file, 'FAIL: ' . $item['name'] . ' (Failed in connection to remote site.)' );
                        dt_save_log( $file, maybe_serialize( $result ) );
                        continue;
                    }

                    dt_save_log( $file, 'SUCCESS: ' . $item['name'] );

                } catch ( Exception $e ) {
                    dt_write_log( $e );
                }
            }

        }
        else {
            dt_write_log( __METHOD__ . ' : Failed on post array' );
        }
    }

    protected function run_action() {}
}
function dt_load_async_trigger_remotes() {
    if ( isset( $_POST['_wp_nonce'] )
        && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wp_nonce'] ) ) )
        && isset( $_POST['action'] )
        && sanitize_key( wp_unslash( $_POST['action'] ) ) == 'dt_async_trigger_remotes' ) {
        try {
            $object = new DT_Network_Dashboard_Cron_Trigger_Remotes();
            $object->get_trigger_remotes();
        } catch ( Exception $e ) {
            dt_write_log( __METHOD__ . ': Failed to call site snapshot' );
            return new WP_Error( __METHOD__, 'Failed to send email with Async' );
        }
    }
    return 1;
}
add_action( 'init', 'dt_load_async_trigger_remotes' );


/**
 * Class DT_Network_Dashboard_Cron_Trigger_Multisite
 *
 * Headless async service that retrieves the single site snapshot
 */
class DT_Network_Dashboard_Cron_Trigger_Multisite extends Disciple_Tools_Async_Task
{
    protected $action = 'trigger_multisites';
    protected function prepare_data( $data ) {
        return $data;
    }

    public function get_trigger_multisites() {
        if ( isset( $_POST[0]['list'] ) ) {

            $file = 'trigger';
            $list = recursive_sanitize_text_field( $_POST[0]['list'] );

            foreach ( $list as $item ) {
                try {
                    dt_write_log($item['name']);
                    $site_url = get_blog_option($item['type_id'], 'siteurl');

                    dt_save_log( $file, 'START: ' . $item['name'] . ' ('. $site_url .')' );

                    // Send remote request
                    $args = [
                        'method' => 'GET',
                    ];
                    $result = wp_remote_get(  trailingslashit( $site_url ) . 'wp-cron.php', $args );
                    if ( is_wp_error( $result ) ) {
                        dt_save_log( $file, 'FAIL: ' . $item['name'] . ' (Failed in connection to remote site.)' );
                        dt_save_log( $file, maybe_serialize( $result ) );
                        continue;
                    } else {
                        dt_save_log( $file, 'SUCCESS: ' . $item['name'] );
                    }

                } catch ( Exception $e ) {
                    dt_write_log( $e );
                }
            }

        }
        else {
            dt_write_log( __METHOD__ . ' : Failed on post array' );
        }
    }

    protected function run_action() {}
}
function dt_load_async_trigger_multisites() {
    if ( isset( $_POST['_wp_nonce'] )
        && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wp_nonce'] ) ) )
        && isset( $_POST['action'] )
        && sanitize_key( wp_unslash( $_POST['action'] ) ) == 'dt_async_trigger_multisites' ) {
        try {
            $object = new DT_Network_Dashboard_Cron_Trigger_Multisite();
            $object->get_trigger_multisites();
        } catch ( Exception $e ) {
            dt_write_log( __METHOD__ . ': Failed to call site snapshot' );
            return new WP_Error( __METHOD__, 'Failed to send email with Async' );
        }
    }
    return 1;
}
add_action( 'init', 'dt_load_async_trigger_multisites' );