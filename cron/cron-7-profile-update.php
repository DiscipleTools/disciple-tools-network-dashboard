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
if ( !wp_next_scheduled( 'dt_network_dashboard_profile_update' )) {
    wp_schedule_event( strtotime( 'tomorrow 6 am' ), 'daily', 'dt_network_dashboard_profile_update' );
}
add_action( 'dt_network_dashboard_profile_update', 'dt_network_dashboard_profiles_update' );


/**
 * Run collection process
 */
function dt_network_dashboard_profiles_update() {

    $file = 'profile-collection';

    if ( !dt_is_todays_log( $file )) {
        dt_reset_log( $file );

        dt_save_log( $file, '', false );
        dt_save_log( $file, '*********************************************', false );
        dt_save_log( $file, 'PROFILE LOGS', false );
        dt_save_log( $file, 'Timestamp: ' . date( 'Y-m-d', time() ), false );
        dt_save_log( $file, '*********************************************', false );
        dt_save_log( $file, '', false );
    }

    // Get list of sites
    $sites = DT_Network_Dashboard_Site_Post_Type::all_sites();
    if (empty( $sites )) {
        dt_save_log( $file, 'No sites found to collect.', false );
        return false;
    }

    // Remote Profile Collection
    foreach ($sites as $site) {
        if ($site['id'] === get_current_blog_id()) {
            continue;
        }
        if ( $site['type'] === 'multisite' ){
            continue;
        }

        DT_Network_Dashboard_Site_Post_Type::update_remote_site_profile_by_id( $site['type_id'] );
        dt_save_log( $file, 'Updated Site ' . $site['name'], false );
    }

    /* Multisite Collection */
    if (dt_is_current_multisite_dashboard_approved()) {
        // collect multisite
        foreach ($sites as $site) {
            if ( $site['id'] === get_current_blog_id()) {
                continue;
            }
            if ( $site['type'] === 'remote' ){
                continue;
            }

            switch_to_blog( $site['type_id'] );

            $profile = dt_network_site_profile();

            restore_current_blog();

            if ( ! empty( $profile ) ) {
                update_post_meta( $site['id'], 'profile', $profile );
                dt_save_log( $file, 'Updated Site ' . $site['name'], false );
            }
        }
    }

    return true;
}