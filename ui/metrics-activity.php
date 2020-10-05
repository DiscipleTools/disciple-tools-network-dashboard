<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.


class DT_Network_Dashboard_Metrics_Activity extends DT_Network_Dashboard_Metrics_Base
{
    //slug and title of the top menu folder
    public $base_slug = 'activity'; // lowercase
    public $slug = 'feed'; // lowercase
    public $url;
    public $title;
    public $base_title;
    public $js_object_name = 'wpNetworkDashboardActivity'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = 'metrics-activity.js'; // should be full file name plus extension

    public function __construct() {
        parent::__construct();


        $this->base_title = __( 'Activity', 'disciple_tools' );
        $this->title = __( 'Feed', 'disciple_tools' );

        add_filter( 'dt_metrics_menu', [ $this, 'menu' ], 10 );

        $url_path = dt_get_url_path();
        $this->url = $this->root_slug . '/' . $this->base_slug . '/' . $this->slug;
        if ( $this->url === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
        }
        add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
    }

    public function scripts() {
//        wp_register_script( 'amcharts-core', 'https://www.amcharts.com/lib/4/core.js', false, '4' );
//        wp_register_script( 'amcharts-charts', 'https://www.amcharts.com/lib/4/charts.js', false, '4' );
//        wp_register_script( 'amcharts-animated', 'https://www.amcharts.com/lib/4/themes/animated.js', [ 'amcharts-core' ], '4' );


        wp_enqueue_script( 'dt_'.$this->root_slug . $this->base_slug . $this->slug .'_script', plugin_dir_url(__FILE__) . $this->js_file_name, [
            'jquery',
//            'jquery-ui-core',
//            'amcharts-core',
//            'amcharts-charts',
//            'amcharts-animated',
//            'lodash'
        ], filemtime( plugin_dir_path(__FILE__) . $this->js_file_name ), true );

        wp_localize_script(
            'dt_network_dashboard'.$this->base_slug . $this->slug .'_script', $this->js_object_name, [
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
        $content .= '<li><a href="' . esc_url( site_url( '/' . $this->url ) ) . '" onclick="show_network_home()">' . esc_html__( 'Activity' ) . '</a></li>';
        return $content;
    }

}
new DT_Network_Dashboard_Metrics_Activity();
