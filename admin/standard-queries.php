<?php
/**
 * Network Dashboard Queries
 *
 * @param       $type
 * @param array $args
 *
 * @return array|null|object|\WP_Error
 */

function dt_network_dashboard_queries( $type, $args = [] ) {
    global $wpdb;

    if ( empty( $type ) ) {
        return new WP_Error( __METHOD__, 'Required type is missing.' );
    }

    switch ( $type ) {

        case 'check_sum_list':

            if ( ! isset( $args['site_post_id'] ) ) {
                return new WP_Error( __METHOD__, 'check_sum_list query request was missing the required site_post_id parameter.' );
            }
            $site_post_id = $args['site_post_id'];
            $partner_id = get_post_meta( $site_post_id, 'partner_id', true );
            $results = $wpdb->get_results( $wpdb->prepare( "
                SELECT foreign_key, check_sum 
                FROM $wpdb->dt_network_locations 
                WHERE partner_id = %s
                ",
                $partner_id),
            ARRAY_A );
            break;

        case 'get_report_by_id':

            if ( ! isset( $args['id'] ) ) {
                return new WP_Error( __METHOD__, 'check_sum_list query request was missing the required site_post_id parameter.' );
            }
            $id = $args['id'];
            $results = $wpdb->get_results( $wpdb->prepare( "
                SELECT * 
                FROM $wpdb->dt_network_reports 
                WHERE id = %s
                ",
                $id
                ),
            ARRAY_A);
            break;

        case 'site_link_list':
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
            break;

        case 'sites_with_snapshots':
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
            break;

        case 'all_multisite_ids':
            $table = $wpdb->base_prefix . 'blogs';
            $results = $wpdb->get_col( "SELECT blog_id FROM $table" );
            break;

        case 'multisite_and_post_ids':
            $table = $wpdb->base_prefix . 'blogs';
            $results = $wpdb->get_results( "
                SELECT
                  multisite.blog_id as blog_id,
                  postmeta.post_id as post_id
                FROM $table as multisite
                LEFT JOIN $wpdb->postmeta as postmeta
                      ON postmeta.meta_value=multisite.blog_id
                       AND postmeta.post_id IN ( SELECT ID FROM $wpdb->posts WHERE post_type = 'multisite_reports' AND post_status = 'publish')
            ",
            ARRAY_A );
            break;

        case 'multisite_snapshots':
            $assoc_array = [];
            $results = $wpdb->get_results("
                SELECT
                  ID as id,
                  blog_id.meta_value as blog_id,
                  blog_name.meta_value as blog_name,
                  snapshot.meta_value as snapshot,
                  snapshot_date.meta_value as snapshot_date,
                  snapshot_fail.meta_value as snapshot_fail
                FROM $wpdb->posts as posts
                  JOIN $wpdb->postmeta as blog_id
                      ON posts.ID=blog_id.post_id
                       AND blog_id.meta_key = 'blog_id'
                  LEFT JOIN $wpdb->postmeta as blog_name
                      ON posts.ID=blog_name.post_id
                       AND blog_name.meta_key = 'blog_name'
                  LEFT JOIN $wpdb->postmeta as snapshot
                      ON posts.ID=snapshot.post_id
                      AND snapshot.meta_key = 'snapshot'
                  LEFT JOIN $wpdb->postmeta as snapshot_date
                      ON posts.ID=snapshot_date.post_id
                       AND snapshot_date.meta_key = 'snapshot_date'
                   LEFT JOIN $wpdb->postmeta as snapshot_fail
                      ON posts.ID=snapshot_fail.post_id
                       AND snapshot_fail.meta_key = 'snapshot_fail'
                WHERE post_type = 'multisite_reports'
                  AND post_status = 'publish'
            ",
            ARRAY_A );

            if ( ! empty( $results ) ) {
                foreach ( $results as $result ) {
                    $assoc_array[$result['blog_id']] = $result;
                }
                return $assoc_array;
            }
            break;

        case 'location_by_foreign_key':
            /**
             * Requires 1 parameter. foreign_key
             */
            if ( empty( $args ) || 1 != count( $args ) ) {
                return new WP_Error( __METHOD__, 'Argument submitted is either empty or not equal to 1.' );
            }
            $foreign_key = $args['foreign_key'] ?? $args[0] ?? 0;
            $results = $wpdb->get_row( $wpdb->prepare( "
                SELECT * 
                FROM $wpdb->dt_network_locations 
                WHERE foreign_key = %s",
                $foreign_key
                ),
            ARRAY_A );
            break;

        case 'query_location_population_groups':
            $results = $wpdb->get_results("
                SELECT 
                t1.ID as id, 
                t1.post_parent as parent_id, 
                t1.post_title as location,
                (SELECT post_title FROM $wpdb->posts WHERE ID = t1.post_parent) as parent_name,
                t2.meta_value as gn_population, 
                ROUND(t2.meta_value / (SELECT option_value FROM $wpdb->options WHERE option_name = 'dt_network_dashboard_population'), 0 ) as groups_needed,
                (SELECT count(*) FROM $wpdb->p2p WHERE p2p_to = t1.ID) as groups
                FROM $wpdb->posts as t1
                LEFT JOIN $wpdb->postmeta as t2
                ON t1.ID=t2.post_id
                AND t2.meta_key = 'gn_population'
                WHERE post_type = 'locations' AND post_status = 'publish'
            ",
            ARRAY_A );

            break;

        case 'query_location_data':
            $results = $wpdb->get_results("
            SELECT 
            t1.ID as id, 
            t1.post_parent as parent_id, 
            t1.post_title as location,
            (SELECT post_title FROM $wpdb->posts WHERE ID = t1.post_parent) as parent_name,
            t2.meta_value as gn_population, 
            ROUND(t2.meta_value / (SELECT option_value FROM $wpdb->options WHERE option_name = 'dt_network_dashboard_population'), 0 ) as groups_needed,
            (SELECT count(*) FROM $wpdb->p2p WHERE p2p_to = t1.ID) as groups
            
            FROM $wpdb->posts as t1
            LEFT JOIN $wpdb->postmeta as t2
            ON t1.ID=t2.post_id
            AND t2.meta_key = 'gn_population'
            WHERE post_type = 'locations' AND post_status = 'publish'
        ",
            ARRAY_A );

            break;

        case 'query_location_latlng':
            $results = $wpdb->get_results("
                SELECT 
                t2.meta_value as latitude,
                t3.meta_value as longitude,
                t1.post_title as location
                FROM $wpdb->posts as t1
                LEFT JOIN $wpdb->postmeta as t2
                ON t1.ID=t2.post_id
                AND t2.meta_key = 'gn_latitude'
                LEFT JOIN $wpdb->postmeta as t3
                ON t1.ID=t3.post_id
                AND t3.meta_key = 'gn_longitude'
                WHERE post_type = 'locations' 
                AND post_status = 'publish'
                AND post_parent != '0'
            ",
            ARRAY_A );

            break;

        default:
            $results = null;
            break;
    }

    return $results;
}
