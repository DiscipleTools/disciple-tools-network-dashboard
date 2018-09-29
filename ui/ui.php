<?php

/**
 * Class DT_Zume_Hooks
 */
class DT_Network_Dashboard_Hooks
{

    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Build hook classes
     */
    public function __construct() {
        new DT_Network_Dashboard_UI();
    }
}
DT_Network_Dashboard_Hooks::instance();

/**
 * Empty class for now..
 * Class DT_Zume_Hook_Base
 */
abstract class DT_Network_Dashboard_Base
{
    public function __construct() {
    }
}

class DT_Network_Dashboard_UI extends DT_Network_Dashboard_Base
{
    /**
     * This filter adds a menu item to the metrics
     *
     * @param $content
     *
     * @return string
     */
    public function menu( $content ) {
        $content .= '
              <li><a href="'. site_url( '/network/' ) .'#network_dashboard_overview" onclick="show_network_dashboard_overview()">' .  esc_html__( 'Overview' ) . '</a></li>
              <li><a href="'. site_url( '/network/' ) .'#network_tree" onclick="show_network_tree()">' .  esc_html__( 'Tree' ) . '</a></li>
              <li><a href="'. site_url( '/network/' ) .'#network_map" onclick="show_network_map()">' .  esc_html__( 'Map' ) . '</a></li>
              <li><a href="'. site_url( '/network/' ) .'#network_side_tree" onclick="show_network_side_tree()">' .  esc_html__( 'Side Tree' ) . '</a></li>
              <li><a href="'. site_url( '/network/' ) .'#report_sync" onclick="show_report_sync()">' .  esc_html__( 'Report Sync' ) . '</a></li>';
        return $content;
    }

