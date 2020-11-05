<?php
/**
 * Scheduled Cron Service
 */

if ( is_multisite() ){
    if ( dt_is_current_multisite_dashboard_approved() ) {

        if ( !wp_next_scheduled( 'dt_network_dashboard_collect_multisite_snapshots' )) {
            wp_schedule_event( strtotime( 'tomorrow 2am' ), 'daily', 'dt_network_dashboard_collect_multisite_snapshots' );
        }
        add_action( 'dt_network_dashboard_collect_multisite_snapshots', 'dt_network_dashboard_multisite_snapshot_async' );
    }

    /**
     * Run collection process
     */
    function dt_network_dashboard_multisite_snapshot_async() {
        if ( ! dt_is_current_multisite_dashboard_approved() ) {
            return false;
        }

        $limit = 100; // set max loops before spawning new cron

        DT_Network_Dashboard_Site_Post_Type::sync_all_multisites_to_post_type();

        $file = 'multisite';

        if ( ! dt_is_todays_log( $file ) ) {
            dt_reset_log( $file );

            dt_save_log( $file, '', false );
            dt_save_log( $file, '*********************************************', false );
            dt_save_log( $file, 'MULTISITE SNAPSHOT LOGS', false );
            dt_save_log( $file, 'Timestamp: ' . gmdate( 'Y-m-d', time() ), false );
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
            wp_schedule_single_event( strtotime( '+5 minutes' ), 'dt_network_dashboard_collect_multisite_snapshots' );
        }

        // Loop sites through a second async task, so that each will become and individual async process.
        $i = 0;
        foreach ( $sites as $site_id ) {

            dt_network_dashboard_collect_multisite( $site_id );

            if ( $i >= $limit ){
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
        global $wpdb;

        if ( ! dt_is_current_multisite_dashboard_approved() ) {
            return false;
        }

        // is this an approved network dashboard, and has that owner turned off collection
        $check = false;
        $approved_sites = dt_dashboard_approved_sites();
        $approved_sites_ids = array_keys( $approved_sites );
        if ( in_array( $blog_id, $approved_sites_ids ) ){
            $check = true;
        }

        $source_blog_id = get_current_blog_id();

        $file = 'multisite';
        dt_save_log( $file, 'START ID: ' . $blog_id );

        switch_to_blog( $blog_id );

        // checks if dashboard has set to not distribute to this dashboard.
        if ( $check ) {
            $prefix = $wpdb->get_blog_prefix( $blog_id );
            $posts_table = $prefix . 'posts';
            $postmeta_table = $prefix . 'postmeta';
            // @phpcs:disable
            $allowed = $wpdb->get_var($wpdb->prepare( "
                SELECT pm1.meta_value 
                FROM {$posts_table} as p 
                    JOIN {$postmeta_table} as pm1 ON p.ID=pm1.post_id
                    AND pm1.meta_key = 'send_activity'
                    JOIN {$postmeta_table} as pm2 ON p.ID=pm2.post_id
                    AND pm2.meta_key = 'type_id'
                WHERE p.post_type = 'dt_network_dashboard'
                    AND pm2.meta_value = %s
                    ",
            $source_blog_id ) );
            // phpcs:enable
            if ( ! empty( $allowed ) && 'none' === $allowed ){
                restore_current_blog();
                return false;
            }
        }

        $profile = dt_network_site_profile();
        $snapshot = DT_Network_Dashboard_Snapshot::snapshot_report( true );

        if ( $snapshot['status'] == 'FAIL' ) {
            // retry connection in 3 seconds
            sleep( 5 );
            dt_save_log( $file, 'RETRY ID: ' . $blog_id . ' (Payload = FAIL)' );
            $snapshot = DT_Network_Dashboard_Snapshot::snapshot_report();
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
}


