<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Class DT_Network_Dashboard_Reports
 */
class DT_Network_Dashboard_Reports
{
    public static function trigger_transfer( $site_post_id, $type ) {

        // Trigger Remote Report from Site
        $site = Site_Link_System::get_site_connection_vars( $site_post_id );
        if ( is_wp_error( $site ) ) {
            return new WP_Error( __METHOD__, 'Error creating site connection details.' );
        }

        $data = '';

        if ( 'site_locations' === $type ) {
            $data = self::get_check_sum_list( $site_post_id );
            dt_write_log($data);
        }

        $args = [
            'method' => 'POST',
            'body' => [
                'transfer_token' => $site['transfer_token'],
                'type' => $type,
                'data' => $data,
            ]
        ];
        $result = wp_remote_post( 'https://' . $site['url'] . '/wp-json/dt-public/v1/network/trigger_transfer', $args );
        if ( is_wp_error( $result ) ) {
            return new WP_Error( 'failed_remote_post', $result->get_error_message() );
        } else {
            return $result['body'];
        }
    }

    public static function get_check_sum_list( $site_post_id) {
        global $wpdb;
        $partner_id = get_post_meta( $site_post_id, 'partner_id', true );
        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT foreign_key, check_sum FROM $wpdb->dt_network_locations WHERE partner_id = %s
        ", $partner_id), ARRAY_A );
        return $results;
    }

    public static function query_site_link_list() {
        global $wpdb;
        $list = $wpdb->get_results("
            SELECT post_title as name, ID as id
            FROM $wpdb->posts
            JOIN $wpdb->postmeta
              ON $wpdb->posts.ID=$wpdb->postmeta.post_id
              AND $wpdb->postmeta.meta_key = 'type'
              AND $wpdb->postmeta.meta_value = 'network_dashboard'
            WHERE post_type = 'site_link_system' 
                AND post_status = 'publish'
        ", ARRAY_A );

        return $list;
    }

    public static function insert_report( $args ) {
        global $wpdb;

        $args = wp_parse_args( $args, [
            'partner_id' => null,
            'total_contacts' => 0,
            'total_groups' => 0,
            'total_users' => 0,
            'date' => current_time( 'mysql' ),
            'raw_response' => '',
        ]);

        $result = $wpdb->insert( $wpdb->dt_network_reports, [
                'partner_id' => $args['partner_id'],
                'total_contacts' => $args['total_contacts'],
                'total_groups' => $args['total_groups'],
                'total_users' => $args['total_users'],
                'date' => $args['date'],
                'raw_response' => $args['raw_response'],
            ]);

        if (! $result ) {
            return new WP_Error(__METHOD__, 'Failed to insert report data. ' . $wpdb->last_error );
        } else {
            return (int) $wpdb->insert_id;
        }

    }

    public static function update_site_profile( $site_id, $site_profile ) {
        $error = new WP_Error;

        if ( empty( $site_profile ) ) {
            $error->add(__METHOD__, 'Empty site profile' );
            return [
                'status' => 'FAIL',
                'action' => $error,
            ];
        }

        foreach ( $site_profile as $key => $value ) {
            update_post_meta( $site_id, $key, $value );
        }

        return [
            'status' => 'OK',
            'action' => 'Updated'
        ];
    }

    public static function update_site_locations( $site_id, $report_data ) {
        $error = [];

        update_post_meta( $site_id, 'partner_locations_check_sum', $report_data['check_sum'] );
        update_post_meta( $site_id, 'partner_locations_total', $report_data['total'] );

        if ( empty( $report_data['locations'] ) ) {
            return [
                'status' => 'FAIL',
                'action' => 'No locations found in report data.'
            ];
        }
        dt_write_log($report_data['total']);

        global $wpdb;

        foreach ( $report_data['locations'] as $location ) {

            $last_error = $wpdb->last_error;

            $partner_id = sanitize_text_field( wp_unslash( $location['partner_id'] ) );
            $foreign_key = sanitize_text_field( wp_unslash( $location['foreign_key'] ) );
            $id = sanitize_text_field( wp_unslash( $location['id'] ) );
            $parent_id = sanitize_text_field( wp_unslash( $location['parent_id'] ) );
            $post_title = sanitize_text_field( wp_slash( $location['post_title'] ) );
            $address = sanitize_text_field( wp_slash( $location['address'] ) );
            $latitude = sanitize_text_field( wp_unslash( $location['latitude'] ) );
            $longitude = sanitize_text_field( wp_unslash( $location['longitude'] ) );
            $country_short_name = sanitize_text_field( wp_unslash( $location['country_short_name'] ) );
            $admin1_short_name = sanitize_text_field( wp_unslash( $location['admin1_short_name'] ) );
            $types = sanitize_text_field( wp_unslash( $location['types'] ) );


            $sql = "INSERT INTO $wpdb->dt_network_locations (
                    partner_id,
                    foreign_key,
                    id,
                    parent_id,
                    post_title,
                    address,
                    latitude,
                    longitude,
                    types,
                    country_short_name,
                    admin1_short_name
                    ) 
                    VALUES (
                    '$partner_id',
                    '$foreign_key',
                    $id,
                    $parent_id,
                    '$post_title',
                    '$address',
                    $latitude,
                    $longitude,
                    '$types',
                    '$country_short_name',
                    '$admin1_short_name'
                    ) 
                    ON DUPLICATE KEY UPDATE 
                    partner_id='$partner_id',
                    foreign_key='$foreign_key',
                    id=$id,
                    parent_id=$parent_id,
                    post_title='$post_title',
                    address='$address',
                    latitude=$latitude,
                    longitude=$longitude,
                    types='$types',
                    country_short_name='$country_short_name',
                    admin1_short_name='$admin1_short_name'
                    ;";

            $wpdb->query( $sql );

            if( (empty( $wpdb->last_result ) || !$wpdb->last_result ) && !empty( $wpdb->last_error ) && $last_error != $wpdb->last_error ) {
                $error[]= $wpdb->last_error . " ($sql)";
            }

        }

        return [
            'status' => 'OK',
            'action' => 'Updated'
        ];
    }
}