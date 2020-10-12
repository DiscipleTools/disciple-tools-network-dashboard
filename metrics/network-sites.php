<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.


class DT_Network_Dashboard_Metrics_Sites extends DT_Network_Dashboard_Metrics_Base
{
    public function __construct() {
        parent::__construct();

        $this->base_slug = 'sites';
        $this->slug = '';
        $this->base_title = __( 'Site Reports', 'disciple_tools' );
        $this->title = __( 'Site Reports', 'disciple_tools' );
        $this->menu_title = 'Site Reports';
        $this->url = $this->root_slug . '/' . $this->base_slug;
        $this->key = $this->root_slug . '_' . $this->base_slug;
        $this->js_file_name = $this->root_slug . '-' . $this->base_slug . '.js';
        $this->js_object_name = $this->key;

        add_filter( 'dt_network_dashboard_build_menu', [ $this, 'menu' ], 4 );
        add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ], 199 );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );

        if ( $this->url === $this->url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'add_scripts' ], 99 );
        }

    }

    public function add_scripts() {
        wp_enqueue_script( $this->js_object_name .'_script', plugin_dir_url(__FILE__) . $this->js_file_name, [
            'jquery',
            'network_base_script',
            'datatable',
        ], filemtime( plugin_dir_path(__FILE__) . $this->js_file_name ), true );
    }

    public function menu( $tree ){
        // top levels load at 10, sub levels need to load at 50+
        $tree[$this->base_slug] = [
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
        $data = [];

        switch( $params['type'] ) {
            case 'sites_list':
                $data = $this->get_site_list();
                break;
            case 'locations_list':
                $data =  $this->get_locations_list();
                break;
            case 'sites':
            default:
                $data = $this->get_sites();
                break;
        }

        return $data;
    }

}
new DT_Network_Dashboard_Metrics_Sites();
