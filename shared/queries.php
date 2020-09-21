<?php

class DT_Network_Dashboard_Queries {

    public static function site_link_list() : array {
        global $wpdb;

        $results = $wpdb->get_results("
                SELECT 
                  post_title as name, 
                  ID as id
                FROM $wpdb->posts as p
                JOIN $wpdb->postmeta as pm
                  ON p.ID=pm.post_id
					AND pm.meta_key = 'type'
				LEFT JOIN $wpdb->postmeta as pm2
                  ON p.ID=pm2.post_id
					AND pm2.meta_key = 'non_wp' 
                  WHERE p.post_type = 'site_link_system'
                  AND p.post_status = 'publish'
                  AND ( pm.meta_value = 'network_dashboard_both'
                  OR pm.meta_value = 'network_dashboard_receiving' )
                    AND pm2.meta_value != '1'
            ",
        ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function has_sites_for_collection() : int {
        global $wpdb;

        $results = $wpdb->get_var("
                SELECT 
                  count(id)
                FROM $wpdb->posts as p
                JOIN $wpdb->postmeta as pm
                  ON p.ID=pm.post_id
					AND pm.meta_key = 'type'
				LEFT JOIN $wpdb->postmeta as pm2
                  ON p.ID=pm2.post_id
					AND pm2.meta_key = 'non_wp' 
                  WHERE p.post_type = 'site_link_system'
                  AND p.post_status = 'publish'
                  AND ( pm.meta_value = 'network_dashboard_both'
                  OR pm.meta_value = 'network_dashboard_receiving' )
                    AND pm2.meta_value != '1'
            ");

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
                JOIN $wpdb->postmeta as pm2
                  ON p.ID=pm2.post_id
					AND pm2.meta_key = 'non_wp' AND pm2.meta_value = '1'
                JOIN $wpdb->postmeta as c
                  ON a.ID=c.post_id
                  AND c.meta_key = 'snapshot'
                  AND c.meta_value IS NOT NULL
                JOIN $wpdb->postmeta as d
                  ON a.ID=d.post_id
                  AND d.meta_key = 'partner_id'
                WHERE a.post_type = 'site_link_system'
                  AND a.post_status = 'publish'
                    AND ( b.meta_value = 'network_dashboard_both'
                  OR b.meta_value = 'network_dashboard_receiving' )
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