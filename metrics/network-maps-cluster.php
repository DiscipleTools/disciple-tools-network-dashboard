<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.


class DT_Network_Dashboard_Metrics_Maps_Cluster extends DT_Network_Dashboard_Metrics_Base
{
    public function __construct() {
        if ( empty( DT_Mapbox_API::get_key() ) ){
            return;
        }
        parent::__construct();

//        $this->base_slug = 'maps';
//        $this->slug = 'cluster';
//        $this->base_title = __( 'Cluster Map', 'disciple_tools' );
//        $this->title = __( 'Cluster Map', 'disciple_tools' );
//        $this->menu_title = 'Cluster Map';
//        $this->url = $this->root_slug . '/' . $this->base_slug . '/'  . $this->slug;
//        $this->key = $this->root_slug . '_' . $this->base_slug . '_' . $this->slug;
//        $this->js_file_name = $this->root_slug . '-' . $this->base_slug . '-' . $this->slug . '.js';
//        $this->js_object_name = $this->key;
//
//        add_filter( 'dt_network_dashboard_build_menu', [ $this, 'menu' ], 50 );
//        add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ], 199 );
//        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
//
//        if ( $this->url === substr( $this->url_path, 0, 20 )  ) {
//            add_action( 'wp_enqueue_scripts', [ $this, 'add_scripts' ], 99 );
//        }
//
//        add_filter('dashboard_cluster_layer_geojson', [$this, 'cluster_geojson_contacts'], 10, 2 );
//        add_filter('dashboard_cluster_layer_geojson', [$this, 'cluster_geojson_groups'], 10, 2 );
    }

    public function add_scripts() {
        wp_enqueue_script( $this->js_object_name .'_script',
            plugin_dir_url( __FILE__ ) . $this->js_file_name,
            [
            'jquery',
            'network_base_script',
            ],
            filemtime( plugin_dir_path( __FILE__ ) . $this->js_file_name ),
        true );
        wp_localize_script(
            $this->js_object_name .'_script',
            $this->js_object_name,
            [
                'endpoint' => $this->url,
                'map_key' => DT_Mapbox_API::get_key(),
            ]
        );
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
            $this->namespace,
            '/' . $this->url . '/',
            [
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

        $params = $request->get_json_params() ?? $request->get_body_params();
        if ( ! isset( $params['post_type'] ) || empty( $params['post_type'] ) ) {
            return new WP_Error( __METHOD__, "Missing Post Types", [ 'status' => 400 ] );
        }

        $post_type = sanitize_text_field( wp_unslash( $params['post_type'] ) );

        return apply_filters( 'dashboard_cluster_layer_geojson', $this->_empty_geojson(), $post_type );
        ;
    }

    public function cluster_geojson_contacts( $geojson, $post_type ) {
        if ( 'contacts' !== $post_type ) {
            return $geojson;
        }

        global $wpdb;

        $sites = $this->get_sites();
        $list = [];
        foreach ( $sites as $site ){
            if ( ! isset( $site['locations']['contacts']['active'] ) ) {
                continue;
            }
            foreach ( $site['locations']['contacts']['active'] as $index => $value ) {
                if ( ! isset( $list[$index] ) ) {
                    $list[$index] = $value;
                }
                $list[$index] = $list[$index] + $value;
            }
        }

        $grid = $wpdb->get_results( "SELECT grid_id, longitude, latitude FROM $wpdb->dt_location_grid", ARRAY_A );
        $grid_list = [];
        foreach ( $grid as $g ){
            $grid_list[$g['grid_id']] = $g;
        }

        $features = [];
        foreach ( $list as $grid_id => $value ) {
//            if ( ! isset( $grid_list[$grid_id] ) || empty( $grid_list[$grid_id] ) ) {
//                continue;
//            }

            $features[] = array(
                'type' => 'Feature',
                'properties' => array(
                    "name" => $grid_list[$grid_id]['name'] ?? '',
                    "count" => $value,
                ),
                'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => array(
                        $grid_list[$grid_id]['longitude'],
                        $grid_list[$grid_id]['latitude'],
                        1
                    ),
                ),
            );
        }

        $geojson = array(
            'type' => 'FeatureCollection',
            'features' => $features,
        );

        return $geojson;

    }
    public function cluster_geojson_groups( $geojson, $post_type ) {
        if ( 'groups' !== $post_type ) {
            return $geojson;
        }

        //        if ( $post_type === 'contacts' ) {
//            $results = Disciple_Tools_Mapping_Queries::get_contacts_grid_totals( $status );
//        } else if ( $post_type === 'groups' ) {
//            $results = Disciple_Tools_Mapping_Queries::get_groups_grid_totals( $status );
//        } else {
//            return new WP_Error( __METHOD__, "Invalid post type", [ 'status' => 400 ] );
//        }


        return $geojson;
    }

}
new DT_Network_Dashboard_Metrics_Maps_Cluster();
