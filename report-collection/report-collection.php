<?php
/**
 *
 */

class DT_Network_Dashboard_Report_Collections
{
    public function get_report( WP_REST_Request $request ) {

        if ( ! user_can( get_current_user_id(), 'manage_dt' ) ) {
            return new WP_Error( __METHOD__, 'Permission error.' );
        }

        $params = $request->get_params();
        if ( isset( $params['id'] ) ) {

            // Trigger Remote Report from Site
            $site = Site_Link_System::get_site_connection_vars( $params['id'] );
            if ( is_wp_error( $site ) ) {
                return new WP_Error( __METHOD__, 'Error creating site connection details.' );
            }
            $args = [
                'method' => 'GET',
                'body' => [
                    'transfer_token' => $site['transfer_token'],
                ]
            ];
            $result = wp_remote_get( 'https://' . $site['url'] . '/wp-json/dt-public/v1/network/trigger_report', $args );
            if ( is_wp_error( $result ) ) {
                return new WP_Error( 'failed_remote_get', $result->get_error_message() );
            } else {
                return $result;
            }
            // end Remote connection


        } else {
            return new WP_Error(__METHOD__, 'Missing parameters' );
        }
    }
}