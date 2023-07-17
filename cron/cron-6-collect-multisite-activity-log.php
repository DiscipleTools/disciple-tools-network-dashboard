<?php
/**
 * Scheduled Cron Service
 */

if ( is_multisite() && dt_network_dashboard_multisite_is_approved() ){

    if ( !wp_next_scheduled( 'dt_network_dashboard_multisite_activity_log_async' ) ) {
        wp_schedule_event( strtotime( 'tomorrow 4:30 am' ), 'daily', 'dt_network_dashboard_multisite_activity_log_async' );
    }
    add_action( 'dt_network_dashboard_multisite_activity_log_async', 'dt_network_dashboard_multisite_activity_log_async' );


    /**
     * Run collection process
     */
    function dt_network_dashboard_multisite_activity_log_async() {
        if ( ! dt_is_current_multisite_dashboard_approved() ) {
            return false;
        }

        $limit = 100; // set max loops before spawning new cron

        DT_Network_Dashboard_Site_Post_Type::sync_all_multisites_to_post_type();

        $file = 'activity-multisite';

        if ( ! dt_is_todays_log( $file ) ) {
            dt_reset_log( $file );

            dt_save_log( $file, '', false );
            dt_save_log( $file, '*********************************************', false );
            dt_save_log( $file, 'MULTISITE ACTIVITY LOGS', false );
            dt_save_log( $file, 'Timestamp: ' . gmdate( 'Y-m-d', time() ), false );
            dt_save_log( $file, '*********************************************', false );
            dt_save_log( $file, '', false );
        }

        // Get list of sites
        $sites = DT_Network_Dashboard_Site_Post_Type::all_multisite_sites();
        if ( empty( $sites ) ){
            dt_save_log( $file, 'No sites found to collect.', false );
            return false;
        }

        if ( count( $sites ) > $limit ) {
            /* if more than the limit of sites, spawn another event to process. This will keep spawning until all sites are reduced.*/
            wp_schedule_single_event( strtotime( '+5 minutes' ), 'dt_network_dashboard_multisite_activity_log_async' );
        }

        // Loop sites through a second async task, so that each will become and individual async process.
        $i = 0;
        foreach ( $sites as $site ) {
            if ( $site['partner_id'] === dt_network_site_id() ){
                continue;
            }

            dt_network_dashboard_collect_multisite_activity( $site );

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
    function dt_network_dashboard_collect_multisite_activity( $site ) {
        if ( ! dt_is_current_multisite_dashboard_approved() ) {
            return false;
        }

        if ( ! dt_is_network_dashboard_plugin_active( $site['type_id'] ) ) {
            return false;
        }

        global $wpdb;
        $file = 'activity-multisite';
        dt_save_log( $file, 'START ID: ' . $site['type_id'] );

        $registered_actions = dt_network_dashboard_registered_actions( true );

        // query last record in activity log
        $last_record_id = $wpdb->get_var( $wpdb->prepare( "SELECT MAX( site_record_id ) FROM $wpdb->dt_movement_log WHERE site_id = %s", $site['partner_id'] ) );
        if ( is_wp_error( $last_record_id ) ){
            dt_save_log( $file, 'FAIL ID: ' . $site['type_id'] );
            dt_save_log( $last_record_id );
            return false;
        }

        switch_to_blog( $site['type_id'] );

            $prefix = $wpdb->get_blog_prefix( $site['type_id'] );
            $table = $prefix . 'dt_movement_log';

            // @phpcs:disable
            $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE site_id = %s AND id > %s AND action IN ($registered_actions) ORDER BY id LIMIT 10000", $site['partner_id'], $last_record_id ), ARRAY_A );
            // @phpcs:enable

        restore_current_blog();

        // insert new records
        DT_Network_Activity_Log::transfer_insert_multiple( $results, $site['partner_id'] );

        // store to local multisite post id
        update_post_meta( $site['id'], 'activity_timestamp', time() );

        dt_save_log( $file, 'Number of collected records: ' . count( $results ) );

        dt_save_log( $file, 'SUCCESS ID: ' . $site['type_id'] );

        return true;
    }
}
