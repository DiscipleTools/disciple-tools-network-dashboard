<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.


class DT_Network_Dashboard_Metrics_Activity_Map extends DT_Network_Dashboard_Metrics_Base
{
    public function __construct() {
        if ( empty( DT_Mapbox_API::get_key() ) ){
            return;
        }
        parent::__construct();

        $this->base_slug = 'activity';
        $this->slug = 'map';
        $this->base_title = __( 'Map', 'disciple-tools-network-dashboard' );
        $this->title = __( 'Map', 'disciple-tools-network-dashboard' );
        $this->menu_title = __( 'Map', 'disciple-tools-network-dashboard' );
        $this->url = $this->root_slug . '/' . $this->base_slug . '/'  . $this->slug;
        $this->key = $this->root_slug . '_' . $this->base_slug . '_' . $this->slug;
        $this->js_file_name = $this->root_slug . '-' . $this->base_slug . '-' . $this->slug . '.js';
        $this->js_object_name = $this->key;

        add_filter( 'dt_network_dashboard_build_menu', array( $this, 'menu' ), 60 );
        add_filter( 'dt_templates_for_urls', array( $this, 'add_url' ), 199 );
        add_action( 'rest_api_init', array( $this, 'add_api_routes' ) );

        if ( $this->url === $this->url_path ) {
            add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ), 99 );
        }
    }

    public function add_scripts() {
        wp_enqueue_script( 'network_activity_script',
            plugin_dir_url( __FILE__ ) . 'network-activity.js',
            array(
            'jquery',
            'network_base_script',
            ),
            filemtime( plugin_dir_path( __FILE__ ) . 'network-activity.js' ),
        true );
        wp_enqueue_script( $this->js_object_name .'_script',
            plugin_dir_url( __FILE__ ) . $this->js_file_name,
            array(
            'jquery',
            'network_base_script',
            'network_activity_script'
            ),
            filemtime( plugin_dir_path( __FILE__ ) . $this->js_file_name ),
        true );
        wp_localize_script(
            $this->js_object_name .'_script',
            $this->js_object_name,
            array(
                'endpoint' => $this->url,
                'map_key' => DT_Mapbox_API::get_key(),
            )
        );
    }

    public function menu( $tree ){
        $tree[$this->base_slug]['children'][$this->slug] = array(
            'key' => $this->key,
            'label' => __( 'Map', 'disciple-tools-network-dashboard' ),
            'url' => '/'.$this->url,
            'children' => array()
        );
        return $tree;
    }

    public function add_url( $template_for_url) {
        $template_for_url[$this->url] = 'template-metrics.php';
        return $template_for_url;
    }

    public function add_api_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->url . '/',
            array(
                array(
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => array( $this, 'endpoint' ),
                    'permission_callback' => '__return_true',
                ),
            )
        );
    }

    public function endpoint( WP_REST_Request $request ){
        if ( !$this->has_permission() ) {
            return new WP_Error( __METHOD__, "Missing Permissions", array( 'status' => 400 ) );
        }

        $params = $request->get_params();
        if ( isset( $params['filters'] ) && ! empty( $params['filters'] ) ){
            $filters = dt_recursive_sanitize_array( $params['filters'] );
            $feed = $this->get_activity_log( $filters );
        } else {
            $feed = $this->get_activity_log();
        }

        /**
         * Build GEOJSON
         */
        $features = array();
        $no_location = 0;
        $has_location = 0;
        foreach ( $feed as $value ) {
            if ( empty( $value['lng'] ) || empty( $value['lat'] ) ) {
                $no_location++;
                continue;
            }
            $has_location++;

            $features[] = array(
                'type' => 'Feature',
                'properties' => array(
                    "name" => $value['site_name'] ?? '',
                    "action" => $value['action'],
                ),
                'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => array(
                        $value['lng'],
                        $value['lat'],
                        1
                    ),
                ),
            );
        }

        $geojson = array(
            'type' => 'FeatureCollection',
            'no_location' => $no_location,
            'has_location' => $has_location,
            'features' => $features,
        );

        return $geojson;

    }
}
new DT_Network_Dashboard_Metrics_Activity_Map();
