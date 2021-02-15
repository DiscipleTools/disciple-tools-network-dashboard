<?php

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class DT_Network_Dashboard_Metrics_Base {

    public $url_path;
    public $url;
    public $key;
    public $namespace = 'dt/v1';
    public $root_slug = 'network';
    public $base_slug = 'example'; //lowercase
    public $slug = '';
    public $base_title = "Example Title";
    public $title = '';
    public $menu_title = 'Example';
    public $js_object_name = ''; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = ''; // should be full file name plus extension
    public $permissions = [ 'dt_all_access_contacts', 'view_project_metrics', 'view_network_dashboard' ];
    public static $activity_filter;

    private static $_instance = null;
    public static function instance() {
        if (is_null( self::$_instance )) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        if ( empty( $this->url_path ) ){
            $this->url_path = dt_get_url_path();
        }
    }

    public function has_permission(){
        return dt_network_dashboard_has_metrics_permissions();
    }

    public function filter_mapping_module_data( $data) {
        $data['custom_column_labels'] = $this->location_data_types();
        return $data;
    }

    public function add_url( $template_for_url) {
        $template_for_url['network'] = 'template-metrics.php';
        return $template_for_url;
    }

    public function url_redirect() {
        $url = dt_get_url_path();
        $plugin_dir = get_stylesheet_directory();
        if ( strpos( $url, "network" ) !== false ){
            $path = $plugin_dir . '/template-metrics.php';
            include( $path );
            die();
        }
    }

    public function menu( $content) {
        return $content;
    }

    public function base_add_api_routes() {
        register_rest_route(
            $this->namespace,
            '/network/base/',
            [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'base_endpoint' ],
                ],
            ]
        );
        register_rest_route(
            $this->namespace,
            '/network/base/',
            [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'base_endpoint' ],
                ],
            ]
        );
    }

    public function base_endpoint( WP_REST_Request $request ){
        if ( !$this->has_permission() ) {
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }
        $params = $request->get_params();

        switch ( $params['type'] ) {
            case 'sites':
                $data = $this->get_sites();
                break;
            case 'sites_list':
                $data = $this->get_site_list();
                break;
            case 'locations_list':
                $data = $this->get_locations_list();
                break;
            case 'global':
                $data['sites'] = $this->get_sites();
                $data['global'] = $this->get_global();
                break;
            case 'activity':
                if ( isset( $params['filters'] ) && ! empty( $params['filters'] ) ){
                    $filters = dt_recursive_sanitize_array( $params['filters'] );
                    $data = self::build_log( $filters );
                } else {
                    $data = self::build_log();
                }
                break;

            case 'activity_stats':
                if ( isset( $params['filters'] ) && ! empty( $params['filters'] ) ){
                    $filters = dt_recursive_sanitize_array( $params['filters'] );
                    $data = self::get_activity_stats( $filters );
                } else {
                    $data = self::get_activity_stats();
                }

                break;
            case 'reset':
                $data['sites'] = $this->get_sites( true );
                ;
                $data['global'] = $this->get_global( true );
                $data['sites_list'] = $this->get_site_list( true );
                $data['locations_list'] = $this->get_locations_list( true );
                break;
            case 'all':
            default:
                $data['sites'] = $this->get_sites();
                ;
                $data['global'] = $this->get_global();
                $data['sites_list'] = $this->get_site_list();
                $data['locations_list'] = $this->get_locations_list();
                break;
        }

        return $data;
    }

    public static function reset_ui_caches(){
        // reset ui transient caches with new parameters
        self::get_sites( true );
        self::get_global( true );
        self::get_site_list( true );
        self::get_locations_list( true );
    }

    public function base_scripts() {
        wp_enqueue_script( 'network_base_script',
            plugin_dir_url( __FILE__ ) . 'base.js',
            [
                'jquery',
                'amcharts-core',
                'amcharts-charts',
                'amcharts-animated',
                'amcharts-maps',
                'datatable',
                'lodash'
            ],
            filemtime( plugin_dir_path( __FILE__ ) . 'base.js' ),
        true );

        // mapbox
        if ( DT_Mapbox_API::get_key() ){
            DT_Mapbox_API::load_mapbox_header_scripts();
        }
        wp_localize_script(
            'network_base_script',
            'network_base_script',
            [
                'map_key' => DT_Mapbox_API::get_key(),
                'trans' => $this->translations(),
            ]
        );

        // amcharts
        wp_register_script( 'amcharts-core', 'https://www.amcharts.com/lib/4/core.js', false, '4' );
        wp_register_script( 'amcharts-charts', 'https://www.amcharts.com/lib/4/charts.js', false, '4' );
        wp_register_script( 'amcharts-animated', 'https://www.amcharts.com/lib/4/themes/animated.js', false, '4' );
        wp_register_script( 'amcharts-maps', 'https://www.amcharts.com/lib/4/maps.js', false, '4' );

        // Datatable
        wp_register_style( 'datatable-css', '//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css' );
        wp_enqueue_style( 'datatable-css' );
        wp_register_script( 'datatable', '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js', false, '1.10' );
    }

    public function load_grid_mapping_scripts(){
        DT_Mapping_Module::instance()->drilldown_script();
        DT_Mapping_Module::instance()->scripts();
        wp_enqueue_script( 'mapping-drill-down', get_template_directory_uri() . '/dt-mapping/drill-down.js', [ 'jquery', 'lodash' ], '1.1' );
        wp_localize_script(
            'mapping-drill-down',
            'mappingModule',
            array(
                'mapping_module' => $this->localize_script(),
            )
        );
    }

    public function localize_script() {
        if ( ! class_exists( 'DT_Mapping_Module' ) ) {
            require_once( get_template_directory() . 'dt-mapping/mapping.php' );
        }
        $mapping_module = DT_Mapping_Module::instance()->localize_script();

        if ( dt_network_dashboard_denied() ) {
            return [];
        } else {
            return $mapping_module;
        }
    }

    public function translations() {
        return [
            'base_1' => __( 'ID', 'disciple-tools-network-dashboard' ),
            'base_2' => __( 'Site', 'disciple-tools-network-dashboard' ),
            'base_3' => __( 'Contacts', 'disciple-tools-network-dashboard' ),
            'base_4' => __( 'Groups', 'disciple-tools-network-dashboard' ),
            'base_5' => __( 'Users', 'disciple-tools-network-dashboard' ),
            'base_6' => __( 'Timestamp', 'disciple-tools-network-dashboard' ),
            'base_7' => __( 'Visit', 'disciple-tools-network-dashboard' ),
            'base_8' => __( 'View', 'disciple-tools-network-dashboard' ),

            'site_1' => __( 'Active Snapshot', 'disciple-tools-network-dashboard' ),
            'site_2' => __( 'Generations', 'disciple-tools-network-dashboard' ),
            'site_3' => __( 'Follow-up Funnel', 'disciple-tools-network-dashboard' ),
            'site_4' => __( 'On-Going Meetings', 'disciple-tools-network-dashboard' ),
            'site_5' => __( 'Coaching', 'disciple-tools-network-dashboard' ),
            'site_6' => __( 'Baptized', 'disciple-tools-network-dashboard' ),
            'site_7' => __( 'Active Groups', 'disciple-tools-network-dashboard' ),
            'site_8' => __( 'Church', 'disciple-tools-network-dashboard' ),
            'site_9' => __( 'Health Metrics', 'disciple-tools-network-dashboard' ),
            'site_10' => __( 'Total Users', 'disciple-tools-network-dashboard' ),
            'site_11' => __( 'Responders', 'disciple-tools-network-dashboard' ),
            'site_12' => __( 'Dispatchers', 'disciple-tools-network-dashboard' ),
            'site_13' => __( 'Multipliers', 'disciple-tools-network-dashboard' ),
            'site_14' => __( 'Admins', 'disciple-tools-network-dashboard' ),
            'site_15' => __( 'User Login Activity', 'disciple-tools-network-dashboard' ),
            'site_16' => __( 'Users Active in the Last 30 Days', 'disciple-tools-network-dashboard' ),
            'site_17' => __( 'Practicing', 'disciple-tools-network-dashboard' ),
            'site_18' => __( 'Not Practicing', 'disciple-tools-network-dashboard' ),
            'site_19' => __( 'Chart Funnel', 'disciple-tools-network-dashboard' ),

            'contacts' => __( 'Contacts', 'disciple-tools-network-dashboard' ),
            'contact' => __( 'Contact', 'disciple-tools-network-dashboard' ),
            'group' => __( 'Group', 'disciple-tools-network-dashboard' ),
            'groups' => __( 'Groups', 'disciple-tools-network-dashboard' ),
            'user' => __( 'User', 'disciple-tools-network-dashboard' ),
            'users' => __( 'Users', 'disciple-tools-network-dashboard' ),
            'site' => __( 'Site', 'disciple-tools-network-dashboard' ),
            'sites' => __( 'Sites', 'disciple-tools-network-dashboard' ),
            'baptisms' => __( 'Baptisms', 'disciple-tools-network-dashboard' ),
            'church' => __( 'Church', 'disciple-tools-network-dashboard' ),
            'churches' => __( 'Churches', 'disciple-tools-network-dashboard' ),
            'location' => __( 'Location', 'disciple-tools-network-dashboard' ),
            'types' => __( 'Types', 'disciple-tools-network-dashboard' ),

            'modify_filter' => __( 'modify filter', 'disciple-tools-network-dashboard' ),
            'filter_list' => __( 'Filter List', 'disciple-tools-network-dashboard' ),
            'reset_data' => __( 'reset data', 'disciple-tools-network-dashboard' ),

            'status' => __( 'Status', 'disciple-tools-network-dashboard' ),
            'status_all' => __( 'Status - All', 'disciple-tools-network-dashboard' ),

            'zoom_level' => __( 'Zoom Level', 'disciple-tools-network-dashboard' ),
            'auto_zoom' => __( 'Auto Zoom', 'disciple-tools-network-dashboard' ),
            'world' => __( 'World', 'disciple-tools-network-dashboard' ),
            'country' => __( 'Country', 'disciple-tools-network-dashboard' ),
            'state' => __( 'State', 'disciple-tools-network-dashboard' ),

            'active_contacts' => __( 'Active Contacts', 'disciple-tools-network-dashboard' ),
            'paused_contacts' => __( 'Paused Contacts', 'disciple-tools-network-dashboard' ),
            'closed_contacts' => __( 'Closed Contacts', 'disciple-tools-network-dashboard' ),
            'total_contacts' => __( 'Total Contacts', 'disciple-tools-network-dashboard' ),
            'new_contacts' => __( 'New Contacts', 'disciple-tools-network-dashboard' ),

            'new_baptisms' => __( 'New Baptisms', 'disciple-tools-network-dashboard' ),
            'baptism_generations' => __( 'Baptism Generations', 'disciple-tools-network-dashboard' ),
            'select_location' => __( 'Select Location', 'disciple-tools-network-dashboard' ),

            'new_groups' => __( 'New Groups', 'disciple-tools-network-dashboard' ),
            'pre_group' => __( 'Pre-Group', 'disciple-tools-network-dashboard' ),
            'pre_groups' => __( 'Pre-Groups', 'disciple-tools-network-dashboard' ),

            'last_7_days' => __( 'Last 7 Days', 'disciple-tools-network-dashboard' ),
            'last_30_days' => __( 'Last 30 Days', 'disciple-tools-network-dashboard' ),
            'last_60_days' => __( 'Last 60 Days', 'disciple-tools-network-dashboard' ),
            'last_12_months' => __( 'Last 12 Months', 'disciple-tools-network-dashboard' ),
            'last_24_months' => __( 'Last 24 Months', 'disciple-tools-network-dashboard' ),
            'this_year' => __( 'This Year', 'disciple-tools-network-dashboard' ),

            'activity_1' => __( 'Activity Map', 'disciple-tools-network-dashboard' ),
            'activity_2' => __( 'Stats', 'disciple-tools-network-dashboard' ),
            'activity_3' => __( 'Activities', 'disciple-tools-network-dashboard' ),
            'activity_4' => __( 'Actions', 'disciple-tools-network-dashboard' ),
            'activity_5' => __( 'Sites', 'disciple-tools-network-dashboard' ),
            'activity_6' => __( 'By Action Type', 'disciple-tools-network-dashboard' ),
            'activity_7' => __( 'Key', 'disciple-tools-network-dashboard' ),
            'activity_8' => __( 'Name', 'disciple-tools-network-dashboard' ),
            'activity_9' => __( 'Total Activities', 'disciple-tools-network-dashboard' ),
            'activity_10' => __( 'Feed', 'disciple-tools-network-dashboard' ),
            'activity_11' => __( 'Time', 'disciple-tools-network-dashboard' ),
            'activity_12' => __( 'Result Limit', 'disciple-tools-network-dashboard' ),
            'activity_13' => __( 'uncheck', 'disciple-tools-network-dashboard' ),
            'activity_14' => __( 'check', 'disciple-tools-network-dashboard' ),

            'home_1' => __( 'Home', 'disciple-tools-network-dashboard' ),
            'home_2' => __( 'Countries', 'disciple-tools-network-dashboard' ),
            'home_3' => __( 'Events (30 Days)', 'disciple-tools-network-dashboard' ),

            'stats_1' => __( 'All Active/Inactive', 'disciple-tools-network-dashboard' ),
            'map_1' => __( 'Hover Map', 'disciple-tools-network-dashboard' ),

            'results' => __( 'Results', 'disciple-tools-network-dashboard' ),
            'has_location' => __( 'Has location data', 'disciple-tools-network-dashboard' ),
            'no_location' => __( 'Without location data', 'disciple-tools-network-dashboard' ),
        ];
    }

    public static function get_sites( $reset = false ) {
        if ( $reset ){
            delete_transient( __METHOD__ );
            delete_transient( __METHOD__ . '_state' );
        }

        $live_global_hash_state = DT_Network_Dashboard_Site_Post_Type::global_time_hash();
        $stored_global_hash_state = get_transient( __METHOD__ . '_state' );

        if ( $live_global_hash_state === $stored_global_hash_state && ! empty( get_transient( __METHOD__ ) ) ){
            return get_transient( __METHOD__ );
        }

        $new = [];

        $sites = DT_Network_Dashboard_Site_Post_Type::all_sites();
        if ( !empty( $sites )) {
            foreach ($sites as $site) {
                if ( 'multisite' === $site['type'] ){
                    continue;
                }
                if ( 'hide' === $site['visibility'] ){
                    continue;
                }
                $snapshot = maybe_unserialize( $site['snapshot'] );
                if ( !empty( $snapshot['partner_id'] )) {
                    $new[$snapshot['partner_id']] = $snapshot;
                    $new[$snapshot['partner_id']]['partner_name'] = $site['name'];
                }
            }
        }

        if (dt_is_current_multisite_dashboard_approved()) {
            foreach ($sites as $key => $site) {
                if ( 'multisite' !== $site['type'] ){
                    continue;
                }
                if ( 'hide' === $site['visibility'] ){
                    continue;
                }
                $snapshot = maybe_unserialize( $site['snapshot'] );
                if ( !empty( $snapshot['partner_id'] )) {
                    $new[$snapshot['partner_id']] = $snapshot;
                }
            }
        }

        set_transient( __METHOD__, $new, 24 * HOUR_IN_SECONDS );
        set_transient( __METHOD__.'_state', $live_global_hash_state, 24 * HOUR_IN_SECONDS );

        return $new;
    }

    public static function get_site_list( $reset = false ) {
        if ( $reset ){
            delete_transient( __METHOD__ );
            delete_transient( __METHOD__ . '_state' );
        }

        $live_global_hash_state = DT_Network_Dashboard_Site_Post_Type::global_time_hash();
        $stored_global_hash_state = get_transient( __METHOD__ . '_state' );

        if ( $live_global_hash_state === $stored_global_hash_state && ! empty( get_transient( __METHOD__ ) ) ){
            return get_transient( __METHOD__ );
        }

        $sites = DT_Network_Dashboard_Site_Post_Type::all_sites();

        $new = [];
        if ( !empty( $sites )) {
            foreach ($sites as $key => $site) {
                if ( 'multisite' === $site['type'] ){
                    continue;
                }
                if ( 'hide' === $site['visibility'] ){
                    continue;
                }
                $snapshot = maybe_unserialize( $site['snapshot'] );
                if ( !empty( $snapshot['partner_id'] )) {
                    $new[] = [
                        'id' => $snapshot['partner_id'],
                        'name' => ucwords( $site['name'] ),
                        'contacts' => $snapshot['contacts']['current_state']['status']['active'],
                        'groups' => $snapshot['groups']['current_state']['total_active'],
                        'users' => $snapshot['users']['current_state']['total_users'],
                        'date' => gmdate( 'Y-m-d H:i:s', $snapshot['date'] ),
                    ];
                }
            }
        }

        if (dt_is_current_multisite_dashboard_approved()) {
            foreach ($sites as $key => $site) {
                if ( 'multisite' !== $site['type'] ){
                    continue;
                }
                if ( 'hide' === $site['visibility'] ){
                    continue;
                }
                $snapshot = maybe_unserialize( $site['snapshot'] );
                if ( !empty( $snapshot['partner_id'] )) {
                    $new[] = [
                        'id' => $snapshot['partner_id'],
                        'name' => ucwords( $snapshot['profile']['partner_name'] ),
                        'contacts' => $snapshot['contacts']['current_state']['status']['active'],
                        'groups' => $snapshot['groups']['current_state']['total_active'],
                        'users' => $snapshot['users']['current_state']['total_users'],
                        'date' => gmdate( 'Y-m-d H:i:s', $snapshot['date'] ),
                    ];
                }
            }
        }

        set_transient( __METHOD__, $new, 24 * HOUR_IN_SECONDS );
        set_transient( __METHOD__.'_state', $live_global_hash_state, 24 * HOUR_IN_SECONDS );

        return $new;
    }

    public static function get_global( $reset = false ) {
        if ( $reset ){
            delete_transient( __METHOD__ );
            delete_transient( __METHOD__ . '_state' );
        }

        $live_global_hash_state = DT_Network_Dashboard_Site_Post_Type::global_time_hash();
        $stored_global_hash_state = get_transient( __METHOD__ . '_state' );

        if ( $live_global_hash_state === $stored_global_hash_state && ! empty( get_transient( __METHOD__ ) ) ){
            return get_transient( __METHOD__ );
        }

        $totals = self::compile_totals();
        $data = [
            'contacts' => [
                'total' => $totals['total_contacts'] ?? 0,
                'status' => self::compile_contacts_status(),
                'added' => [
                    'sixty_days' => self::compile_by_days( 'contacts' ),
                    'twenty_four_months' => self::compile_by_months( 'contacts' ),
                ],
                'baptisms' => [
                    'added' => [
                        'sixty_days' => self::compile_by_days_baptisms(),
                        'twenty_four_months' => self::compile_by_months_baptisms(),
                    ],
                    'generations' => self::compile_generations_baptisms(),
                ],
            ],
            'groups' => [
                'total' => $totals['total_groups'] ?? 0,
                'added' => [
                    'sixty_days' => self::compile_by_days( 'groups' ),
                    'twenty_four_months' => self::compile_by_months( 'groups' ),
                ],
                'status' => self::compile_groups_status(),
                'church_generations' => self::compile_generations_church(),
                'group_generations' => self::compile_generations_group(),
            ],
            'users' => [
                'total' => $totals['total_users'] ?? 0,
                'current_state' => self::compile_user_types(),
                'login_activity' => [
                    'sixty_days' => self::compile_by_days_users(),
                    'twenty_four_months' => self::compile_by_months_users(),
                ],
                'last_thirty_day_engagement' => self::compile_logins_last_thirty_days(),
            ],
            'sites' => [
                'total' => $totals['total_sites'] ?? 0,
            ],
            'locations' => [
                'total_countries' => $totals['total_countries'] ?? 0,
            ],
            'activity' => [
                'total' => number_format( $totals['total_activities'] ?? 0 ),
            ],
        ];

        $data = apply_filters( 'dt_network_dashboard_global_data', $data );

        set_transient( __METHOD__, $data, 24 * HOUR_IN_SECONDS );
        set_transient( __METHOD__.'_state', $live_global_hash_state, 24 * HOUR_IN_SECONDS );

        return $data;
    }

    public static function get_locations_list( $reset = false ) {
        if ( $reset ){
            delete_transient( __METHOD__ );
            delete_transient( __METHOD__ . '_state' );
        }

        $live_global_hash_state = DT_Network_Dashboard_Site_Post_Type::global_time_hash();
        $stored_global_hash_state = get_transient( __METHOD__ . '_state' );

        if ( $live_global_hash_state === $stored_global_hash_state && ! empty( get_transient( __METHOD__ ) ) ){
            return get_transient( __METHOD__ );
        }

        $data_types = self::location_data_types();
        $data = [
            'custom_column_labels' => $data_types,
            'current_state' => [
                'active_countries' => 0,
                'active_admin0_grid_ids' => [],
                'active_admin1' => 0,
                'active_admin1_grid_ids' => [],
                'active_admin2' => 0,
                'active_admin2_grid_ids' => [],
            ],
            'list' => [],
        ];
        $sites = self::get_sites();

        if (empty( $sites )) {
            return [];
        }

        $custom_column_data = [];
        foreach ($sites as $id => $site) {
            foreach ($site['locations']['list'] as $grid_id => $stats) {
                if ( !isset( $custom_column_data[$grid_id] ) ) {
                    $custom_column_data[$grid_id] = [];
                    $i = 0;
                    $label_counts = count( $data_types );
                    while ($i <= $label_counts -1 ) {
                        $custom_column_data[$grid_id][$i] = 0;
                        $i++;
                    }
                }
                $custom_column_data[$grid_id][0] = (int) $custom_column_data[$grid_id][0] + (int) $stats['contacts'] ?? 0;
                $custom_column_data[$grid_id][1] = (int) $custom_column_data[$grid_id][1] + (int) $stats['groups'] ?? 0;
                $custom_column_data[$grid_id][2] = (int) $custom_column_data[$grid_id][2] + (int) $stats['churches'] ?? 0;
                $custom_column_data[$grid_id][3] = (int) $custom_column_data[$grid_id][3] + (int) $stats['users'] ?? 0;
            }
        }

        $data["custom_column_data"] = $custom_column_data;

        foreach ($sites as $id => $site) {

            // list
            foreach ($site['locations']['list'] as $grid_id => $stats) {
                if ( !isset( $data['list'][$grid_id] )) {
                    $data['list'][ $grid_id ] = [
                        "contacts" => 0,
                        "groups" => 0,
                        "churches" => 0,
                        "users" => 0
                    ];
                    $data['list'][$grid_id]['sites'] = $sites[$id]['profile']['partner_name'];
                } else {
                    $data['list'][$grid_id]['sites'] .= ', ' . $sites[$id]['profile']['partner_name'];
                }
                $data['list'][$grid_id]['contacts'] = (int) $data['list'][$grid_id]['contacts'] + (int) $stats['contacts'] ?? 0;
                $data['list'][$grid_id]['groups'] = (int) $data['list'][$grid_id]['groups'] + (int) $stats['groups'] ?? 0;
                $data['list'][$grid_id]['churches'] = (int) $data['list'][$grid_id]['churches'] + (int) $stats['churches'] ?? 0;
                $data['list'][$grid_id]['users'] = (int) $data['list'][$grid_id]['users'] + (int) $stats['users'] ?? 0;
                $data['list'][$grid_id][$id] = $sites[$id]['profile']['partner_name'];

            }

            // complete list
            $list_location_grids = array_keys( $data['list'] );
            $location_grid_properties = self::format_location_grid_types( Disciple_Tools_Mapping_Queries::get_by_grid_id_list( $list_location_grids, true ) );
            if ( !empty( $location_grid_properties )) {
                foreach ($location_grid_properties as $value) {
                    foreach ($value as $k => $v) {
                        $data['list'][$value['grid_id']][$k] = $v;
                    }
                }
            }
        }

        set_transient( __METHOD__, $data, 24 * HOUR_IN_SECONDS );
        set_transient( __METHOD__.'_state', $live_global_hash_state, 24 * HOUR_IN_SECONDS );

        return $data;
    }

    public static function get_activity_log( $filters = [] ){
        global $wpdb;
        $hash = hash( 'sha256', maybe_serialize( $filters ) );

        if (wp_cache_get( __METHOD__, $hash )) {
            return wp_cache_get( __METHOD__, $hash );
        }

        $sites = DT_Network_Dashboard_Site_Post_Type::all_visible_sites();
        $sites_id_list = [];
        foreach ( $sites as $site ){
            $sites_id_list[] = $site['partner_id'];
        }

        $defaults = [
            'start' => '-7 days',
            'end' => time(),
            'limit' => 2000,
            'offset' => 0,
            'boundary' => [], // n_lat, s_lat, e_lng, w_lng lnglat, sw lnglat
            'actions' => array_keys( dt_network_dashboard_registered_actions() ),
            'sites' => $sites_id_list,
        ];

        $filter = wp_parse_args( $filters, $defaults );
        $additional_where = '';

        /* process start time */
        if ( isset( $filters['start'] ) && ! empty( $filters['start'] ) ){
            if ( is_numeric( $filters['start'] ) ) {
                $filter['start'] = sanitize_text_field( wp_unslash( $filters['start'] ) );
            } else {
                $filter['start'] = strtotime( sanitize_text_field( wp_unslash( $filters['start'] ) ) );
            }
        }
        if ( empty( $filter['start'] ) || $filter['start'] > time() || $filter['start'] < strtotime( '30 years ago' ) ) {
            $filter['start'] = strtotime( sanitize_text_field( wp_unslash( '- 7 days' ) ) );
        }

        /* process end time */
        if ( isset( $filters['end'] ) && ! empty( $filters['end'] ) ){
            $filter['end'] = strtotime( sanitize_text_field( wp_unslash( $filters['end'] ) ) );
        }
        if ( empty( $filter['end'] ) || $filter['end'] < strtotime( '30 years ago' ) ) {
            $filter['end'] = time();
        }

        /**
         * Action and Sites are negative filters. If the value is included in the filter, it is excluded from the query.
         */
        /* process actions */
        if ( ! empty( $filter['actions'] ) && is_array( $filter['actions'] ) ) {
            $string = dt_array_to_sql( $filter['actions'] );
            $additional_where .= " AND action IN (".$string.")";
        }
        /* process sites */
        if ( ! empty( $filter['sites'] ) && is_array( $filter['sites'] ) ) {
            $string = dt_array_to_sql( $filter['sites'] );
            $additional_where .= " AND site_id IN (".$string.")";
        }

        /* process boundary */
        if ( ! empty( $filter['boundary'] ) && is_array( $filter['boundary'] ) ) {
            if ( isset( $filter['boundary']['n_lat'] )
              && isset( $filter['boundary']['s_lat'] )
              && isset( $filter['boundary']['e_lng'] )
              && isset( $filter['boundary']['w_lng'] )
            ) {
                $additional_where .= "
                AND lng < ".$filter['boundary']['e_lng']."
                AND lng > ".$filter['boundary']['w_lng']."
                AND lat > ".$filter['boundary']['s_lat']."
                AND lat < ".$filter['boundary']['n_lat']."
                ";
            }
        }

        /* handle local site */
        // @phpcs:disable
        $profile = dt_network_site_profile();
        $results = $wpdb->get_results( $wpdb->prepare( "
                SELECT ml.*,
                       DATE_FORMAT(FROM_UNIXTIME(ml.timestamp), '%%Y-%%c-%%e') AS day,
                       DATE_FORMAT(FROM_UNIXTIME(ml.timestamp), '%%H:%%i %%p') AS time,
                       CASE
                           WHEN pname.meta_value != '' THEN pname.meta_value
                           WHEN ml.site_id = %s THEN %s
                           ELSE ''
                       END as site_name
                FROM $wpdb->dt_movement_log as ml
                LEFT JOIN $wpdb->posts as pid ON pid.post_title=ml.site_id
                	AND pid.post_type = 'dt_network_dashboard'
                LEFT JOIN $wpdb->postmeta as pname ON pid.ID=pname.post_id
                	AND	pname.meta_key = 'name'
                LEFT JOIN $wpdb->postmeta as pvisibility ON pid.ID=pvisibility.post_id
                	AND	pvisibility.meta_key = 'visibility'
                WHERE ml.timestamp > %s
                  AND ml.timestamp < %s
                  AND ( pvisibility.meta_value != 'hide' || ml.site_id = %s )
                  $additional_where
                ORDER BY ml.timestamp DESC
                LIMIT %d
                OFFSET %d
                ",
            $profile['partner_id'],
            $profile['partner_name'],
            $filter['start'],
            $filter['end'],
            $profile['partner_id'],
            $filter['limit'],
            $filter['offset']
            ),
        ARRAY_A );
        // @phpcs:enable

        foreach ( $results as $index => $result ){
            $results[$index]['payload'] = maybe_unserialize( $result['payload'] );
        }

        wp_cache_set( __METHOD__, $results, __METHOD__, 10 );

        self::$activity_filter = $filter; // define the current activity filter used for the query

        return $results;
    }

    public static function get_activity_stats( $filters = [] ) {
        $logs = self::get_activity_log( $filters );
        if ( empty( $logs ) ) {
            $logs = [];
        }

        $stats = [
            'records_count' => count( $logs ),
            'sites' => [],
            'sites_totals' => [],
            'actions' => [],
            'actions_totals' => [],
        ];

        $sites = DT_Network_Dashboard_Site_Post_Type::all_visible_sites();
        foreach ( $sites as $site ){
            $stats['sites'][$site['partner_id']] = $site['name'];
        }

        $stats['actions'] = dt_network_dashboard_registered_actions();

        foreach ( $logs as $log ){
            /* sites */
            $stats['sites_labels'][$log['site_id']] = $log['site_name'];
            if ( ! isset( $stats['sites_totals'][$log['site_id']] ) ){
                $stats['sites_totals'][$log['site_id']] = 0;
            }
            $stats['sites_totals'][$log['site_id']]++;

            /* actions*/

            if ( ! isset( $stats['actions_totals'][$log['action']] ) ){
                $stats['actions_totals'][$log['action']] = 0;
            }
            $stats['actions_totals'][$log['action']]++;
        }

        $stats['activity_filter'] = self::$activity_filter; // return the current activity filter used for the query

        return $stats;
    }

    public static function format_location_grid_types( $query) {
        if ( !empty( $query ) || !is_array( $query )) {
            foreach ($query as $index => $value) {
                if (isset( $value['grid_id'] )) {
                    $query[$index]['grid_id'] = (int) $value['grid_id'];
                }
                if (isset( $value['population'] )) {
                    $query[$index]['population'] = (int) $value['population'];
                    $query[$index]['population_formatted'] = number_format( (int) $value['population'] );
                }
                if (isset( $value['latitude'] )) {
                    $query[$index]['latitude'] = (float) $value['latitude'];
                }
                if (isset( $value['longitude'] )) {
                    $query[$index]['longitude'] = (float) $value['longitude'];
                }
                if (isset( $value['parent_id'] )) {
                    $query[$index]['parent_id'] = (float) $value['parent_id'];
                }
                if (isset( $value['admin0_grid_id'] )) {
                    $query[$index]['admin0_grid_id'] = (float) $value['admin0_grid_id'];
                }
                if (isset( $value['admin1_grid_id'] )) {
                    $query[$index]['admin1_grid_id'] = (float) $value['admin1_grid_id'];
                }
                if (isset( $value['admin2_grid_id'] )) {
                    $query[$index]['admin2_grid_id'] = (float) $value['admin2_grid_id'];
                }
                if (isset( $value['admin3_grid_id'] )) {
                    $query[$index]['admin3_grid_id'] = (float) $value['admin3_grid_id'];
                }
            }
        }
        return $query;
    }

    public static function location_data_types() {
        return [
            [
                "key" => "contacts",
                "label" => "Contacts"
            ],
            [
                "key" => "groups",
                "label" => "Groups"
            ],
            [
                "key" => "churches",
                "label" => "Churches"
            ],
            [
                "key" => "users",
                "label" => "Users"
            ]
        ];
    }

    /**
     * Gets an array of the last number of days.
     *
     * @param int $number_of_days
     *
     * @return array
     */
    public static function get_day_list( $number_of_days = 60) {
        $d = [];
        for ($i = 0; $i < $number_of_days; $i++) {
            $d[gmdate( "Y-m-d", strtotime( '-' . $i . ' days' ) )] = [
                'date' => gmdate( "Y-m-d", strtotime( '-' . $i . ' days' ) ),
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
    public static function get_month_list( $number_of_months = 25) {
        $d = [];
        for ($i = 0; $i < $number_of_months; $i++) {
            $d[gmdate( "Y-m", strtotime( '-' . $i . ' months' ) ) . '-01'] = [
                'date' => gmdate( "Y-m", strtotime( '-' . $i . ' months' ) ) . '-01',
                'value' => 0,
            ];
        }
        return $d;
    }

    public static function compile_by_days( $type ) {
        $dates1 = self::get_day_list( 60 );
        $dates2 = [];

        $sites = self::get_sites();
        if (empty( $sites )) {
            return [];
        }

        // extract days
        foreach ($sites as $key => $site) {
            foreach ($site[$type]['added']['sixty_days'] as $day) {
                if (isset( $dates1[$day['date']]['value'] ) && $day['value']) {
                    $dates1[$day['date']]['value'] = $dates1[$day['date']]['value'] + $day['value'];
                }
            }
        }

        arsort( $dates1 );

        foreach ($dates1 as $d) {
            $dates2[] = $d;
        }

        return $dates2;
    }

    public static function compile_by_months( $type) {
        $dates1 = self::get_month_list( 25 );
        $dates2 = [];

        $sites = self::get_sites();
        if (empty( $sites )) {
            return [];
        }

        // extract months
        foreach ($sites as $key => $site) {
            foreach ($site[$type]['added']['twenty_four_months'] as $day) {
                if (isset( $dates1[$day['date']]['value'] ) && $day['value']) {
                    $dates1[$day['date']]['value'] = $dates1[$day['date']]['value'] + $day['value'];
                }
            }
        }

        arsort( $dates1 );

        foreach ($dates1 as $d) {
            $dates2[] = $d;
        }

        return $dates2;
    }

    public static function compile_by_days_baptisms() {
        $dates1 = self::get_day_list( 60 );
        $dates2 = [];

        $sites = self::get_sites();
        if (empty( $sites )) {
            return [];
        }

        // extract days
        foreach ($sites as $key => $site) {
            foreach ($site['contacts']['baptisms']['added']['sixty_days'] as $day) {
                if (isset( $dates1[$day['date']]['value'] ) && $day['value']) {
                    $dates1[$day['date']]['value'] = $dates1[$day['date']]['value'] + $day['value'];
                }
            }
        }

        arsort( $dates1 );

        foreach ($dates1 as $d) {
            $dates2[] = $d;
        }

        return $dates2;
    }

    public static function compile_by_months_baptisms() {
        $dates1 = self::get_month_list( 25 );
        $dates2 = [];

        $sites = self::get_sites();
        if (empty( $sites )) {
            return [];
        }

        // extract months
        foreach ($sites as $key => $site) {
            foreach ($site['contacts']['baptisms']['added']['twenty_four_months'] as $day) {
                if (isset( $dates1[$day['date']]['value'] ) && $day['value']) {
                    $dates1[$day['date']]['value'] = $dates1[$day['date']]['value'] + $day['value'];
                }
            }
        }

        arsort( $dates1 );

        foreach ($dates1 as $d) {
            $dates2[] = $d;
        }

        return $dates2;
    }

    public static function compile_by_days_users() {
        $dates1 = self::get_day_list( 60 );
        $dates2 = [];

        $sites = self::get_sites();
        if (empty( $sites )) {
            return [];
        }

        // extract days
        foreach ($sites as $key => $site) {
            foreach ($site['users']['login_activity']['sixty_days'] as $day) {
                if (isset( $dates1[$day['date']]['value'] ) && $day['value']) {
                    $dates1[$day['date']]['value'] = $dates1[$day['date']]['value'] + $day['value'];
                }
            }
        }

        arsort( $dates1 );

        foreach ($dates1 as $d) {
            $dates2[] = $d;
        }

        return $dates2;
    }

    public static function compile_by_months_users() {
        $dates1 = self::get_month_list( 25 );
        $dates2 = [];

        $sites = self::get_sites();
        if (empty( $sites )) {
            return [];
        }

        // extract months
        foreach ($sites as $key => $site) {
            foreach ($site['users']['login_activity']['twenty_four_months'] as $day) {
                if (isset( $dates1[$day['date']]['value'] ) && $day['value']) {
                    $dates1[$day['date']]['value'] = $dates1[$day['date']]['value'] + $day['value'];
                }
            }
        }

        arsort( $dates1 );

        foreach ($dates1 as $d) {
            $dates2[] = $d;
        }

        return $dates2;
    }

    public static function compile_logins_last_thirty_days(){
        $data = [];

        $sites = self::get_sites();
        if (empty( $sites )) {
            return [];
        }

        foreach ($sites as $key => $site) {
            if ( ! isset( $site['users']['last_thirty_day_engagement'] ) ){
                continue;
            }
            foreach ( $site['users']['last_thirty_day_engagement'] as $index => $value) {
                if ( ! isset( $data[$index] ) ) {
                    $data[$index] = $value;
                    continue;
                }
                $data[$index]['value'] = $value['value'] + $data[$index]['value'];
            }
        }

        return $data;
    }

    public static function compile_generations_church(){
        $data = [];

        $sites = self::get_sites();
        if (empty( $sites )) {
            return [];
        }

        foreach ($sites as $key => $site) {
            if ( ! isset( $site['groups']['church_generations'] ) ){
                continue;
            }
            foreach ($site['groups']['church_generations'] as $index => $gen) {
                if ( ! isset( $data[$index] ) ) {
                    $data[$index] = $gen;
                    continue;
                }
                $data[$index]['value'] = $gen['value'] + $data[$index]['value'];
            }
        }

        return $data;
    }

    public static function compile_generations_group(){
        $data = [];

        $sites = self::get_sites();
        if (empty( $sites )) {
            return [];
        }

        foreach ($sites as $key => $site) {
            if ( ! isset( $site['groups']['group_generations'] ) ){
                continue;
            }
            foreach ($site['groups']['group_generations'] as $index => $gen) {
                if ( ! isset( $data[$index] ) ) {
                    $data[$index] = $gen;
                    continue;
                }
                $data[$index]['value'] = $gen['value'] + $data[$index]['value'];
            }
        }

        return $data;
    }

    public static function compile_generations_baptisms(){
        $data = [];

        $sites = self::get_sites();
        if (empty( $sites )) {
            return [];
        }

        foreach ($sites as $key => $site) {
            if ( ! isset( $site['contacts']['baptisms']['generations'] ) ){
                continue;
            }
            foreach ($site['contacts']['baptisms']['generations'] as $index => $gen) {
                if ( ! isset( $data[$index] ) ) {
                    $data[$index] = $gen;
                    continue;
                }
                $data[$index]['value'] = $gen['value'] + $data[$index]['value'];
            }
        }

        return $data;
    }

    public static function compile_user_types(){
        $data = [];

        $sites = self::get_sites();
        if (empty( $sites )) {
            return [];
        }

        foreach ($sites as $key => $site) {
            if ( ! isset( $site['users']['current_state']['roles'] ) ){
                continue;
            }
            foreach ( $site['users']['current_state']['roles'] as $index => $value ) {
                if ( ! isset( $data[$index] ) ) {
                    $data[$index] = $value;
                    continue;
                }
                $data[$index] = $value + $data[$index];
            }
        }

        return $data;
    }

    public static function compile_contacts_status(){
        $data = [];
        $data['total'] = 0;

        $sites = self::get_sites();
        if (empty( $sites )) {
            return [];
        }

        foreach ($sites as $key => $site) {
            if ( ! isset( $site['contacts']['current_state']['status'] ) ){
                continue;
            }
            foreach ( $site['contacts']['current_state']['status'] as $index => $value ) {
                if ( ! isset( $data[$index] ) ) {
                    $data[$index] = $value;
                    continue;
                }
                $data[$index] = $value + $data[$index];
            }

            if ( ! isset( $site['contacts']['current_state']['all_contacts'] ) ){
                continue;
            }
            $data['total'] = $data['total'] + $site['contacts']['current_state']['all_contacts'];

        }

        return $data;
    }

    public static function compile_groups_status(){
        $data = [];
        $data['total'] = 0;

        $sites = self::get_sites();
        if (empty( $sites )) {
            return [];
        }

        foreach ($sites as $key => $site) {
            if ( ! isset( $site['groups']['current_state']['active'] ) ){
                continue;
            }
            foreach ( $site['groups']['current_state']['active'] as $index => $value ) {
                if ( ! isset( $data[$index] ) ) {
                    $data[$index] = $value;
                    continue;
                }
                $data[$index] = $value + $data[$index];
            }

            if ( ! isset( $site['groups']['current_state']['all'] ) ){
                continue;
            }
            $data['total'] = $data['total'] + $site['groups']['current_state']['all'];

        }

        return $data;
    }

    public static function compile_totals() {
        $sites = self::get_sites();
        $data = [
            'total_contacts' => 0,
            'total_groups' => 0,
            'total_users' => 0,
            'total_countries' => 0,
            'total_sites' => 0,
            'total_activities' => 0,
        ];
        if (empty( $sites )) {
            return [];
        }

        foreach ($sites as $key => $site) {
            $data['total_contacts'] = $data['total_contacts'] + $site['contacts']['current_state']['status']['active'];
            $data['total_groups'] = $data['total_groups'] + $site['groups']['current_state']['total_active'];
            $data['total_users'] = $data['total_users'] + $site['users']['current_state']['total_users'];

            if ( !empty( $site['locations']['current_state']['active_admin0_grid_ids'] )) {
                foreach ($site['locations']['current_state']['active_admin0_grid_ids'] as $grid_id) {
                    $data['countries'][$grid_id] = true;
                }
            }
        }
        if ( !empty( $data['countries'] )) {
            $data['total_countries'] = count( $data['countries'] );
        }

        $data['total_sites'] = count( $sites );

        $data['total_activities'] = self::query_count_activity_log();

        return $data;
    }

    public static function query_count_activity_log() : int {
        global $wpdb;
        $time = strtotime( '-30 days' );
        $results = $wpdb->get_var( $wpdb->prepare( "
                SELECT COUNT(ml.id) as count
                FROM $wpdb->dt_movement_log as ml
                LEFT JOIN $wpdb->posts as pid ON pid.post_title=ml.site_id
                	AND pid.post_type = 'dt_network_dashboard'
                LEFT JOIN $wpdb->postmeta as pvisibility ON pid.ID=pvisibility.post_id
                	AND	pvisibility.meta_key = 'visibility'
                WHERE ml.timestamp > %s
                    AND pvisibility.meta_value != 'hide'
                ",
        $time ) );


        if ( empty( $results ) ){
            return 0;
        }
        return $results;

    }

    public function build_log( $filter = [] ){

        $results = $this->get_activity_log( $filter );

        $results = apply_filters( 'dt_network_dashboard_build_message', $results );

        $data = [];
        foreach ( $results as $index => $result ) {
            if ( ! isset( $data[$result['day']] ) ) {
                $data[$result['day']] = [];
                $data[$result['day']]['label'] = $this->create_time_string( $result['day'] );
                $data[$result['day']]['list'] = [];
            }

            if ( isset( $result['message'] ) ) {
                $location = ( empty( $result['label'] ) ) ? '' : ' (' . $result['label'] . ')';
                $data[$result['day']]['list'][] = [
                    'time' => $result['time'],
                    'message' => $result['message'] . $location,
                    'action' => $result['action'],
                    'site_id' => $result['site_id'],
                ];
            }
            else if ( isset( $result['payload']['note'] ) ) {
                $data[$result['day']]['list'][] = [
                    'time' => $result['time'],
                    'message' => '('. $result['time'].') ' .  $result['payload']['note']. '. (' . $result['label'] . ')',
                    'action' => $result['action'],
                    'site_id' => $result['site_id'],
                ];
            }
            else {
                $location = ( empty( $result['label'] ) ) ? '' : ' (' . $result['label'] . ')';
                $data[$result['day']]['list'][] = [
                    'time' => $result['time'],
                    'message' => $result['site_name'] . ' reporting ' . str_replace( '_', ' ', $result['action'] ) . $location,
                    'action' => $result['action'],
                    'site_id' => $result['site_id'],
                ];
            }
        }

        return $data;
    }

    public static function create_time_string( $day ) : string {
        $current_day = strtotime( $day );
        $week_ago = strtotime( '-7 days' );

        if ( $current_day > $week_ago ) {
            $time_string = gmdate( 'l', $current_day );
        }
        else {
            $time_string = gmdate( 'M j, Y', $current_day );
        }
        return $time_string;
    }

    public function _empty_geojson() {
        return array(
            'type' => 'FeatureCollection',
            'features' => []
        );
    }
}
DT_Network_Dashboard_Metrics_Base::instance();


class DT_Network_Dashboard_Metrics_Base_Loader extends DT_Network_Dashboard_Metrics_Base {
    private static $_instance = null;
    public static function instance() {
        if (is_null( self::$_instance )) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        parent::__construct();
        add_action( "template_redirect", [ $this, 'url_redirect' ], 10 );
        add_action( 'wp_enqueue_scripts', [ $this, 'base_scripts' ], 99 );
        add_action( 'rest_api_init', [ $this, 'base_add_api_routes' ] );
    }
}
DT_Network_Dashboard_Metrics_Base_Loader::instance();
