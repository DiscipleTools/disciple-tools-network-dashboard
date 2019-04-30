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
            $data = DT_Network_Dashboard_Queries::check_sum_list( $site_post_id  );
        }
        if ( 'outstanding_site_locations' === $type ) {
            $data = self::get_outstanding_locations( $site_post_id );
            if ( empty( $data ) ) {
                return 'No outstanding locations to sync.';
            }
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

    public static function live_stats( $site_post_id, $type ) {

        $site = Site_Link_System::get_site_connection_vars( $site_post_id );
        if ( is_wp_error( $site ) ) {
            return new WP_Error( __METHOD__, 'Error creating site connection details.' );
        }

        $args = [
            'method' => 'GET',
            'body' => [
                'transfer_token' => $site['transfer_token'],
                'type' => $type,
            ]
        ];
        $result = wp_remote_get( 'https://' . $site['url'] . '/wp-json/dt-public/v1/network/live_stats', $args );
        if ( is_wp_error( $result ) ) {
            return new WP_Error( 'failed_remote_post', $result->get_error_message() );
        } else {
            return $result['body'];
        }
    }

    /**
     * Function builds list locations that have changed, been added, and removes locations that have been deleted.
     *
     * @param $site_post_id
     *
     * @return array
     */
    public static function get_outstanding_locations( $site_post_id ) {
        dt_write_log( __METHOD__ );

        $action = [
            'match' => 0,
            'update_scheduled' => 0,
            'schedule_error' => 0,
        ];

        $outstanding_locations = [];

        $remote_data = json_decode( self::live_stats( $site_post_id, 'locations_list' ) );

        $dashboard_data = DT_Network_Dashboard_Queries::check_sum_list( $site_post_id );
        ;

        foreach ( $remote_data as $master ) {
            foreach ( $dashboard_data as $dash_value ) {
                // first match foreign key
                if ( $master->foreign_key === $dash_value['foreign_key'] ) {
                    // then test if check_sum matches
                    if ( isset( $dash_value['check_sum'] ) && $master->check_sum === $dash_value['check_sum'] ) {
                        // record match and break to next master value
                        $action['match']++;
                        continue 2;
                    }
                }
            }

            $outstanding_locations[] = [
                'foreign_key' => $master->foreign_key,
                'check_sum' => $master->check_sum,
            ];
        }

        // remove deleted locations
        foreach ( $dashboard_data as $dash_value ) {
            foreach ( $remote_data as $master ) {
                if ( $master->foreign_key === $dash_value['foreign_key'] ) {
                    continue 2;
                }
            }
            self::delete_location( $dash_value );
        }

        // return list of locations that need updated or added.
        return $outstanding_locations;
    }

    public static function insert_report( $args ) {
        global $wpdb;

        $args = wp_parse_args( $args, [
            'partner_id' => null,
            'total_contacts' => 0,
            'total_groups' => 0,
            'total_users' => 0,
            'date' => current_time( 'mysql' ),
        ]);

        $result = $wpdb->insert( $wpdb->dt_network_reports, [
                'partner_id' => $args['partner_id'],
                'total_contacts' => $args['total_contacts'],
                'total_groups' => $args['total_groups'],
                'total_users' => $args['total_users'],
                'date' => $args['date'],
        ]);

        if ( ! $result ) {
            return new WP_Error( __METHOD__, 'Failed to insert report data. ' . $wpdb->last_error );
        } else {
            return (int) $wpdb->insert_id;
        }
    }

    public static function update_site_profile( $site_id, $site_profile ) {
        $error = new WP_Error();

        if ( empty( $site_profile ) ) {
            $error->add( __METHOD__, 'Empty site profile' );
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

    public static function update_location( $report_data ) {
        global $wpdb;

        if ( empty( $report_data ) ) {
            return [
                'status' => 'FAIL',
                'action' => 'No location found in report data.'
            ];
        }

        $partner_id = esc_sql( $report_data['partner_id'] );
        $foreign_key = esc_sql( $report_data['foreign_key'] );
        $id = esc_sql( $report_data['id'] );
        $parent_id = esc_sql( $report_data['parent_id'] );
        $post_title = esc_sql( $report_data['post_title'] );
        $address = esc_sql( $report_data['address'] );
        $latitude = esc_sql( $report_data['latitude'] );
        $longitude = esc_sql( $report_data['longitude'] );
        $types = esc_sql( $report_data['types'] );
        $country_short_name = esc_sql( $report_data['country_short_name'] ) ?? '';
        $admin1_short_name = esc_sql( $report_data['admin1_short_name'] ) ?? '';
        $check_sum = esc_sql( $report_data['check_sum'] );


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
                    admin1_short_name,
                    check_sum
                    ) 
                    VALUES (
                    '$partner_id',
                    '$foreign_key',
                    '$id',
                    '$parent_id',
                    '$post_title',
                    '$address',
                    '$latitude',
                    '$longitude',
                    '$types',
                    '$country_short_name',
                    '$admin1_short_name',
                    '$check_sum'
                    ) 
                    ON DUPLICATE KEY UPDATE 
                    partner_id='$partner_id',
                    foreign_key='$foreign_key',
                    id='$id',
                    parent_id='$parent_id',
                    post_title='$post_title',
                    address='$address',
                    latitude='$latitude',
                    longitude='$longitude',
                    types='$types',
                    country_short_name='$country_short_name',
                    admin1_short_name='$admin1_short_name',
                    check_sum='$check_sum'
                    ;";

        // @codingStandardsIgnoreLine
        $wpdb->query( $sql );

        return [
            'status' => 'OK',
            'action' => 'Updated'
        ];
    }

    public static function delete_location( $report_data ) {
        global $wpdb;

        if ( empty( $report_data ) ) {
            return [
                'status' => 'FAIL',
                'action' => 'No location found in report data.'
            ];
        }

        $wpdb->delete(
            $wpdb->dt_network_locations,
            [
                'foreign_key' => $report_data['foreign_key']
            ]
        );

        return [
            'status' => 'OK',
            'action' => $wpdb->rows_affected,
        ];
    }
}