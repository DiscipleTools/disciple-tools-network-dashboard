<?php
/**
 * Scheduled Cron Service
 *
 * One cron service runs on submitting site, and triggers recieving site to make collection. (This is because the size limit on
 * a wp_remote_post body section will likely truncate the number of records that can be submitted. There appears to be a
 * 1024k size limit. So using the push element to not send data, but trigger a collection gets around this problem, because there is
 * no size limits on a collection post.
 * @link https://wordpress.stackexchange.com/questions/301451/wp-remote-post-doesnt-work-with-more-than-1024-bytes-in-the-body
 *
 */

/**
 * Scheduled Cron Service
 */
if (!wp_next_scheduled('dt_network_dashboard_collect_remote_activity_logs')) {
    wp_schedule_event(strtotime('tomorrow 4:30 am'), 'daily', 'dt_network_dashboard_collect_remote_activity_logs');
}
add_action('dt_network_dashboard_collect_remote_activity_logs', 'dt_network_dashboard_collect_remote_activity_logs');

/**
 * Run collection process
 */
function dt_network_dashboard_collect_remote_activity_logs()
{

    $limit = 100; // set max loops before spawning new cron

    $file = 'activity-remote';

    if (!dt_is_todays_log($file)) {
        dt_reset_log($file);

        dt_save_log($file, '', false);
        dt_save_log($file, '*********************************************', false);
        dt_save_log($file, 'REMOTE ACTIVITY LOGS', false);
        dt_save_log($file, 'Timestamp: ' . date('Y-m-d', time()), false);
        dt_save_log($file, '*********************************************', false);
        dt_save_log($file, '', false);
    }

    // Get list of sites
    $sites = DT_Network_Dashboard_Site_Post_Type::all_remote_sites();
    if (empty($sites)) {
        dt_save_log($file, 'No sites found to collect.', false);
        return false;
    }

    if (count($sites) > $limit) {
        /* if more than the limit of sites, spawn another event to process. This will keep spawning until all sites are reduced.*/
        wp_schedule_single_event(strtotime('+5 minutes'), 'dt_network_dashboard_collect_remote_activity_logs');
    }

    // Loop sites through a second async task, so that each will become and individual async process.
    $i = 0;
    foreach ($sites as $site) {
        if ($site['partner_id'] === dt_network_site_id() ) {
            continue;
        }

        dt_network_dashboard_collect_remote_activity_single($site);

        if ($i >= $limit) {
            break;
        }
        $i++;
    }

    return true;
}


/**
 * @param $blog_id
 *
 * @return bool
 */
function dt_network_dashboard_collect_remote_activity_single($site)
{

    $file = 'activity-remote';
    
    global $wpdb;
    $last_site_record_id = $wpdb->get_var($wpdb->prepare( "SELECT MAX( site_record_id ) FROM $wpdb->dt_movement_log WHERE site_id = %s", $site['partner_id'] ) );
    if ( is_wp_error( $last_site_record_id ) ){
        dt_save_log( $file, 'FAIL ID: ' . $site['type_id'] );
        dt_save_log( $file, $last_site_record_id );
        return false;
    }
    if ( empty( $last_site_record_id ) ){
        $last_site_record_id = 0;
    }

    $site_vars = Site_Link_System::get_site_connection_vars( $site['type_id'], 'post_id');
    if (is_wp_error($site_vars)) {
        dt_save_log(  $file, 'FAIL ID: ' . $site['id'] . ' (Failed to get valid site link connection details)');
        return false;
    }

    $args = [
        'method' => 'POST',
        'body' => [
            'transfer_token' => $site_vars['transfer_token'],
            'last_site_record_id' => $last_site_record_id,
        ]
    ];

    $results = wp_remote_post( 'https://' . $site_vars['url'] . '/wp-json/dt-public/v1/network_dashboard/activity', $args );
    if (is_wp_error($results) ) {
        dt_save_log(  $file, 'FAIL ID: ' . $site_vars['url'] . ' (Failed to get valid site link connection details)');
        return false;
    }
    if ( isset( $results['body'] ) && empty( $results['body'] ) ) {
        return false;
    }

    $data = json_decode( $results['body'], true );

    if( 'SUCCESS' !== $data['status'] ){
        dt_save_log(  $file, 'FAIL ID: ' . $site['id'] . $data['error'] ?? '' );
        return false;
    }

    foreach( $data['data'] as $index => $row ){
        DT_Network_Activity_Log::transfer_insert( $row );
    }

    dt_save_log( $file, 'SUCCESS ID: ' . $site['type_id'] );

    return true;
}
