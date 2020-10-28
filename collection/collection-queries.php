<?php

class DT_Network_Dashboard_Queries {

    public static function remote_site_id_list() : array {
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

    public static function remote_sites_needing_snapshot_refreshed() : array {

        $sites = self::remote_site_id_list();
        if ( empty( $sites ) ){
            return [];
        }

        $snapshots_needing_refreshed = [];
        foreach ( $sites as $site ) {
            if ( get_option( $site['id'], 'snapshot_date' ) >= strtotime( 'today' ) ) {
                continue;
            }

            $snapshots_needing_refreshed[] = $site['id'];
        }

        return $snapshots_needing_refreshed;
    }

    public static function get_site_id_from_partner_id( $partner_id ) : int {
        global $wpdb;

        $results = $wpdb->get_var($wpdb->prepare( "
                SELECT 
                  pm.post_id as site_post_id
                FROM $wpdb->posts as p
                JOIN $wpdb->postmeta as pm
                  ON p.ID=pm.post_id
					AND pm.meta_key = 'partner_id'
				WHERE p.post_type = 'site_link_system'
                  AND p.post_status = 'publish'
                  AND pm.meta_value = %s",
        $partner_id)  );

        if ( empty( $results ) ) {
            $results = 0;
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
                 CASE
                    WHEN e.meta_value IS NOT NULL THEN e.meta_value
                    WHEN f.meta_value IS NOT NULL THEN f.meta_value
                    ELSE a.post_title
                    END as name,
                  a.ID as id,
                  d.meta_value as partner_id,
                  c.meta_value as snapshot
                FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as b
                  ON a.ID=b.post_id
                  AND b.meta_key = 'type'
                LEFT JOIN $wpdb->postmeta as pm2
                  ON a.ID=pm2.post_id
					AND pm2.meta_key = 'non_wp'
                JOIN $wpdb->postmeta as c
                  ON a.ID=c.post_id
                  AND c.meta_key = 'snapshot'
                  AND c.meta_value IS NOT NULL
                JOIN $wpdb->postmeta as d
                  ON a.ID=d.post_id
                  AND d.meta_key = 'partner_id'
                 LEFT JOIN $wpdb->postmeta as e
                  	ON a.ID=e.post_id
                  	AND e.meta_key = 'partner_nickname'
                 LEFT JOIN $wpdb->postmeta as f
                  	ON a.ID=f.post_id
                  	AND f.meta_key = 'partner_name'
                WHERE a.post_type = 'site_link_system'
                  AND a.post_status = 'publish'
                    AND ( b.meta_value = 'network_dashboard_both'
                  OR b.meta_value = 'network_dashboard_receiving' )
                  AND pm2.meta_value != '1'
                  ORDER BY name;
            ",
        ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function all_multisite_blog_ids() : array {
        global $wpdb;
        $table = $wpdb->base_prefix . 'blogs';
        $results = $wpdb->get_col( "SELECT blog_id FROM $table" );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function multisite_snapshots() {
        if ( ! dt_is_current_multisite_dashboard_approved() ) {
            return [];
        }

        $site_ids = self::all_multisite_blog_ids();

        $snapshot = [];
        foreach ( $site_ids as $id ) {
            if ( get_blog_option( $id, 'current_theme' ) !== 'Disciple Tools' ) {
                continue;
            }

            $snapshot[$id] = get_blog_option( $id, 'dt_snapshot_report' );
        }

        return $snapshot;
    }

    /**
     * Returns list of site ids that need a refreshed snapshot
     *
     * @return array
     */
    public static function multisite_sites_needing_snapshot_refreshed() {
        if ( ! dt_is_current_multisite_dashboard_approved() ) {
            return [];
        }

        $site_ids = self::all_multisite_blog_ids();

        $snapshots_needing_refreshed = [];
        foreach ( $site_ids as $id ) {
            if ( get_blog_option( $id, 'current_theme' ) !== 'Disciple Tools' ) {
                continue;
            }

            if ( get_blog_option( $id, 'dt_snapshot_report_timestamp' ) >= strtotime( 'today' ) ) {
                continue;
            }

            $snapshots_needing_refreshed[] = $id;
        }

        return $snapshots_needing_refreshed;
    }
}

function dt_multisite_dashboard_snapshots() {
    return DT_Network_Dashboard_Queries::multisite_snapshots();
}