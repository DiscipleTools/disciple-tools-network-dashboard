<?php
/**
 * Scheduled Cron Service
 */

if (!wp_next_scheduled('dt_network_dashboard_push_snapshot')) {
    wp_schedule_event(strtotime('tomorrow 3am'), 'daily', 'dt_network_dashboard_push_snapshot');
}
add_action('dt_network_dashboard_push_snapshot', 'dt_network_dashboard_push_snapshots');

function dt_network_dashboard_push_snapshots()
{
    $sites = DT_Network_Dashboard_Snapshot_Queries::dashboards_to_report_to();

    if ( empty( $sites ) ){
        return false;
    }

    $status = [
        'success' => 0,
        'fail' => 0,
    ];

    $snapshot = DT_Network_Dashboard_Snapshot_Report::snapshot_report();
    if ( is_wp_error( $snapshot ) ){
        dt_write_log(__METHOD__, 'Failed to send report because snapshot collection failed' );
        return false;
    }

    // Loop sites and call their wp-cron.php service to run.
    foreach ($sites as $site) {
        try {
            $site_post_id = $site['id'] ?? 0;

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
                    'snapshot' => $snapshot
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