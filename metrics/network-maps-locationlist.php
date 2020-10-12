<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.


class DT_Network_Dashboard_Metrics_Maps_Locationlist extends DT_Network_Dashboard_Metrics_Base
{
    public function __construct() {
        parent::__construct();

        $this->base_slug = 'maps';
        $this->slug = 'locationlist';
        $this->base_title = __( 'Location List', 'disciple_tools' );
        $this->title = __( 'Location List', 'disciple_tools' );
        $this->menu_title = 'Location List';
        $this->url = $this->root_slug . '/' . $this->base_slug . '/'  . $this->slug;
        $this->key = $this->root_slug . '_' . $this->base_slug . '_' . $this->slug;
        $this->js_file_name = $this->root_slug . '-' . $this->base_slug . '-' . $this->slug . '.js';
        $this->js_object_name = $this->key;

        add_filter( 'dt_network_dashboard_build_menu', [ $this, 'menu' ], 50 );
        add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ], 199 );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
        add_filter( 'dt_mapping_module_data', [ $this, 'filter_mapping_module_data' ], 50, 1 );

        if ( $this->url === $this->url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'add_scripts' ], 99 );
        }
    }

    public function add_scripts() {
        wp_enqueue_script( $this->js_object_name .'_script', plugin_dir_url(__FILE__) . $this->js_file_name, [
            'jquery',
            'network_base_script',
        ], filemtime( plugin_dir_path(__FILE__) . $this->js_file_name ), true );
        wp_localize_script(
            $this->js_object_name .'_script', $this->js_object_name, [
                'endpoint' => $this->url,
                'data' => $this->get_locations_list(),

            ]
        );

        wp_enqueue_script( 'mapping-drill-down', get_template_directory_uri() . '/dt-mapping/drill-down.js', [ 'jquery', 'lodash' ], '1.1' );
        wp_localize_script(
            'mapping-drill-down',
            'mappingModule',
            array(
                'mapping_module' => $this->localize_script(),
            )
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

    public function filter_mapping_module_data( $data) {
        $data['custom_column_labels'] = $this->location_data_types();
        return $data;
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

        return $params;
    }

}
new DT_Network_Dashboard_Metrics_Maps_Locationlist();
