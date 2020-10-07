<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.


class DT_Network_Dashboard_Metrics_Activity_Chart extends DT_Network_Dashboard_Metrics_Base
{
    public function __construct() {
        parent::__construct();

        $this->base_slug = 'activity';
        $this->slug = 'chart';
        $this->base_title = __( 'Chart', 'disciple_tools' );
        $this->title = __( 'Chart', 'disciple_tools' );
        $this->menu_title = 'Chart';
        $this->url = $this->root_slug . '/' . $this->base_slug . '/'  . $this->slug;

        add_filter( 'dt_network_dashboard_build_menu', [ $this, 'menu' ], 55 );
        add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ], 199 );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );

        if ( $this->url === $this->url_path ) {
            $this->js_file_name = $this->root_slug . '-' . $this->base_slug . '-' . $this->slug . '.js';
            $this->js_object_name = 'dt_' . $this->root_slug . '_' . $this->base_slug . '_' . $this->slug;
            add_action( 'wp_enqueue_scripts', [ $this, 'add_scripts' ], 99 );
        }

    }

    public function add_scripts() {
        wp_enqueue_script( $this->js_object_name .'_script', plugin_dir_url(__FILE__) . $this->js_file_name, [
            'jquery',
        ], filemtime( plugin_dir_path(__FILE__) . $this->js_file_name ), true );
        wp_localize_script(
            $this->js_object_name .'_script', $this->js_object_name, [
                'root' => esc_url_raw( rest_url() ),
                'theme_uri' => get_template_directory_uri(),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
            ]
        );
    }

    public function menu( $tree ){
        $tree[$this->base_slug]['children'][$this->slug] = [
            'key' => $this->slug,
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
            $this->namespace, '/network/'.$this->base_slug.'/'.$this->slug . '/', [
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
        dt_write_log(__METHOD__);
        $params = $request->get_params();

        return $params;
    }

}
new DT_Network_Dashboard_Metrics_Activity_Chart();
