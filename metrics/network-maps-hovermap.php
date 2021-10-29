<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.


class DT_Network_Dashboard_Metrics_Maps_Hovermap extends DT_Network_Dashboard_Metrics_Base
{
    public function __construct() {
        parent::__construct();

        $this->base_slug = 'maps';
        $this->slug = 'hovermap';
        $this->base_title = __( 'Hover Map', 'disciple-tools-network-dashboard' );
        $this->title = __( 'Hover Map', 'disciple-tools-network-dashboard' );
        $this->menu_title = __( 'Hover Map', 'disciple-tools-network-dashboard' );
        $this->url = $this->root_slug . '/' . $this->base_slug . '/'  . $this->slug;
        $this->key = $this->root_slug . '_' . $this->base_slug . '_' . $this->slug;
        $this->js_file_name = $this->root_slug . '-' . $this->base_slug . '-' . $this->slug . '.js';
        $this->js_object_name = $this->key;

        add_filter( 'dt_network_dashboard_build_menu', array( $this, 'menu' ), 50 );
        add_filter( 'dt_templates_for_urls', array( $this, 'add_url' ), 199 );
        add_action( 'rest_api_init', array( $this, 'add_api_routes' ) );
        add_filter( 'dt_mapping_module_data', array( $this, 'filter_mapping_module_data' ), 50, 1 );

        if ( $this->url === $this->url_path ) {
            add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ), 99 );
        }
    }

    public function add_scripts() {
        wp_enqueue_script( $this->js_object_name .'_script',
            plugin_dir_url( __FILE__ ) . $this->js_file_name,
            array(
            'jquery',
            'jquery-ui-core',
            'amcharts-core',
            'amcharts-animated',
            'amcharts-maps',
            'mapping-drill-down',
            'lodash',
            ),
            filemtime( plugin_dir_path( __FILE__ ) . $this->js_file_name ),
        true );

        $this->load_grid_mapping_scripts();
    }

    public function menu( $tree ){
        $tree[$this->base_slug]['children'][$this->slug] = array(
            'key' => $this->key,
            'label' => __( 'Hover Map', 'disciple-tools-network-dashboard' ),
            'url' => '/'.$this->url,
            'children' => array()
        );
        return $tree;
    }

    public function add_url( $template_for_url ) {
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

        return $params;
    }

}
new DT_Network_Dashboard_Metrics_Maps_Hovermap();
