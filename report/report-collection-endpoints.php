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
 * Class DT_Network_Dashboard_Report_Collection_Endpoints
 */
class DT_Network_Dashboard_Report_Collection_Endpoints
{

    private $version = 1;
    private $namespace;
    private $public_namespace;

    /**
     * DT_Network_Dashboard_Report_Collection_Endpoints The single instance of DT_Network_Dashboard_Report_Collection_Endpoints.
     *
     * @var     object
     * @access    private
     * @since     0.1.0
     */
    private static $_instance = null;

    /**
     * Main DT_Network_Dashboard_Report_Collection_Endpoints Instance
     * Ensures only one instance of DT_Network_Dashboard_Report_Collection_Endpoints is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return DT_Network_Dashboard_Report_Collection_Endpoints instance
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
}
DT_Network_Dashboard_Report_Collection_Endpoints::instance();