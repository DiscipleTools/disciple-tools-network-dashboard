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
        $this->url =  $this->root_slug . '/' . $this->base_slug;
        $this->key = $this->root_slug . '_' . $this->base_slug;
        $this->js_file_name = $this->root_slug . '-' . $this->base_slug . '.js';
        $this->js_object_name = $this->key;

        add_filter( 'dt_network_dashboard_build_menu', [ $this, 'menu' ], 1 );
        add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ], 199 );
        add_filter( 'dt_mapping_module_data', [ $this, 'filter_mapping_module_data' ], 50, 1 );

        if ( $this->root_slug === $this->url_path || $this->url === $this->url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'add_scripts' ], 99 );
        }
    }

    public function add_scripts() {
        wp_enqueue_script( $this->js_object_name .'_script', plugin_dir_url(__FILE__) . $this->js_file_name, [
            'jquery',
            'datatable',
            'mapping-drill-down',
            'dt_mapping_js',
            'network_base_script',
        ], filemtime( plugin_dir_path(__FILE__) . $this->js_file_name ), true );

        $this->load_grid_mapping_scripts();
    }

    public function menu( $tree ){
        $tree[$this->base_slug] = [
            'key' => $this->key,
            'label' => $this->menu_title,
            'url' => '/' . $this->url,
            'children' => []
        ];
        return $tree;
    }

    public function add_url( $template_for_url) {
        $template_for_url['network'] = 'template-metrics.php';
        $template_for_url[$this->url] = 'template-metrics.php';
        return $template_for_url;
    }

}
new DT_Network_Dashboard_Metrics_Home();
