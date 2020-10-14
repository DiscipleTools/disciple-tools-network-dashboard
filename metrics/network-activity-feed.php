<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.


class DT_Network_Dashboard_Metrics_Activity_Feed extends DT_Network_Dashboard_Metrics_Base
{
    public function __construct() {
        parent::__construct();

        $this->base_slug = 'activity';
        $this->slug = 'feed';
        $this->base_title = __( 'Feed', 'disciple_tools' );
        $this->title = __( 'Feed', 'disciple_tools' );
        $this->menu_title = 'Feed';
        $this->url = $this->root_slug . '/' . $this->base_slug . '/'  . $this->slug;
        $this->key = $this->root_slug . '_' . $this->base_slug . '_' . $this->slug;
        $this->js_file_name = $this->root_slug . '-' . $this->base_slug . '-' . $this->slug . '.js';
        $this->js_object_name = $this->key;

        add_filter( 'dt_network_dashboard_build_menu', [ $this, 'menu' ], 50 );
        add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ], 199 );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );

        if ( $this->url === $this->url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'add_scripts' ], 99 );
        }

        add_filter( 'dt_network_dashboard_build_message', [ $this, 'filter_studying_message'] );

    }

    public function add_scripts() {
        wp_enqueue_script( $this->js_object_name .'_script', plugin_dir_url(__FILE__) . $this->js_file_name, [
            'jquery',
            'network_base_script',
        ], filemtime( plugin_dir_path(__FILE__) . $this->js_file_name ), true );

    }

    public function menu( $tree ){
        $tree[$this->base_slug]['children'][$this->slug] = [
            'key' => $this->key,
            'label' => $this->menu_title,
            'url' => '/'.$this->url,
            'children' => []
        ];
        return $tree;
    }

    public function add_url( $template_for_url) {
        $template_for_url[$this->url] = 'template-metrics.php';
        return $template_for_url;
    }

    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/' . $this->url . '/', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'endpoint' ],
                ],
            ]
        );
    }

    public function endpoint( WP_REST_Request $request ){
        if ( !$this->has_permission() ) {
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }
        $params = $request->get_params();

        if ( isset( $params['time'] ) ) {
            $time = sanitize_text_field( wp_unslash( $params['time'] ) );
        } else {
            $time = null;
        }

        dt_network_dashboard_push_activity();

        return $this->build_log( $time );
    }

    public function build_log( $time ){

        $results = $this->query_log( $time );

        $results = apply_filters( 'dt_network_dashboard_build_message', $results );

        $data = [];
        foreach( $results as $index => $result ) {
            if ( ! isset( $data[$result['day']] ) ) {
                $data[$result['day']] = [];
            }
            if ( isset( $result['message'] ) ) {
                $data[$result['day']][] = $result['message'];
            } else {
                $data[$result['day']][] = '('. $result['time'].')' . '  | action: ' . $result['action'] . $result['category'] . ' (' . $result['label'] . ')' ;
            }
        }

        return $data;
    }

    public function query_log( $time = null, $site_id = null ){
        global $wpdb;
        if ( empty( $time ) ){
            $time = strtotime('-30 days' );
        }
        $results = $wpdb->get_results( $wpdb->prepare( "
                SELECT *, DATE_FORMAT(FROM_UNIXTIME(timestamp), '%Y-%c-%e') AS day, DATE_FORMAT(FROM_UNIXTIME(timestamp), '%H:%i %p') AS time
                FROM $wpdb->dt_movement_log 
                WHERE timestamp > %s 
                ORDER BY timestamp DESC
                ", $time ), ARRAY_A );


        foreach( $results as $index => $result ){
            $results[$index]['payload'] = maybe_unserialize( $result['payload']);
        }

        return $results;
    }

    public function filter_studying_message( $activity_log ){
        foreach( $activity_log as $index => $log ){
            if ( 'studying' === $log['category'] ) {
                $activity_log[$index]['message'] = '(' . $log['time'] . ') This is the message for studying. ('. $log['label'] . ')';
            }
        }

        return $activity_log;
    }

}
new DT_Network_Dashboard_Metrics_Activity_Feed();
