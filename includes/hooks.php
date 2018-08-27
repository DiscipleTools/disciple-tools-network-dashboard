<?php

/**
 * Class DT_Zume_Hooks
 */
class DT_Saturation_Mapping_Hooks
{

    private static $_instance = null;

    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Build hook classes
     */
    public function __construct()
    {
        new DT_Saturation_Mapping_Metrics();
    }
}
DT_Saturation_Mapping_Hooks::instance();

/**
 * Empty class for now..
 * Class DT_Zume_Hook_Base
 */
abstract class DT_Saturation_Mapping_Base
{
    public function __construct()
    {
    }
}

class DT_Saturation_Mapping_Metrics extends DT_Saturation_Mapping_Base
{
    /**
     * This filter adds a menu item to the metrics
     *
     * @param $content
     *
     * @return string
     */
    public function menu( $content ) {
        $content .= '<li><a href="'. site_url( '/metrics/saturation/' ) .'#saturation_mapping_overview" onclick="show_saturation_mapping_overview()">' .  esc_html__( 'Saturation Mapping' ) . '</a>
            <ul class="menu vertical nested is-active">
              <li><a href="'. site_url( '/metrics/saturation/' ) .'#saturation_mapping_overview" onclick="show_saturation_mapping_overview()">' .  esc_html__( 'Overview' ) . '</a></li>
              <li><a href="'. site_url( '/metrics/saturation/' ) .'#saturation_tree" onclick="show_saturation_tree()">' .  esc_html__( 'Tree' ) . '</a></li>
              <li><a href="'. site_url( '/metrics/saturation/' ) .'#saturation_map" onclick="show_saturation_map()">' .  esc_html__( 'Map' ) . '</a></li>
            </ul>
          </li>';
        return $content;
    }

    /**
     * Load scripts for the plugin
     */
    public function scripts() {
        wp_enqueue_script( 'dt_saturation_mapping_script', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'metrics.js', [
            'jquery',
            'jquery-ui-core',
        ], filemtime( plugin_dir_path(__DIR__ ) . 'includes/metrics.js' ), true );

        wp_localize_script(
            'dt_saturation_mapping_script', 'wpApiSatMapMetrics', [
                'root' => esc_url_raw( rest_url() ),
                'plugin_uri' => plugin_dir_url(__DIR__ ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'map_key' => dt_get_option( 'map_key' ),
                'stats' => [
                    'table' => DT_Saturation_Mapping_Stats::get_location_table(),
                    'tree' => DT_Saturation_Mapping_Stats::get_location_tree(),
                    'map' => DT_Saturation_Mapping_Stats::get_location_map(),
                ],
                'translations' => [
                    "sm_title" => __( "Saturation Mapping", "dt_zume" ),
                ]
            ]
        );
    }

    public function add_url( $template_for_url ) {
        $template_for_url['metrics/saturation'] = 'template-metrics.php';
        return $template_for_url;
    }

    public function __construct() {
        if ( isset( $_SERVER["SERVER_NAME"] ) ) {
            $url  = ( !isset( $_SERVER["HTTPS"] ) || @( $_SERVER["HTTPS"] != 'on' ) ) ? 'http://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) ) : 'https://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) );
            if ( isset( $_SERVER["REQUEST_URI"] ) ) {
                $url .= sanitize_text_field( wp_unslash( $_SERVER["REQUEST_URI"] ) );
            }
        }
        $url_path = trim( str_replace( get_site_url(), "", $url ), '/' );

        if ( 'metrics' === substr( $url_path, '0', 7 ) ) {

            add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ] ); // add custom URL
            add_filter( 'dt_metrics_menu', [ $this, 'menu' ], 99 );

            if ( 'metrics/saturation' === $url_path ) {
                add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
            }
        }
    }
}