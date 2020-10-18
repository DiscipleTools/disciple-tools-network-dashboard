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
            $this->public_namespace, '/network_dashboard/trigger_activity', [
                'methods'  => 'POST',
                'callback' => [ $this, 'trigger_activity' ],
            ]
        );

        register_rest_route(
            $this->public_namespace, '/network_dashboard/activity', [
                'methods'  => 'POST',
                'callback' => [ $this, 'activity' ],
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

    public function trigger_activity( WP_REST_Request $request ) {
        dt_write_log(__METHOD__);

        // test if request source is valid
        $params = $this->process_token( $request );
        if ( is_wp_error( $params ) ) {
            return [
                'status' => 'FAIL',
                'error' => $params,
            ];
        }

        // @todo test if connection allows submission for activity log



        // @todo find the last site_record_id collected
        $last_record_collected = 0; // @todo query for last record collected.

        // request activity log from most recent site_record_id
        $site_vars = Site_Link_System::get_site_connection_vars( $params['site_post_id'], 'post_id');
        if (is_wp_error($site_vars)) {
            dt_write_log( __METHOD__, 'FAIL ID: ' . $params['site_post_id'] . ' (Failed to get valid site link connection details)');
            return $site_vars;
        }
        $args = [
            'method' => 'POST',
            'body' => [
                'transfer_token' => $site_vars['transfer_token'],
                'last_record_collected' => $last_record_collected,
            ]
        ];
        $result = wp_remote_post( 'https://' . $site_vars['url'] . '/wp-json/dt-public/v1/network_dashboard/activity', $args );
        if (is_wp_error($result)) {
            dt_write_log( __METHOD__, 'FAIL ID: ' . $site_vars['url'] . ' (Failed to get valid site link connection details)');
        }

        // @todo insert new activity log records
        dt_write_log(json_decode( $result['body'], true));


        return true;
    }

    public function activity( WP_REST_Request $request ) {
        dt_write_log(__METHOD__);

        // test if valid request for activity log
        $params = $this->process_token( $request );
        if ( is_wp_error( $params ) ) {
            return [
                'status' => 'FAIL',
                'error' => $params,
            ];
        }

        $dt_network_dashboard_id = get_post_meta( $params['site_post_id'], 'dt_network_dashboard', true );
        if ( empty( $dt_network_dashboard_id ) ){
            return [
                'status' => 'FAIL',
                'error' => 'No network dashboard post type setup for this site link. Check configuration.',
            ];
        }

        $send_activity_configuration = get_post_meta( $dt_network_dashboard_id, 'send_activity', true );
        if ( 'none' === $send_activity_configuration ) {
            return [
                'status' => 'REJECT',
                'error' => 'Activity collection is rejected by administrator configuration.',
            ];
        } else if ( 'live' === $send_activity_configuration) {
            return [
                'status' => 'REJECT',
                'error' => 'Activity log is currently being send live and does not need to be collected.',
            ];
        }

        // @todo check level of precision allowed to send activity

        $last_site_record_id = sanitize_text_field( wp_unslash( $params['last_site_record_id'] ?? 0 ) );
        global $wpdb;
        $activity = $wpdb->get_results( $wpdb->prepare( "
                SELECT * 
                FROM $wpdb->dt_movement_log
                WHERE site_record_id > %s
                ORDER BY site_record_id
                LIMIT 5000
                ", $last_site_record_id ), ARRAY_A );
        if ( is_wp_error( $activity ) ) {
            return [
                'status' => 'FAIL',
                'error' => $activity,
            ];
        }

        return [
            'status' => 'SUCCESS',
            'data' => $activity,
        ];
    }




}
DT_Network_Dashboard_Network_Endpoints::instance();