    /**
     * Load scripts for the plugin
     */
    public function scripts() {
        wp_enqueue_script( 'dt_network_dashboard_script', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'ui.js', [
            'jquery',
            'jquery-ui-core',
        ], filemtime( plugin_dir_path( __DIR__ ) . 'ui/ui.js' ), true );
        wp_enqueue_script( 'jquery-ui-autocomplete' );

        wp_localize_script(
            'dt_network_dashboard_script', 'wpApiNetworkDashboard', [
                'root' => esc_url_raw( rest_url() ),
                'plugin_uri' => plugin_dir_url( __DIR__ ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'map_key' => dt_get_option( 'map_key' ),
                'spinner' => '<img src="'. plugin_dir_url( __FILE__ ) . '/spinner.svg" width="12px" />',
                'spinner_large' => '<img src="'. plugin_dir_url( __FILE__ ) . '/spinner.svg" width="24px" />',
                'stats' => [
                    'table' => DT_Network_Dashboard_UI::get_location_table(),
                    'tree' => DT_Network_Dashboard_UI::get_location_tree(),
                    'map' => DT_Network_Dashboard_UI::get_location_map(),
                    'side_tree' => DT_Network_Dashboard_UI::get_location_side_tree(),
                    'level_tree' => DT_Network_Dashboard_UI::get_locations_level_tree(),
                    'report_sync' => DT_Network_Dashboard_UI::get_site_link_list(),
                ],
                'translations' => [
                    "sm_title" => __( "Network Dashboard", "dt_zume" ),
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

    // Enqueue maps and charts for standard metrics
    public function enqueue_google() {
        /* phpcs:ignore WordPress.WP.EnqueuedResourceParameters */
        wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', [], false );
        /* phpcs:ignore WordPress.WP.EnqueuedResourceParameters */
        wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . dt_get_option( 'map_key' ), array(), null, true );
    }

    public function __construct() {

        if ( current_user_can( 'view_any_contacts' ) || current_user_can( 'view_project_metrics' ) ) {

            add_action( 'dt_top_nav_desktop', [ $this, 'top_nav_desktop' ] );
            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_google' ], 10 );

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

    public static function get_location_tree() {
        $table_data = self::query_location_population_groups();

        $chart = [];
        foreach ( $table_data as $row ) {
            if ( (int) $row['groups_needed'] < 1 ) {
                $row['groups_needed'] = 0;
            }
            $chart[] = [
                [
                    'v' => $row['location'],
                    'f' => $row['location'] . '<br>pop: ' . $row['gn_population'] . '<br>need: ' . $row['groups_needed']
                ],
                $row['parent_name'],
                ''
            ];
        }

        return $chart;
    }

    public static function get_location_table() {
        $table_data = self::query_location_population_groups();

        $chart = [];
        foreach ( $table_data as $row ) {
            if ( (int) $row['groups_needed'] < 1 ) {
                $row['groups_needed'] = 0;
            }
            $chart[] = [ $row['location'], (int) $row['gn_population'], (int) $row['groups_needed'], (int) $row['groups'] ];
        }

        return $chart;
    }

    public static function get_location_map() {
        $table_data = self::query_location_latlng();

        $chart = [];
        $chart[] = [ 'Lat', 'Long', 'Name' ];
        foreach ( $table_data as $row ) {
            if ( ! empty( $row['latitude'] ) && ! empty( $row['longitude'] ) ) {
                $chart[] = [
                    (float) $row['latitude'],
                    (float) $row['longitude'],
                    $row['location']
                ];
            }
        }

        return $chart;
    }

    public static function get_location_side_tree() {
        $table_data = self::query_location_population_groups();

        $chart = [];
        $chart[] = [ 'id', 'childLabel', 'parent', 'size', [ 'role' => 'style' ] ];
        foreach ( $table_data as $row ) {
            if ( $row['parent_id'] == 0 ) {
                $row['parent_id'] = -1;
            }
            $chart[] = [ (int) $row['id'], $row['location'], (int) $row['parent_id'], 1, 'black' ];
        }

        return $chart;
    }

    public static function query_geoname_list() {
        global $wpdb;
        return $wpdb->get_col("SELECT CONCAT( name, ', ', country_code) FROM dt_geonames" );
    }

    public static function query_location_population_groups() {
        global $wpdb;

        $results = $wpdb->get_results("
            SELECT 
            t1.ID as id, 
            t1.post_parent as parent_id, 
            t1.post_title as location,
            (SELECT post_title FROM $wpdb->posts WHERE ID = t1.post_parent) as parent_name,
            t2.meta_value as gn_population, 
            ROUND(t2.meta_value / (SELECT option_value FROM $wpdb->options WHERE option_name = 'dt_network_dashboard_population'), 0 ) as groups_needed,
            (SELECT count(*) FROM $wpdb->p2p WHERE p2p_to = t1.ID) as groups
            FROM $wpdb->posts as t1
            LEFT JOIN $wpdb->postmeta as t2
            ON t1.ID=t2.post_id
            AND t2.meta_key = 'gn_population'
            WHERE post_type = 'locations' AND post_status = 'publish'
        ", ARRAY_A );

        return $results;
    }

    public static function query_location_latlng() {
        global $wpdb;

        $results = $wpdb->get_results("
            SELECT 
            t2.meta_value as latitude,
            t3.meta_value as longitude,
            t1.post_title as location
            FROM $wpdb->posts as t1
            LEFT JOIN $wpdb->postmeta as t2
            ON t1.ID=t2.post_id
            AND t2.meta_key = 'gn_latitude'
            LEFT JOIN $wpdb->postmeta as t3
            ON t1.ID=t3.post_id
            AND t3.meta_key = 'gn_longitude'
            WHERE post_type = 'locations' 
            AND post_status = 'publish'
            AND post_parent != '0'
        ", ARRAY_A );

        return $results;
    }

    public static function get_locations_level_tree() {
        global $wpdb;
        $query = $wpdb->get_results("
                    SELECT 
                    t1.ID as id, 
                    t1.post_parent as parent_id, 
                    t1.post_title as name,
                    t2.meta_value as gn_population, 
                    ROUND(t2.meta_value / (SELECT option_value FROM $wpdb->options WHERE option_name = 'dt_network_dashboard_population'), 0 ) as groups_needed,
                    (SELECT count(*) FROM $wpdb->p2p WHERE p2p_to = t1.ID) as groups
                    FROM $wpdb->posts as t1
                    LEFT JOIN $wpdb->postmeta as t2
                    ON t1.ID=t2.post_id
                    AND t2.meta_key = 'gn_population'
                    WHERE post_type = 'locations' AND post_status = 'publish'
                ", ARRAY_A );
        // prepare special array with parent-child relations
        $menu_data = array(
            'items' => array(),
            'parents' => array()
        );
        foreach ( $query as $menuItem )
        {
            $menu_data['items'][$menuItem['id']] = $menuItem;
            $menu_data['parents'][$menuItem['parent_id']][] = $menuItem['id'];
        }

        function build_menu( $parent_id, $menu_data, $gen) {
            $html = '';

            if (isset( $menu_data['parents'][$parent_id] ))
            {
                $html = '<ul class="gen-ul ul-gen-'.$gen.'">';
                $gen++;
                foreach ($menu_data['parents'][$parent_id] as $item_id)
                {
                    $html .= '<li class="gen-li li-gen-'.$gen.'">';
                    //            $html .= '(level: '.$gen.')<br> ';
                    $html .= '<strong>'. $menu_data['items'][$item_id]['name'] . '</strong><br>';
                    $html .= 'population: '. ( $menu_data['items'][$item_id]['gn_population'] ?: '0' ) . '<br>';
                    $html .= 'groups needed: '. ( $menu_data['items'][$item_id]['groups_needed'] ?: '0' ) . '<br>';
                    $html .= 'groups: '. $menu_data['items'][$item_id]['groups'];

                    $html .= '</li>';

                    // find childitems recursively
                    $html .= build_menu( $item_id, $menu_data, $gen );
                }
                $html .= '</ul>';
            }

            return $html;
        }

        $list = '<style>
                    .gen-ul {
                        list-style: none;
                        padding-left:30px;
                    }
                    .gen-li {
                        padding: 25px;
                        border: 1px solid grey;
                        margin-top: 10px;
                        width: 20%;
                        background: yellowgreen;
                        border-radius:10px;
                    }
                </style>';

        $list .= build_menu( 0, $menu_data, -1 );

        return $list;
    }

    public static function get_site_link_list() {
        global $wpdb;
        $list = $wpdb->get_results("
            SELECT post_title, ID as id
            FROM $wpdb->posts
            WHERE post_type = 'site_link_system' 
                AND post_status = 'publish'
        ", ARRAY_A );

        return $list;
    }
}