<?php
/**
 * Rest Endpoints for the network feature of Disciple Tools
 *
 * @class      Disciple_Tools_Notifications
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple_Tools
 */

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

/**
 * Class DT_Network_Dashboard_Network_Endpoints
 */

class DT_Network_Dashboard_Network_Endpoints extends DT_Network_Dashboard_Endpoints_Base
{
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
        parent::__construct();

        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    } // End __construct()

    public function add_api_routes() {
        register_rest_route(
            $this->public_namespace, '/network_dashboard/collector', [
                'methods'  => 'POST',
                'callback' => [ $this, 'collector' ],
            ]
        );

        register_rest_route(
            $this->public_namespace, '/network_dashboard/activity', [
                'methods'  => 'POST',
                'callback' => [ $this, 'activity' ],
            ]
        );

        register_rest_route(
            $this->public_namespace, '/network_dashboard/trigger_activity', [
                'methods'  => 'POST',
                'callback' => [ $this, 'trigger_activity' ],
            ]
        );
    }

    public function collector( WP_REST_Request $request ) {
        $params = $this->process_token( $request );
        if ( is_wp_error( $params ) ) {
            return [
                'status' => 'FAIL',
                'error' => $params,
            ];
        }

        if ( ! isset( $params['snapshot'] ) || empty( $params['snapshot'] ) ){
            return [
                'status' => 'FAIL',
                'error' => $params,
            ];
        }

        return DT_Network_Dashboard_Site_Post_Type::update_snapshot( $params['snapshot'], $params['site_post_id'] );
    }

    public function activity( WP_REST_Request $request ) {
        dt_write_log(__METHOD__);
        $params = $this->process_token( $request );
        if ( is_wp_error( $params ) ) {
            return [
                'status' => 'FAIL',
                'error' => $params,
            ];
        }

        global $wpdb;
        $activity = $wpdb->get_results("
                SELECT * 
                FROM $wpdb->dt_movement_log
                LIMIT 5000
                ", ARRAY_A );

        $data = DT_Network_Activity_Log::convert_log_for_sending( $activity );
        return $data;
    }

    public function trigger_activity( WP_REST_Request $request ) {
        dt_write_log(__METHOD__);
        $params = $this->process_token( $request );
        if ( is_wp_error( $params ) ) {
            return [
                'status' => 'FAIL',
                'error' => $params,
            ];
        }

        $site_vars = Site_Link_System::get_site_connection_vars( $params['site_post_id'], 'post_id');
        if (is_wp_error($site_vars)) {
            dt_write_log( __METHOD__, 'FAIL ID: ' . $params['site_post_id'] . ' (Failed to get valid site link connection details)');
            return $site_vars;
        }

        $args = [
            'method' => 'POST',
            'body' => [
                'transfer_token' => $site_vars['transfer_token'],
            ]
        ];
        $result = wp_remote_post( 'https://' . $site_vars['url'] . '/wp-json/dt-public/v1/network_dashboard/activity', $args );

        if (is_wp_error($result)) {
            dt_write_log( __METHOD__, 'FAIL ID: ' . $site_vars['url'] . ' (Failed to get valid site link connection details)');
        }

        dt_write_log('result with body size');
//            dt_write_log($result);
        dt_write_log(json_decode( $result['body'], true));
        return true;
    }
}
DT_Network_Dashboard_Network_Endpoints::instance();


