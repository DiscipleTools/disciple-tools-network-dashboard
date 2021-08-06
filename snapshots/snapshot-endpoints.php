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
 * Class DT_Network_Dashboard_Snapshot_Endpoints
 */

class DT_Network_Dashboard_Snapshot_Endpoints extends DT_Network_Dashboard_Endpoints_Base
{

    /**
     * DT_Network_Dashboard_Snapshot_Endpoints The single instance of DT_Network_Dashboard_Snapshot_Endpoints.
     *
     * @var     object
     * @access    private
     * @since     0.1.0
     */
    private static $_instance = null;

    /**
     * Main DT_Network_Dashboard_Snapshot_Endpoints Instance
     * Ensures only one instance of DT_Network_Dashboard_Snapshot_Endpoints is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return DT_Network_Dashboard_Snapshot_Endpoints instance
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

        add_action( 'rest_api_init', array( $this, 'add_api_routes' ) );
    } // End __construct()

    public function add_api_routes() {
        register_rest_route(
            $this->public_namespace,
            '/network_dashboard/live_stats',
            array(
                'methods'  => 'POST',
                'callback' => array( $this, 'live_stats' ),
                'permission_callback' => '__return_true',
            )
        );
        register_rest_route(
            $this->public_namespace,
            '/network_dashboard/profile',
            array(
                'methods'  => 'POST',
                'callback' => array( $this, 'profile' ),
                'permission_callback' => '__return_true',
            )
        );

    }

    public function live_stats( WP_REST_Request $request ) {
        $params = $this->process_token( $request );
        if ( is_wp_error( $params ) ) {
            return array(
                'status' => 'FAIL',
                'error' => $params,
            );
        }

        $snapshot = DT_Network_Dashboard_Snapshot::snapshot_report();

        $snapshot = apply_filters( 'dt_network_dashboard_snapshot_location_precision', $snapshot, $params['site_post_id'] );

        return $snapshot;
    }

    public function profile( WP_REST_Request $request ) {
        $params = $this->process_token( $request );
        if ( is_wp_error( $params ) ) {
            return array(
                'status' => 'FAIL',
                'error' => $params,
            );
        }

        return dt_network_site_profile();
    }

}
DT_Network_Dashboard_Snapshot_Endpoints::instance();


