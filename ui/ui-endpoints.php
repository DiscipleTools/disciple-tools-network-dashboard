<?php
/**
 * Rest endpoints
 *
 * @package  Disciple_Tools
 * @category Plugin
 * @since    0.1
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Network_Dashboard_UI_Endpoints
 */
class DT_Network_Dashboard_UI_Endpoints
{

    private $version = 1;
    private $namespace;
    private $public_namespace;

    /**
     * DT_Network_Dashboard_UI_Endpoints The single instance of DT_Network_Dashboard_UI_Endpoints.
     *
     * @var     object
     * @access    private
     * @since     0.1.0
     */
    private static $_instance = null;

    /**
     * Main DT_Network_Dashboard_UI_Endpoints Instance
     * Ensures only one instance of DT_Network_Dashboard_UI_Endpoints is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return DT_Network_Dashboard_UI_Endpoints instance
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
        $this->namespace = "dt/v" . intval( $this->version );
        $this->public_namespace = "dt-public/v" . intval( $this->version );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    } // End __construct()

    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/network/ui/trigger_transfer', [
                'methods'  => 'POST',
                'callback' => [ $this, 'trigger_transfer' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/network/ui/get_network_report', [
                'methods'  => 'POST',
                'callback' => [ $this, 'get_network_report' ],
            ]
        );
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public function trigger_transfer( WP_REST_Request $request ) {

        if ( ! user_can( get_current_user_id(), 'network_dashboard_viewer' ) ) {
            return new WP_Error( __METHOD__, 'Permission error.' );
        }

        $params = $request->get_params();
        if ( isset( $params['id'] ) && isset( $params['type'] ) ) {
            return DT_Network_Dashboard_Reports::trigger_transfer( $params['id'], $params['type'] );
        } else {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }
    }

    public function get_network_report( WP_REST_Request $request ) {

        if ( ! user_can( get_current_user_id(), 'network_dashboard_viewer' ) ) {
            return new WP_Error( __METHOD__, 'Permission error.' );
        }

        $params = $request->get_params();
        if ( isset( $params['id'] ) ) {
            return dt_network_dashboard_queries( 'get_report_by_id', [ 'id' => $params['id'] ] );
        } else {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }
    }
}
DT_Network_Dashboard_UI_Endpoints::instance();