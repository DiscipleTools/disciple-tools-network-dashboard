<?php

class DT_Network_Dashboard_Queries {

    public static function check_sum_list( int $site_post_id ) : array {
        global $wpdb;

        $partner_id = get_post_meta( $site_post_id, 'partner_id', true );
        $results = $wpdb->get_results( $wpdb->prepare( "
                SELECT foreign_key, check_sum 
                FROM $wpdb->dt_network_locations 
                WHERE partner_id = %s
                ",
        $partner_id),
        ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function get_report_by_id( int $id ) : array {
        global $wpdb;
        $results = $wpdb->get_results( $wpdb->prepare( "
                SELECT * 
                FROM $wpdb->dt_network_reports 
                WHERE id = %s
                ",
            $id
        ),
        ARRAY_A);

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function site_link_list() : array {
        global $wpdb;

        $results = $wpdb->get_results("
                SELECT 
                  post_title as name, 
                  ID as id
                FROM $wpdb->posts
                JOIN $wpdb->postmeta
                  ON $wpdb->posts.ID=$wpdb->postmeta.post_id
                  AND $wpdb->postmeta.meta_key = 'type'
                  AND $wpdb->postmeta.meta_value = 'network_dashboard_receiving'
                WHERE post_type = 'site_link_system'
                  AND post_status = 'publish'
                  ORDER BY name ASC
            ",
        ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function sites_with_snapshots() : array {
        global $wpdb;

        $results = $wpdb->get_results("
                SELECT 
                  a.post_title as name, 
                  a.ID as id,
                  d.meta_value as partner_id,
                  c.meta_value as snapshot
                FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as b
                  ON a.ID=b.post_id
                  AND b.meta_key = 'type'
                  AND b.meta_value = 'network_dashboard_receiving'
                JOIN $wpdb->postmeta as c
                  ON a.ID=c.post_id
                  AND c.meta_key = 'snapshot'
                  AND c.meta_value IS NOT NULL
                JOIN $wpdb->postmeta as d
                  ON a.ID=d.post_id
                  AND d.meta_key = 'partner_id'
                WHERE a.post_type = 'site_link_system'
                  AND a.post_status = 'publish'
                  ORDER BY name ASC
            ",
        ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function all_multisite_ids() : array {
        global $wpdb;
        $table = $wpdb->base_prefix . 'blogs';
        $results = $wpdb->get_col( "SELECT blog_id FROM $table" );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }


}