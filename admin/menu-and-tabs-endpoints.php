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
class DT_Network_Dashboard_Admin_Endpoints
{

    private $namespace;
    private $public_namespace;
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()


    public function __construct() {
        $this->namespace = "dt/v1";
        $this->public_namespace = "dt-public/v1";
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    } // End __construct()

    public function add_api_routes() {
        register_rest_route(
            $this->namespace,
            '/network/admin/trigger_snapshot_collection',
            [
                'methods'  => 'POST',
                'callback' => [ $this, 'trigger_snapshot_collection' ],
            ]
        );
        register_rest_route(
            $this->namespace,
            '/network/admin/trigger_multisite_snapshot_collection',
            [
                'methods'  => 'POST',
                'callback' => [ $this, 'trigger_multisite_snapshot_collection' ],
            ]
        );
    }

    public function trigger_snapshot_collection( WP_REST_Request $request ){
        if ( ! user_can( get_current_user_id(), 'manage_dt' ) ) {
            return new WP_Error( __METHOD__, 'Permission Error' );
        }

        dt_network_dashboard_collect_remote_sites();

        return true;
    }

    public function trigger_multisite_snapshot_collection( WP_REST_Request $request ){
        if ( ! user_can( get_current_user_id(), 'manage_dt' ) ) {
            return new WP_Error( __METHOD__, 'Permission Error' );
        }

        dt_network_dashboard_multisite_snapshot_async();

        return true;
    }

}
DT_Network_Dashboard_Admin_Endpoints::instance();