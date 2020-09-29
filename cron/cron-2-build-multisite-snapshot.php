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
function dt_network_dashboard_multisite_snapshot_async( ) {
    $limit = 100; // set max loops before spawning new cron

    DT_Network_Dashboard_Site_Post_Type::sync_all_multisites_to_post_type();

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
    $sites = DT_Network_Dashboard_Site_Post_Type::multisite_sites_needing_snapshot_refreshed();
    if ( empty( $sites ) ){
        dt_save_log( $file, 'No sites found to collect.', false );
        return false;
    }

    if ( count( $sites ) > $limit ) {
        /* if more than the limit of sites, spawn another event to process. This will keep spawning until all sites are reduced.*/
        wp_schedule_single_event( strtotime('+5 minutes'), 'dt_network_dashboard_collect_multisite_snapshots' );
    }

    // Loop sites through a second async task, so that each will become and individual async process.
    $i = 0;
    foreach ( $sites as $site_id ) {

        dt_network_dashboard_collect_multisite( $site_id );

        if ( $i >= $limit  ){
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
function dt_network_dashboard_collect_multisite( $blog_id ) {

    $file = 'multisite';
    dt_save_log( $file, 'START ID: ' . $blog_id );

    switch_to_blog( $blog_id );

    $profile = dt_network_site_profile();
    $snapshot = DT_Network_Dashboard_Snapshot::snapshot_report( true );
    if ( $snapshot['status'] == 'FAIL' ) {
        // retry connection in 3 seconds
        sleep( 5 );
        dt_save_log( $file, 'RETRY ID: ' . $blog_id . ' (Payload = FAIL)' );
        $snapshot = DT_Network_Dashboard_Snapshot::snapshot_report( true ); // @todo remove true after development
        if ( $snapshot['status'] == 'FAIL' ) {
            dt_save_log( $file, 'FAIL ID: ' . $blog_id . ' (Unable to run snapshot report for '.$blog_id.')' );
            dt_save_log( $file, maybe_serialize( $snapshot ) );
            restore_current_blog();
            return false;
        }
    }

    restore_current_blog();

    // store to local multisite post id
    $partner_post_id = DT_Network_Dashboard_Site_Post_Type::get_post_id( $profile['partner_id'] );
    if ( is_wp_error( $partner_post_id ) ) {
        $partner_post_id = DT_Network_Dashboard_Site_Post_Type::create( $profile, 'multisite', $blog_id );
        if ( is_wp_error( $partner_post_id ) ) {
            dt_save_log( $file, 'FAIL ID: ' . $blog_id . ' (Unable to create '.$blog_id.')' );
            dt_save_log( $file, maybe_serialize( $partner_post_id ) );
            return false;
        }
    }
    update_post_meta( $partner_post_id, 'snapshot', $snapshot );
    update_post_meta( $partner_post_id, 'snapshot_timestamp', $snapshot['timestamp'] );
    delete_post_meta( $partner_post_id, 'snapshot_fail' );

    dt_save_log( $file, 'SUCCESS ID: ' . $blog_id );

    return true;
}
