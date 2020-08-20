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

    private $version = 1;
    private $namespace;
    private $public_namespace;

    /**
     * DT_Network_Dashboard_Admin_Endpoints The single instance of DT_Network_Dashboard_Admin_Endpoints.
     *
     * @var     object
     * @access    private
     * @since     0.1.0
     */
    private static $_instance = null;

    /**
     * Main DT_Network_Dashboard_Admin_Endpoints Instance
     * Ensures only one instance of DT_Network_Dashboard_Admin_Endpoints is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return DT_Network_Dashboard_Admin_Endpoints instance
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
        if ( ! user_can( get_current_user_id(), 'manage_options' ) ) {
            return new WP_Error( __METHOD__, 'Permission Error' );
        }
        add_action( 'get-sites-snapshot', [ $this, 'remote_snapshot_action' ] );

        wp_schedule_single_event( time(), 'get-sites-snapshot' );
        spawn_cron();

        return true;
    }

    public static function remote_snapshot_action(){
        do_action( "dt_get_sites_snapshot" );
    }

    public function trigger_multisite_snapshot_collection( WP_REST_Request $request ){
        if ( ! user_can( get_current_user_id(), 'manage_options' ) ) {
            return new WP_Error( __METHOD__, 'Permission Error' );
        }

        add_action( 'get-multisite-snapshot', [ $this, 'multisite_action' ] );

        wp_schedule_single_event( time(), 'get-multisite-snapshot' );
        spawn_cron();

        return true;
    }
    public static function multisite_action(){
        do_action( "dt_get_multisite_snapshot" );
    }
}
DT_Network_Dashboard_Admin_Endpoints::instance();