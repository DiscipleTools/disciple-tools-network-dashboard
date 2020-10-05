<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class DT_Network_Dashboard_Metrics_Sites extends DT_Network_Dashboard_Metrics_Base {
    private static $_instance = null;
    public static function instance() {
        if (is_null( self::$_instance )) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        parent::__construct();

        if (current_user_can( 'view_any_contacts' ) || current_user_can( 'view_project_metrics' )) {

            $url_path = $this->url_path;

            if ('network/sites' === substr( $url_path, '0', 13 )) {
                add_action( 'wp_enqueue_scripts', [ $this, 'sites_script' ], 99 );
            }
        } // end admin only test
    }


    public function add_url( $template_for_url) {
        $template_for_url['network/sites'] = 'template-metrics.php';
        return $template_for_url;
    }


    public function menu( $content) {
        $content .= '<li><a href="' . esc_url( site_url( '/network/sites/' ) ) . '" onclick="show_network_home()">' . esc_html__( 'Sites' ) . '</a></li>';
        return $content;
    }

    public function sites_script() {

        // UI script
        wp_enqueue_script('dt_network_dashboard_script_sites',
            trailingslashit( plugin_dir_url( __FILE__ ) ) . 'metrics-sites.js',
            [
                'jquery',

            ],
            filemtime( plugin_dir_path( __DIR__ ) . 'metrics/metrics-sites.js' ),
            true);
        wp_localize_script(
            'dt_network_dashboard_script_sites',
            'wpApiNetworkDashboardSites',
            [
                'root' => esc_url_raw( rest_url() ),
                'plugin_uri' => plugin_dir_url( __DIR__ ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'spinner' => ' <img src="' . plugin_dir_url( __DIR__ ) . 'spinner.svg" width="12px" />',
                'spinner_large' => ' <img src="' . plugin_dir_url( __DIR__ ) . 'spinner.svg" width="24px" />',
                'translations' => [
                ]
            ]
        );

    }

}
DT_Network_Dashboard_Metrics_Sites::instance();