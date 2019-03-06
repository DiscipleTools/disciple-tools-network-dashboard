<?php
/**
 * Rest endpoints
 *
 * @package  Disciple_Tools
 * @category Plugin
 * @since    0.1
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Network_Dashboard_UI_Endpoints
 */
class DT_Network_Dashboard_UI_Endpoints
{

    private $version = 1;
    private $namespace;
    private $public_namespace;

    /**
     * DT_Network_Dashboard_UI_Endpoints The single instance of DT_Network_Dashboard_UI_Endpoints.
     *
     * @var     object
     * @access    private
     * @since     0.1.0
     */
    private static $_instance = null;
    /**
     * Main DT_Network_Dashboard_UI_Endpoints Instance
     * Ensures only one instance of DT_Network_Dashboard_UI_Endpoints is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return DT_Network_Dashboard_UI_Endpoints instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {
        $this->namespace = "dt/v" . intval( $this->version );
        $this->public_namespace = "dt-public/v" . intval( $this->version );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    } // End __construct()

    public function add_api_routes() {
        register_rest_route(
            $this->namespace,
            '/network/ui/trigger_transfer',
            [
                'methods'  => 'POST',
                'callback' => [ $this, 'trigger_transfer' ],
            ]
        );
        register_rest_route(
            $this->namespace,
            '/network/ui/get_snapshot',
            [
                'methods'  => 'POST',
                'callback' => [ $this, 'get_snapshot' ],
            ]
        );
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public function trigger_transfer( WP_REST_Request $request ) {

        if ( ! user_can( get_current_user_id(), 'network_dashboard_viewer' ) ) {
            return new WP_Error( __METHOD__, 'Permission error.' );
        }

        $params = $request->get_params();
        if ( isset( $params['id'] ) && isset( $params['type'] ) ) {
            return DT_Network_Dashboard_Reports::trigger_transfer( $params['id'], $params['type'] );
        } else {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }
    }

    public function get_snapshot( WP_REST_Request $request ) {
        if ( ! user_can( get_current_user_id(), 'network_dashboard_viewer' ) ) {
            return new WP_Error( __METHOD__, 'Permission error.' );
        }

        $params = $request->get_params();
        if ( isset( $params['id'] ) ) {
            return get_post_meta( $params['id'], 'snapshot', true );
        } else {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }
    }


}
DT_Network_Dashboard_UI_Endpoints::instance();




class DT_Network_Dashboard_UI
{
    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {

        if ( current_user_can( 'view_any_contacts' ) || current_user_can( 'view_project_metrics' ) ) {

            add_action( 'dt_top_nav_desktop', [ $this, 'top_nav_desktop' ] );

            if ( isset( $_SERVER["SERVER_NAME"] ) ) {
                $url  = ( !isset( $_SERVER["HTTPS"] ) || @( $_SERVER["HTTPS"] != 'on' ) ) ? 'http://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) ) : 'https://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) );
                if ( isset( $_SERVER["REQUEST_URI"] ) ) {
                    $url .= sanitize_text_field( wp_unslash( $_SERVER["REQUEST_URI"] ) );
                }
            }
            $url_path = trim( str_replace( get_site_url(), "", $url ), '/' );

            if ( 'network' === substr( $url_path, '0', 7 ) ) {

                add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ] ); // add custom URL
                add_filter( 'dt_metrics_menu', [ $this, 'menu' ], 99 );

                if ( 'network' === $url_path ) {
                    add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
                }
            }
        } // end admin only test
    }

    /**
     * This filter adds a menu item to the metrics
     *
     * @param $content
     *
     * @return string
     */
    public function menu( $content ) {
        // home
        $content .= '<li><a href="'. site_url( '/network/' ) .'#network_home" onclick="show_network_home()">' .  esc_html__( 'Home' ) . '</a></li>';
        $content .= '<li><a href="'. site_url( '/network/' ) .'#sites" onclick="show_sites_list()">' .  esc_html__( 'Sites' ) . '</a></li>';
        $content .= '<li><a href="'. site_url( '/network/' ) .'#mapping_view" onclick="page_mapping_view()">' .  esc_html__( 'Maps' ) . '</a></li>';
        $content .= '<li><a href="'. site_url( '/network/' ) .'#mapping_list" onclick="page_mapping_list()">' .  esc_html__( 'Maps List' ) . '</a></li>';

        return $content;
    }

