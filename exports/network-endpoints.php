<?php
/**
 * Rest endpoints
 *
 * @package  Disciple_Tools
 * @category Plugin
 * @since    0.1
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

/**
 * Class DT_Network_Dashboard_Network_Endpoints
 */
class DT_Network_Dashboard_Network_Endpoints
{

    private $version = 1;
    private $namespace;
    private $public_namespace;

    /**
     * DT_Network_Dashboard_Network_Endpoints The single instance of DT_Network_Dashboard_Network_Endpoints.
     *
     * @var     object
     * @access    private
     * @since     0.1.0
     */
    private static $_instance = null;

    /**
     * Main DT_Network_Dashboard_Network_Endpoints Instance
     * Ensures only one instance of DT_Network_Dashboard_Network_Endpoints is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return DT_Network_Dashboard_Network_Endpoints instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {
        $this->namespace = "dt" . "/v" . intval( $this->version );
        $this->public_namespace = "dt-public" . "/v" . intval( $this->version );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    } // End __construct()

    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/network/load_p_list_by_country', [
                'methods'  => 'POST',
                'callback' => [ $this, 'load_p_list_by_country' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/network/load_p_countries_installed', [
                'methods'  => 'POST',
                'callback' => [ $this, 'load_p_countries_installed' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/network/get_report', [
                'methods'  => 'POST',
                'callback' => [ $this, 'get_report' ],
            ]
        );

        // public
        register_rest_route(
            $this->public_namespace, '/network/trigger_report', [
                'methods'  => 'GET',
                'callback' => [ $this, 'trigger_report' ],
            ]
        );

    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public function load_p_list_by_country( WP_REST_Request $request ) {

        if ( ! user_can( get_current_user_id(), 'manage_dt' ) ) {
            return new WP_Error( __METHOD__, 'Permission error.' );
        }

        $params = $request->get_params();
        if ( isset( $params['country_code'] ) ) {
            $result = DT_Network_Dashboard_Installer::load_p_list_by_country( $params['country_code'] );
            return $result;
        } else {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return string|WP_Error
     */
    public function load_p_countries_installed( WP_REST_Request $request ) {

        if ( ! user_can( get_current_user_id(), 'manage_dt' ) ) {
            return new WP_Error( __METHOD__, 'Permission error.' );
        }

        return DT_Network_Dashboard_Installer::load_p_countries_installed();
    }


    /**
     * Respond to qualified report collection
     * @param \WP_REST_Request $request
     *
     * @return bool|\WP_Error
     */
    public function trigger_report( WP_REST_Request $request ) {

        $params = $request->get_params();

        $site_key = Site_Link_System::decrypt_transfer_token( $params['transfer_token'] );

        if ( $site_key ) {

            // collect snapshot of data
            $report_data = [
                'partner_id' => '',
                'total_contacts' => 0,
                'total_groups' => 0,
                'total_users' => 0,
                'new_contacts' => 0,
                'new_groups' => 0,
                'new_users' => 0,
                'total_baptisms' => 0,
                'new_baptisms' => 0,
                'baptism_generations' => 0,
                'church_generations' => 0,
                'locations' => [
                    [
                        'location_name' => '',
                        'location_id' => '',
                        'parent_id' => '',
                        'geonameid' => '',
                        'longitude' => '',
                        'latitude' => '',
                        'total_contacts' => 0,
                        'total_groups' => 0,
                        'total_users' => 0,
                        'new_contacts' => 0,
                        'new_groups' => 0,
                        'new_users' => 0,
                    ],
                    [
                        'location_name' => '',
                        'location_id' => '',
                        'parent_id' => '',
                        'geonameid' => '',
                        'longitude' => '',
                        'latitude' => '',
                        'total_contacts' => 0,
                        'total_groups' => 0,
                        'total_users' => 0,
                        'new_contacts' => 0,
                        'new_groups' => 0,
                        'new_users' => 0,
                    ],
                    [
                        'location_name' => '',
                        'location_id' => '',
                        'parent_id' => '',
                        'geonameid' => '',
                        'longitude' => '',
                        'latitude' => '',
                        'total_contacts' => 0,
                        'total_groups' => 0,
                        'total_users' => 0,
                        'new_contacts' => 0,
                        'new_groups' => 0,
                        'new_users' => 0,
                    ],
                ],
                'critical_path' => [
                    'new_inquirers' => 0,
                    'first_meetings' => 0,
                    'ongoing_meetings' => 0,
                    'total_baptisms' => 0,
                    'baptism_generations' => [
                        1 => 0,
                        2 => 0,
                        3 => 0,
                    ],
                    'baptizers' => 0,
                    'total_churches_and_groups' => 0,
                    'active_groups' => 0,
                    'active_churches' => 0,
                    'church_generations' => [
                        1 => 0,
                        2 => 0,
                        3 => 0,
                    ],
                    'church_planters' => 0,
                    'people_groups' => 0,
                ],
                'date' => current_time( 'mysql' ),
            ];


            // trigger post report to report destinations
            $site = Site_Link_System::get_site_connection_vars( $site_key, 'site_key' ); // @todo
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
            // find site destination


            // post to this site endpoint the data

            // if successful return record id and transfer data.

            return true;
        } else {
            return new WP_Error(__METHOD__, 'Missing parameters' );
        }

    }


}
DT_Network_Dashboard_Network_Endpoints::instance();