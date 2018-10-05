<?php
/**
 * Network Dashboard Queries
 *
 * @param       $type
 * @param array $args
 *
 * @return array|null|object|\WP_Error
 */

function dt_network_dashboard_queries ( $type, $args = [] ) {
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
                ", $partner_id), ARRAY_A );
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
            ), ARRAY_A);
            break;

        case 'site_link_list':
            $results = $wpdb->get_results("
                SELECT post_title as name, ID as id
                FROM $wpdb->posts
                JOIN $wpdb->postmeta
                  ON $wpdb->posts.ID=$wpdb->postmeta.post_id
                  AND $wpdb->postmeta.meta_key = 'type'
                  AND $wpdb->postmeta.meta_value = 'network_dashboard'
                WHERE post_type = 'site_link_system'
                  AND post_status = 'publish'
            ", ARRAY_A );
            break;

        default:
            $results = null;
            break;
    }

    return $results;
}