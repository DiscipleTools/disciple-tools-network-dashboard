<?php
/**
 * Scheduled Cron Service
 */

if (!wp_next_scheduled('dt_network_dashboard_push_activity')) {
    wp_schedule_event(strtotime('tomorrow 3am'), 'hourly', 'dt_network_dashboard_push_activity');
}
add_action('dt_network_dashboard_push_activity', 'dt_network_dashboard_push_activity');

function dt_network_dashboard_push_activity()
{
    global $wpdb;
    $local_site_id = dt_network_site_id();

    $sites = DT_Network_Dashboard_Snapshot_Queries::dashboards_to_report_activity_to();

    if ( empty( $sites ) ){
        return false;
    }

    $status = [
        'success' => 0,
        'fail' => 0,
    ];


    // Loop sites and call their wp-cron.php service to run.
    foreach ($sites as $site) {
        try {
            $site_post_id = $site['id'];

            $activity = $wpdb->get_results($wpdb->prepare( "
                SELECT * 
                FROM $wpdb->dt_movement_log
                WHERE site_id = %s
                AND id > %s
                ", $local_site_id, $site['last_activity_id']), ARRAY_A );


            // @todo  convert activity log into submit format


            $site = Site_Link_System::get_site_connection_vars( $site_post_id, 'post_id');
            if (is_wp_error($site)) {
                dt_write_log( __METHOD__, 'FAIL ID: ' . $site_post_id . ' (Failed to get valid site link connection details)');
                continue;
            }

            // Send remote request
            $args = [
                'method' => 'POST',
                'body' => [
                    'transfer_token' => $site['transfer_token'],
                    'activity' => $activity
                ]
            ];
            $result = wp_remote_post( 'https://' . $site['url'] . '/wp-json/dt-public/v1/network_dashboard/collector', $args );
            if (is_wp_error($result)) {
                dt_write_log(__METHOD__, 'FAIL ID: ' . $site_post_id . ' (Failed in connection to remote site.)');
                dt_write_log(__METHOD__, maybe_serialize($result));
                continue;
            }

            dt_write_log(__METHOD__, 'SUCCESS ID: ' . $site_post_id);

            $status['success'] = $status['success'] + 1;
        } catch (Exception $e) {
            dt_write_log($e);
            $status['fail'] = $status['fail'] + 1;
        }
    }
    return true;
}