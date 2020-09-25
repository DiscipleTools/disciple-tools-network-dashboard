<?php
/**
 * Scheduled Cron Service
 */

if (!wp_next_scheduled('dt_network_dashboard_trigger_sites')) {
    wp_schedule_event(strtotime('tomorrow 4am'), 'daily', 'dt_network_dashboard_trigger_sites');
}
add_action('dt_network_dashboard_trigger_sites', 'dt_network_dashboard_remote_sites_trigger');

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
    $sites = DT_Network_Dashboard_Queries::remote_site_id_list();
    if ( empty( $sites ) ){
        dt_save_log( $file, 'No remote sites found to trigger.', false );
        return false;
    }

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