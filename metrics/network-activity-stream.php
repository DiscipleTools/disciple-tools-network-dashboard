<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.


class DT_Network_Dashboard_Metrics_Activity_Feed extends DT_Network_Dashboard_Metrics_Base
{
    public function __construct() {
        parent::__construct();

        $this->base_slug = 'activity';
        $this->slug = 'stream';
        $this->base_title = __( 'Log', 'disciple_tools' );
        $this->title = __( 'Log', 'disciple_tools' );
        $this->menu_title = 'Log';
        $this->url = $this->root_slug . '/' . $this->base_slug . '/'  . $this->slug;
        $this->key = $this->root_slug . '_' . $this->base_slug . '_' . $this->slug;
        $this->js_file_name = $this->root_slug . '-' . $this->base_slug . '-' . $this->slug . '.js';
        $this->js_object_name = $this->key;

        add_filter( 'dt_network_dashboard_build_menu', [ $this, 'menu' ], 50 );
        add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ], 199 );

        if ( $this->url === $this->url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'add_scripts' ], 99 );
        }

    }

    public function add_scripts() {
        wp_enqueue_script( 'network_activity_script',
            plugin_dir_url( __FILE__ ) . 'network-activity.js',
            [
            'jquery',
            'network_base_script',
            ],
            filemtime( plugin_dir_path( __FILE__ ) . 'network-activity.js' ),
        true );
        wp_enqueue_script( $this->js_object_name .'_script',
            plugin_dir_url( __FILE__ ) . $this->js_file_name,
            [
            'jquery',
            'network_base_script',
            'network_activity_script',
            ],
            filemtime( plugin_dir_path( __FILE__ ) . $this->js_file_name ),
        true );
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
}
new DT_Network_Dashboard_Metrics_Activity_Feed();
