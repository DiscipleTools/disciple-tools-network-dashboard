<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.


class DT_Network_Dashboard_Metrics_Home extends DT_Network_Dashboard_Metrics_Base
{
    public function __construct() {
        parent::__construct();

        $this->base_slug = 'home';
        $this->slug = '';
        $this->base_title = __( 'Home', 'disciple_tools' );
        $this->title = __( 'Home', 'disciple_tools' );
        $this->menu_title = 'Home';
        $this->url = $this->root_slug . '/' . $this->base_slug;

        add_filter( 'dt_metrics_menu', [ $this, 'menu' ], 10 );
        add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ], 199 );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
        dt_write_log(__METHOD__);

        if ( $this->root_slug === $this->url_path || $this->url === $this->url_path ) {
            $this->js_file_name = $this->root_slug . '-' . $this->base_slug . '.js';
            $this->js_object_name = 'dt_' . $this->root_slug . '_' . $this->base_slug;
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

    public function menu( $content) {
        // home
        $content .= '<li><a href="/' . $this->url  . '/">' . esc_html( $this->menu_title ) . '</a></li>';
        return $content;
    }

    public function add_url( $template_for_url) {
        $template_for_url['network'] = 'template-metrics.php';
        $template_for_url['network/home'] = 'template-metrics.php';
        return $template_for_url;
    }

    public function add_api_routes() {
        dt_write_log(__METHOD__); // @todo not passing through to here.
        register_rest_route(
            $this->namespace, '/network/', [
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
new DT_Network_Dashboard_Metrics_Home();
