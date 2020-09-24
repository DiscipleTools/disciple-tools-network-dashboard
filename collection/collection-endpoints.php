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

        return DT_Network_Dashboard_Snapshot::save_remote_snapshot( $params['snapshot'] );

    }
}
DT_Network_Dashboard_Network_Endpoints::instance();


