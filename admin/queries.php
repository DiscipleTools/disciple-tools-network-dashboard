<?php

class DT_Network_Dashboard_Queries {

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
                  AND $wpdb->postmeta.meta_value LIKE 'network_dashboard%'
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
                  AND b.meta_value LIKE 'network_dashboard%'
                JOIN $wpdb->postmeta as c
                  ON a.ID=c.post_id
                  AND c.meta_key = 'snapshot'
                  AND c.meta_value IS NOT NULL
                JOIN $wpdb->postmeta as d
                  ON a.ID=d.post_id
                  AND d.meta_key = 'partner_id'
                WHERE a.post_type = 'site_link_system'
                  AND a.post_status = 'publish'
                  ORDER BY name
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