    /**
     * Load scripts for the plugin
     */
    public function scripts() {

        // self hosted or publically hosted amcharts scripts
        if ( get_option( 'dt_network_dashboard_local_amcharts' ) === true ) { // local hosted @todo add checkbox to admin area

            wp_enqueue_script( 'amcharts-core',
                trailingslashit( plugin_dir_url( __FILE__ ) ) . 'amcharts/dist/script/core.js',
                [],
                filemtime( plugin_dir_path( __FILE__ ) . 'amcharts4/dist/script/core.js' ),
            true );
            wp_enqueue_script( 'amcharts-charts',
                trailingslashit( plugin_dir_url( __FILE__ ) ) . 'amcharts/dist/script/charts.js',
                [
                    'amcharts-core'
                ],
                filemtime( plugin_dir_path( __FILE__ ) . 'amcharts4/dist/script/charts.js' ),
            true );
            wp_enqueue_script( 'amcharts-maps',
                trailingslashit( plugin_dir_url( __FILE__ ) ) . 'amcharts/dist/script/maps.js',
                [
                    'amcharts-core'
                ],
                filemtime( plugin_dir_path( __FILE__ ) . 'amcharts4/dist/script/maps.js' ),
            true );
            wp_enqueue_script( 'amcharts-worldlow',
                trailingslashit( plugin_dir_url( __FILE__ ) ) . 'amcharts-geodata/dist/script/worldlow.js',
                [
                    'amcharts-core'
                ],
                filemtime( plugin_dir_path( __FILE__ ) . 'geodata/dist/script/worldlow.js' ),
            true );
            wp_enqueue_script( 'amcharts-animated',
                trailingslashit( plugin_dir_url( __FILE__ ) ) . 'amcharts/dist/script/themes/animated.js',
                [
                    'amcharts-core'
                ],
                filemtime( plugin_dir_path( __FILE__ ) . 'amcharts4/dist/script/themes/animated.js' ),
            true );

        } else { // cdn hosted files

            wp_register_script( 'amcharts-core', 'https://www.amcharts.com/lib/4/core.js', false, '4' );
            wp_register_script( 'amcharts-charts', 'https://www.amcharts.com/lib/4/charts.js', false, '4' );
            wp_register_script( 'amcharts-animated', 'https://www.amcharts.com/lib/4/themes/animated.js', false, '4' );
            wp_register_script( 'amcharts-maps', 'https://www.amcharts.com/lib/4/maps.js', false, '4' );
            wp_register_script( 'amcharts-maps-world', 'https://www.amcharts.com/lib/4/geodata/worldLow.js', false, '4' );

            wp_register_style( 'datatable-css', '//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css', false, '1.10' );
            wp_enqueue_style( 'datatable-css' );
            wp_register_script( 'datatable', '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js', false, '1.10' );
        }

        wp_enqueue_script( 'dt_network_dashboard_script',
            trailingslashit( plugin_dir_url( __FILE__ ) ) . 'ui.js',
            [
            'jquery',
            'jquery-ui-core',
            'amcharts-core',
            'amcharts-charts',
            'amcharts-animated',
            'amcharts-maps',
            'amcharts-maps-world',
            'datatable'
            ],
            filemtime( plugin_dir_path( __DIR__ ) . 'ui/ui.js' ),
        true );
        wp_localize_script(
            'dt_network_dashboard_script',
            'wpApiNetworkDashboard',
            [
                'root' => esc_url_raw( rest_url() ),
                'plugin_uri' => plugin_dir_url( __DIR__ ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'map_key' => dt_get_option( 'map_key' ),
                'spinner' => ' <img src="'. plugin_dir_url( __DIR__ ) . 'spinner.svg" width="12px" />',
                'spinner_large' => ' <img src="'. plugin_dir_url( __DIR__ ) . 'spinner.svg" width="24px" />',
                'sites_list' => $this->get_site_list(),
                'sites' => $this->get_sites(),
                'global' => $this->get_global(),
                'locations' => $this->get_locations(),
                'translations' => [
                    "sm_title" => __( "Network Dashboard", "dt_network_dashboard" ),
                    "title_site_list" => __( "Sites List", "dt_network_dashboard" ),
                    "title_locations" => __( "Locations", "dt_network_dashboard" ),
                ]
            ]
        );
    }



    public function add_url( $template_for_url ) {
        $template_for_url['network'] = 'template-metrics.php';
        return $template_for_url;
    }

    public function top_nav_desktop() {
        if ( current_user_can( 'view_any_contacts' ) || current_user_can( 'view_project_metrics' ) ) {
            ?><li><a href="<?php echo esc_url( site_url( '/network/' ) ); ?>"><?php esc_html_e( "Network" ); ?></a></li><?php
        }
    }

    public function get_sites() {

        if ( wp_cache_get( 'get_sites' ) ) {
            return wp_cache_get( 'get_sites' );
        }

        $sites = dt_network_dashboard_queries( 'sites_with_snapshots' );

        $new = [];
        if ( ! empty( $sites ) ) {
            foreach ( $sites as $site ) {
                $snapshot = maybe_unserialize( $site['snapshot'] );
                if ( ! empty( $snapshot['partner_id'] ) ) {
                    $new[$snapshot['partner_id']] = $snapshot;
                }
            }
        }

        if ( dt_is_current_multisite_dashboard_approved() ) {
            $sites = dt_multisite_dashboard_snapshots();
            foreach ( $sites as $key => $site ) {
                $snapshot = maybe_unserialize( $site );
                if ( ! empty( $snapshot['partner_id'] ) ) {
                    $new[ $snapshot['partner_id'] ] = $snapshot;
                }
            }
        }

        wp_cache_set( 'get_sites', $new );

        return $new;
    }

    public function get_site_list() {
        $sites = dt_network_dashboard_queries( 'sites_with_snapshots' );

        $new = [];
        if ( ! empty( $sites ) ) {
            foreach ( $sites as $key => $site ) {
                $snapshot = maybe_unserialize( $site['snapshot'] );
                if ( ! empty( $snapshot['partner_id'] ) ) {
                    $new[] = [
                        'id' => $snapshot['partner_id'],
                        'name' => $snapshot['profile']['partner_name'],
                        'contacts' => $snapshot['contacts']['current_state']['status']['active'],
                        'groups' => $snapshot['groups']['current_state']['total_active'],
                        'users' => $snapshot['users']['current_state']['total_users'],
                        'date' => date( 'Y-m-d H:i:s', $snapshot['date'] ),
                    ];
                }
            }
        }

        if ( dt_is_current_multisite_dashboard_approved() ) {
            $sites = dt_multisite_dashboard_snapshots();
            foreach ( $sites as $key => $site ) {
                $snapshot = maybe_unserialize( $site );
                if ( ! empty( $snapshot['partner_id'] ) ) {
                    $new[] = [
                        'id' => $snapshot['partner_id'],
                        'name' => $snapshot['profile']['partner_name'],
                        'contacts' => $snapshot['contacts']['current_state']['status']['active'],
                        'groups' => $snapshot['groups']['current_state']['total_active'],
                        'users' => $snapshot['users']['current_state']['total_users'],
                        'date' => date( 'Y-m-d H:i:s', $snapshot['date'] ),
                    ];
                }
            }
        }

        return $new;
    }

    public function get_global() {
        $totals = $this->compile_totals();
        $data = [
            'contacts' => [
                'total' => $totals['total_contacts'],
                'added' => [
                    'sixty_days' => $this->compile_by_days( 'contacts' ),
                    'twenty_four_months' => $this->compile_by_months( 'contacts' ),
                ],
            ],
            'groups' => [
                'total' => $totals['total_groups'],
                'added' => [
                    'sixty_days' => $this->compile_by_days( 'groups' ),
                    'twenty_four_months' => $this->compile_by_months( 'groups' ),
                ],
            ],
            'users' => [
                'total' => $totals['total_users'],
            ],
            'locations' => [
                'total_countries' => $totals['total_countries'],
            ],
        ];

        return $data;
    }

    /**
     * Gets an array of the last number of days.
     *
     * @param int $number_of_days
     *
     * @return array
     */
    public function get_day_list( $number_of_days = 60 ) {
        $d = [];
        for ($i = 0; $i < $number_of_days; $i++) {
            $d[date( "Y-m-d", strtotime( '-'. $i .' days' ) )] = [
                'date' => date( "Y-m-d", strtotime( '-'. $i .' days' ) ),
                'value' => 0,
            ];
        }
        return $d;
    }

    /**
     * Gets an array of last 25 months.
     *
     * @note 25 months allows you to get 3 years to compare of this month.
     *
     * @param int $number_of_months
     *
     * @return array
     */
    public function get_month_list( $number_of_months = 25 ) {
        $d = [];
        for ($i = 0; $i < $number_of_months; $i++) {
            $d[date( "Y-m", strtotime( '-'. $i .' months' ) ) . '-01'] = [
                'date' => date( "Y-m", strtotime( '-'. $i .' months' ) ) . '-01',
                'value' => 0,
            ];
        }
        return $d;
    }

    public function compile_by_days( $type ) {
        $dates1 = $this->get_day_list( 60 );
        $dates2 = [];

        $sites = $this->get_sites();
        if ( empty( $sites ) ) {
            return [];
        }

        // extract days
        foreach ( $sites as $key => $site ) {
            foreach ( $site[$type]['added']['sixty_days'] as $day ) {
                if ( isset( $dates1[$day['date']]['value'] ) && $day['value'] ) {
                    $dates1[$day['date']]['value'] = $dates1[$day['date']]['value'] + $day['value'];
                }
            }
        }

        arsort( $dates1 );

        foreach ( $dates1 as $d ) {
            $dates2[] = $d;
        }

        return $dates2;
    }

    public function compile_by_months( $type ) {
        $dates1 = $this->get_month_list( 25 );
        $dates2 = [];

        $sites = $this->get_sites();
        if ( empty( $sites ) ) {
            return [];
        }

        // extract months
        foreach ( $sites as $key => $site ) {
            foreach ( $site[$type]['added']['twenty_four_months'] as $day ) {
                if ( isset( $dates1[$day['date']]['value'] ) && $day['value'] ) {
                    $dates1[$day['date']]['value'] = $dates1[$day['date']]['value'] + $day['value'];
                }
            }
        }

        arsort( $dates1 );

        foreach ( $dates1 as $d ) {
            $dates2[] = $d;
        }

        return $dates2;
    }

    public function compile_totals() {
        $sites = $this->get_sites();
        $data = [
            'total_contacts' => 0,
            'total_groups' => 0,
            'total_users' => 0,
            'total_countries' => 0, // @todo needs real data
        ];
        if ( empty( $sites ) ) {
            return [];
        }

        // extract months
        foreach ( $sites as $key => $site ) {
            $data['total_contacts'] = $data['total_contacts'] + $site['contacts']['current_state']['status']['active'];
            $data['total_groups'] = $data['total_groups'] + $site['groups']['current_state']['total_active'];
            $data['total_users'] = $data['total_users'] + $site['users']['current_state']['total_users'];
        }

        return $data;
    }



    public function get_locations() { // @todo sample data for site based summaries
        $data = [];
        for ($i = 100; $i <= 105; $i++) {
            $data[] = $this->location( $i );
        }
        return $data;
    }

    public function location( $id ) {

        $data = [
            'countries' => [
                [
                    'id' => 'TN',
                    'name' => 'Tunisia',
                    'site_name' => '7656789876',
                    'contacts' => rand( 300, 1000 ),
                    'groups' => rand( 300, 1000 ),
                    'value' => 100,
                    'color' => 'red'
                ]
            ],
            'current_state' => [
                'active_locations' => rand( 300, 1000 ),
                'inactive_locations' => rand( 300, 1000 ),
                'all_locations' => rand( 300, 1000 ),
            ],
            'list' => [
                [
                    'location_name' => '',
                    'location_id' => '',
                    'parent_id' => '',
                    'geonameid' => '',
                    'longitude' => '',
                    'latitude' => '',
                    'total_contacts' => 0,
                    'total_groups' => 0,
                    'total_users' => 0,
                    'new_contacts' => 0,
                    'new_groups' => 0,
                    'new_users' => 0,
                ],
                [
                    'location_name' => '',
                    'location_id' => '',
                    'parent_id' => '',
                    'geonameid' => '',
                    'longitude' => '',
                    'latitude' => '',
                    'total_contacts' => 0,
                    'total_groups' => 0,
                    'total_users' => 0,
                    'new_contacts' => 0,
                    'new_groups' => 0,
                    'new_users' => 0,
                ],
                [
                    'location_name' => '',
                    'location_id' => '',
                    'parent_id' => '',
                    'geonameid' => '',
                    'longitude' => '',
                    'latitude' => '',
                    'total_contacts' => 0,
                    'total_groups' => 0,
                    'total_users' => 0,
                    'new_contacts' => 0,
                    'new_groups' => 0,
                    'new_users' => 0,
                ],
            ],
        ];
        return $data;
    } // @todo sample data



}
DT_Network_Dashboard_UI::instance();