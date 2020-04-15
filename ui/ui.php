<?php


class DT_Network_Dashboard_UI
{
    private static $_instance = null;

    public static function instance() {
        if (is_null( self::$_instance )) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {

        if (current_user_can( 'view_any_contacts' ) || current_user_can( 'view_project_metrics' )) {

            add_action( 'dt_top_nav_desktop', [ $this, 'top_nav_desktop' ], 99 );

            if (isset( $_SERVER["SERVER_NAME"] )) {
                $url = ( !isset( $_SERVER["HTTPS"] ) || @( $_SERVER["HTTPS"] != 'on' ) ) ? 'http://' . sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) ) : 'https://' . sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) );
                if (isset( $_SERVER["REQUEST_URI"] )) {
                    $url .= sanitize_text_field( wp_unslash( $_SERVER["REQUEST_URI"] ) );
                }
            }
            $url_path = trim( str_replace( get_site_url(), "", $url ), '/' );

            if ('network' === substr( $url_path, '0', 7 )) {
                add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ], 199 ); // add custom URL
                add_action( "template_redirect", [ $this, 'url_redirect' ], 10 );
                add_filter( 'dt_metrics_menu', [ $this, 'menu' ], 199 );

                if ('network' === $url_path) {
                    dt_write_log('network');
                    add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
                    add_filter( 'dt_mapping_module_data', [ $this, 'filter_mapping_module_data' ], 50, 1 );
                }

                if ('network/mapbox' === substr( $url_path, '0', 14 )) {
                    add_action( 'wp_enqueue_scripts', [ $this, 'mapbox_scripts' ], 99 );
                    require_once ('mapbox-metrics.php');
                }
            }

        } // end admin only test
    }

    public function add_url( $template_for_url) {
        $template_for_url['network'] = 'template-metrics.php';
        return $template_for_url;
    }

    /**
     * This filter adds a menu item to the metrics
     *
     * @param $content
     *
     * @return string
     */
    public function menu( $content) {
        // home
        $content .= '<li><a href="' . esc_url( site_url( '/network/' ) ) . '#network_home" onclick="show_network_home()">' . esc_html__( 'Home' ) . '</a></li>';
        $content .= '<li><a href="' . esc_url( site_url( '/network/' ) ) . '#sites" onclick="show_sites_list()">' . esc_html__( 'Sites' ) . '</a></li>';

        if ( DT_Mapbox_API::get_key() ) {

            $content .= '<li><a href="' . esc_url( site_url( '/network/' ) ) . '#mapping_list" onclick="page_mapping_list()">' . esc_html__( 'Location List' ) . '</a></li>';
            $content .= '<li><a href="' . esc_url( site_url( '/network/' ) ) . '#mapping_view" onclick="mapping_view()">' . esc_html__( 'Hover Map' ) . '</a></li>';

            $content .= '<li><a>' . esc_html__( 'Cluster Maps' ) . '</a><ul class="menu vertical nested is-active" aria-expanded="true" id="cluster">';
            $content .= '<li><a href="' . esc_url( site_url( '/network/mapbox/cluster/#contacts' ) ) . '"  onclick="write_cluster(\'contact_settings\')" >' . esc_html__( 'Contacts' ) . '</a></li>';
            $content .= '<li><a href="' . esc_url( site_url( '/network/mapbox/cluster/#groups' ) ) . '"  onclick="write_cluster(\'group_settings\')">' . esc_html__( 'Groups' ) . '</a></li>';
            $content .= '<li><a href="' . esc_url( site_url( '/network/mapbox/cluster/#users' ) ) . '">' . esc_html__( 'Users' ) . '</a></li>';
            $content .= '</ul></li>';

            $content .= '<li><a>' . esc_html__( 'Points Maps' ) . '</a><ul class="menu vertical nested is-active" aria-expanded="true" id="points">';
            $content .= '<li><a href="' . esc_url( site_url( '/network/mapbox/points/#contacts' ) ) . '" >' . esc_html__( 'Contacts' ) . '</a></li>';
            $content .= '<li><a href="' . esc_url( site_url( '/network/mapbox/points/#groups' ) ) . '">' . esc_html__( 'Groups' ) . '</a></li>';
            $content .= '<li><a href="' . esc_url( site_url( '/network/mapbox/points/#users' ) ) . '">' . esc_html__( 'Users' ) . '</a></li>';
            $content .= '</ul></li>';

            $content .= '<li><a>' . esc_html__( 'Area Maps' ) . '</a><ul class="menu vertical nested is-active" aria-expanded="true" id="area">';
            $content .= '<li><a href="' . esc_url( site_url( '/network/mapbox/area/#contacts' ) ) . '" >' . esc_html__( 'Contacts' ) . '</a></li>';
            $content .= '<li><a href="' . esc_url( site_url( '/network/mapbox/area/#groups' ) ) . '">' . esc_html__( 'Groups' ) . '</a></li>';
            $content .= '<li><a href="' . esc_url( site_url( '/network/mapbox/area/#users' ) ) . '">' . esc_html__( 'Users' ) . '</a></li>';
            $content .= '</ul></li>';

        } else {
            $content .= '<li><a href="' . esc_url( site_url( '/network/' ) ) . '#mapping_view" onclick="mapping_view()">' . esc_html__( 'Map' ) . '</a></li>';
            $content .= '<li><a href="' . esc_url( site_url( '/network/' ) ) . '#mapping_list" onclick="page_mapping_list()">' . esc_html__( 'List' ) . '</a></li>';
        }

        return $content;
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

    /**
     * Load scripts for the plugin
     */
    public function scripts() {

        DT_Mapping_Module::instance()->scripts();

        // UI script
        wp_enqueue_script('dt_network_dashboard_script',
            trailingslashit( plugin_dir_url( __FILE__ ) ) . 'ui.js',
            [
                'jquery',
                'amcharts-core',
                'amcharts-charts',
                'amcharts-animated',
                'amcharts-maps',
                'datatable',
            ],
            filemtime( plugin_dir_path( __DIR__ ) . 'ui/ui.js' ),
        true);
        wp_localize_script(
            'dt_network_dashboard_script',
            'wpApiNetworkDashboard',
            [
                'root' => esc_url_raw( rest_url() ),
                'plugin_uri' => plugin_dir_url( __DIR__ ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'spinner' => ' <img src="' . plugin_dir_url( __DIR__ ) . 'spinner.svg" width="12px" />',
                'spinner_large' => ' <img src="' . plugin_dir_url( __DIR__ ) . 'spinner.svg" width="24px" />',
                'sites_list' => $this->get_site_list(),
                'sites' => $this->get_sites(),
                'global' => $this->get_global(),
                'locations_list' => $this->get_locations_list(),
                'translations' => [
                    "sm_title" => __( "Network Dashboard", "dt_network_dashboard" ),
                    "title_site_list" => __( "Sites List", "dt_network_dashboard" ),
                    "title_locations" => __( "Locations", "dt_network_dashboard" ),
                ]
            ]
        );

    }

    public function mapbox_scripts() {

        DT_Mapbox_API::load_mapbox_header_scripts();

        wp_enqueue_script('dt_network_dashboard_mapbox',
            trailingslashit( plugin_dir_url( __FILE__ ) ) . 'mapbox-metrics.js',
            [
                'jquery',
                'mapbox-gl',
                'shared-functions',
            ],
            filemtime( plugin_dir_path( __DIR__ ) . 'ui/mapbox-metrics.js' ),
            true);

        $contact_fields = Disciple_Tools_Contact_Post_Type::instance()->get_contact_field_defaults();
        $group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_group_field_defaults();
        wp_localize_script(
            'dt_network_dashboard_mapbox',
            'dtDashboardMapbox',
            [
                'root' => esc_url_raw( rest_url() ),
                'plugin_uri' => plugin_dir_url( __DIR__ ),
                'theme_uri' => trailingslashit( get_stylesheet_directory_uri() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_id' => get_current_user_id(),
                'map_key' => DT_Mapbox_API::get_key(),
                "spinner_url" => get_stylesheet_directory_uri() . '/spinner.svg',
                "translations" => array(
                    'add' => __( 'add', 'disciple-tools' )
                ),
                'contact_settings' => [
                    'post_type' => 'contacts',
                    'title' => __( 'Contacts', "disciple_tools" ),
                    'status_list' => $contact_fields['overall_status']['default'] ?? []
                ],
                'group_settings' => [
                    'post_type' => 'groups',
                    'title' => __( 'Groups', "disciple_tools" ),
                    'status_list' => $group_fields['group_status']['default'] ?? []
                ]
            ]
        );
    }

    public function filter_mapping_module_data( $data) {
        $data['custom_column_labels'] = $this->location_data_types();
        return $data;
    }

    public function top_nav_desktop() {
        if (current_user_can( 'view_any_contacts' ) || current_user_can( 'view_project_metrics' )) {
            ?>
          <li><a href="<?php echo esc_url( site_url( '/network/' ) ); ?>"><?php esc_html_e( "Network" ); ?></a></li><?php
        }
    }

    public function get_sites() {

        if (wp_cache_get( 'get_sites' )) {
            return wp_cache_get( 'get_sites' );
        }

        $new = [];

        $sites = DT_Network_Dashboard_Queries::sites_with_snapshots();
        if ( !empty( $sites )) {
            foreach ($sites as $site) {
                $snapshot = maybe_unserialize( $site['snapshot'] );
                if ( !empty( $snapshot['partner_id'] )) {
                    $new[$snapshot['partner_id']] = $snapshot;
                }
            }
        }

        if (dt_is_current_multisite_dashboard_approved()) {
            $sites = dt_multisite_dashboard_snapshots();
            foreach ($sites as $key => $site) {
                $snapshot = maybe_unserialize( $site );
                if ( !empty( $snapshot['partner_id'] )) {
                    $new[$snapshot['partner_id']] = $snapshot;
                }
            }
        }

        wp_cache_set( 'get_sites', $new );

        return $new;
    }

    public function get_site_list() {
        $sites = DT_Network_Dashboard_Queries::sites_with_snapshots();

        $new = [];
        if ( !empty( $sites )) {
            foreach ($sites as $key => $site) {
                $snapshot = maybe_unserialize( $site['snapshot'] );
                if ( !empty( $snapshot['partner_id'] )) {
                    $new[] = [
                        'id' => $snapshot['partner_id'],
                        'name' => ucwords( $snapshot['profile']['partner_name'] ),
                        'contacts' => $snapshot['contacts']['current_state']['status']['active'],
                        'groups' => $snapshot['groups']['current_state']['total_active'],
                        'users' => $snapshot['users']['current_state']['total_users'],
                        'date' => date( 'Y-m-d H:i:s', $snapshot['date'] ),
                    ];
                }
            }
        }

        if (dt_is_current_multisite_dashboard_approved()) {
            $sites = dt_multisite_dashboard_snapshots();
            foreach ($sites as $key => $site) {
                $snapshot = maybe_unserialize( $site );
                if ( !empty( $snapshot['partner_id'] )) {
                    $new[] = [
                        'id' => $snapshot['partner_id'],
                        'name' => ucwords( $snapshot['profile']['partner_name'] ),
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
                'total' => $totals['total_contacts'] ?? 0,
                'added' => [
                    'sixty_days' => $this->compile_by_days( 'contacts' ),
                    'twenty_four_months' => $this->compile_by_months( 'contacts' ),
                ],
            ],
            'groups' => [
                'total' => $totals['total_groups'] ?? 0,
                'added' => [
                    'sixty_days' => $this->compile_by_days( 'groups' ),
                    'twenty_four_months' => $this->compile_by_months( 'groups' ),
                ],
            ],
            'users' => [
                'total' => $totals['total_users'] ?? 0,
            ],
            'locations' => [
                'total_countries' => $totals['total_countries'] ?? 0,
            ],
        ];

        return $data;
    }

    public function get_locations_list() {
        $data_types = $this->location_data_types();
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
        $sites = $this->get_sites();

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


//            // current state
//            if ( !empty( $site['locations']['current_state']['active_admin0_grid_ids'] )) {
//                foreach ($site['locations']['current_state']['active_admin0_grid_ids'] as $grid_id) {
//                    $data['current_state']['active_admin0_grid_ids'][$grid_id] = true;
//                }
//            }
//            if ( !empty( $site['locations']['current_state']['active_admin1_grid_ids'] )) {
//                foreach ($site['locations']['current_state']['active_admin1_grid_ids'] as $grid_id) {
//                    $data['current_state']['active_admin1_grid_ids'][$grid_id] = true;
//                }
//            }
//            if ( !empty( $site['locations']['current_state']['active_admin2_grid_ids'] )) {
//                foreach ($site['locations']['current_state']['active_admin2_grid_ids'] as $grid_id) {
//                    $data['current_state']['active_admin2_grid_ids'][$grid_id] = true;
//                }
//            }
//
//
//            if ( !empty( $data['current_state']['active_admin0_grid_ids'] )) {
//                $data['current_state']['active_countries'] = count( $data['current_state']['active_admin0_grid_ids'] );
//            }
//            if ( !empty( $data['current_state']['active_admin1_grid_ids'] )) {
//                $data['current_state']['active_admin1'] = count( $data['current_state']['active_admin1_grid_ids'] );
//            }
//            if ( !empty( $data['current_state']['active_admin2_grid_ids'] )) {
//                $data['current_state']['active_admin2'] = count( $data['current_state']['active_admin2_grid_ids'] );
//            }
//
            // complete list
            $list_location_grids = array_keys( $data['list'] );
            $location_grid_properties = $this->format_location_grid_types( Disciple_Tools_Mapping_Queries::get_by_grid_id_list( $list_location_grids, true ) );
            if ( !empty( $location_grid_properties )) {
                foreach ($location_grid_properties as $value) {
                    foreach ($value as $k => $v) {
                        $data['list'][$value['grid_id']][$k] = $v;
                    }
                }
            }
        }

        return $data;
    }

    public function format_location_grid_types( $query) {
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

    public function location_data_types() {
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
    public function get_day_list( $number_of_days = 60) {
        $d = [];
        for ($i = 0; $i < $number_of_days; $i++) {
            $d[date( "Y-m-d", strtotime( '-' . $i . ' days' ) )] = [
                'date' => date( "Y-m-d", strtotime( '-' . $i . ' days' ) ),
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
    public function get_month_list( $number_of_months = 25) {
        $d = [];
        for ($i = 0; $i < $number_of_months; $i++) {
            $d[date( "Y-m", strtotime( '-' . $i . ' months' ) ) . '-01'] = [
                'date' => date( "Y-m", strtotime( '-' . $i . ' months' ) ) . '-01',
                'value' => 0,
            ];
        }
        return $d;
    }

    public function compile_by_days( $type) {
        $dates1 = $this->get_day_list( 60 );
        $dates2 = [];

        $sites = $this->get_sites();
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

    public function compile_by_months( $type) {
        $dates1 = $this->get_month_list( 25 );
        $dates2 = [];

        $sites = $this->get_sites();
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

    public function compile_totals() {
        $sites = $this->get_sites();
        $data = [
            'total_contacts' => 0,
            'total_groups' => 0,
            'total_users' => 0,
            'total_countries' => 0,
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

        return $data;
    }

}

DT_Network_Dashboard_UI::instance();