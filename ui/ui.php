<?php


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

            add_action( 'dt_top_nav_desktop', [ $this, 'top_nav_desktop' ], 99 );

            if ( isset( $_SERVER["SERVER_NAME"] ) ) {
                $url  = ( !isset( $_SERVER["HTTPS"] ) || @( $_SERVER["HTTPS"] != 'on' ) ) ? 'http://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) ) : 'https://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) );
                if ( isset( $_SERVER["REQUEST_URI"] ) ) {
                    $url .= sanitize_text_field( wp_unslash( $_SERVER["REQUEST_URI"] ) );
                }
            }
            $url_path = trim( str_replace( get_site_url(), "", $url ), '/' );

            if ( 'network' === substr( $url_path, '0', 7 ) ) {

                add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ], 199 ); // add custom URL
                add_filter( 'dt_metrics_menu', [ $this, 'menu' ], 199 );

                if ( 'network' === $url_path ) {
                    add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
                    add_filter( 'dt_mapping_module_data', [ $this, 'filter_mapping_module_data' ], 50, 1 );
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
        $content .= '<li><a href="'. esc_url( site_url( '/network/' ) ) .'#network_home" onclick="show_network_home()">' .  esc_html__( 'Home' ) . '</a></li>';
        $content .= '<li><a href="'. esc_url( site_url( '/network/' ) ) .'#sites" onclick="show_sites_list()">' .  esc_html__( 'Sites' ) . '</a></li>';
        $content .= '<li><a href="'. esc_url( site_url( '/network/' ) ) .'#mapping_view" onclick="page_mapping_view()">' .  esc_html__( 'Map' ) . '</a></li>';
        $content .= '<li><a href="'. esc_url( site_url( '/network/' ) ) .'#mapping_list" onclick="page_mapping_list()">' .  esc_html__( 'List' ) . '</a></li>';

        return $content;
    }

    /**
     * Load scripts for the plugin
     */
    public function scripts() {

        // Amcharts
        wp_register_script( 'amcharts-core', 'https://www.amcharts.com/lib/4/core.js', false, '4' );
        wp_register_script( 'amcharts-charts', 'https://www.amcharts.com/lib/4/charts.js', false, '4' );
        wp_register_script( 'amcharts-animated', 'https://www.amcharts.com/lib/4/themes/animated.js', false, '4' );
        wp_register_script( 'amcharts-maps', 'https://www.amcharts.com/lib/4/maps.js', false, '4' );
        wp_register_script( 'amcharts-maps-world', 'https://www.amcharts.com/lib/4/geodata/worldLow.js', false, '4' );

        // Datatables
        wp_register_style( 'datatable-css', '//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css', false, '1.10' );
        wp_enqueue_style( 'datatable-css' );
        wp_register_script( 'datatable', '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js', false, '1.10' );

        // MapBox
//        wp_register_style( 'mapbox-css', 'https://api.tiles.mapbox.com/mapbox-gl-js/v0.54.0/mapbox-gl.css', false, '0.54.0' );
//        wp_enqueue_style( 'mapbox-css' );
//        wp_register_script( 'mapbox', 'https://api.tiles.mapbox.com/mapbox-gl-js/v0.54.0/mapbox-gl.js', false, '0.54.0', false );


        // UI script
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
                'datatable',
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
                'spinner' => ' <img src="'. plugin_dir_url( __DIR__ ) . 'spinner.svg" width="12px" />',
                'spinner_large' => ' <img src="'. plugin_dir_url( __DIR__ ) . 'spinner.svg" width="24px" />',
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

    public function filter_mapping_module_data( $data ) {
        $data['custom_column_labels'] = $this->location_data_types();
        return $data;
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

        $sites = DT_Network_Dashboard_Queries::sites_with_snapshots();

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

        wp_cache_set( 'get_sites', $new ); dt_write_log( $new );

        return $new;
    }

    public function get_site_list() {
        $sites = DT_Network_Dashboard_Queries::sites_with_snapshots();

        $new = [];
        if ( ! empty( $sites ) ) {
            foreach ( $sites as $key => $site ) {
                $snapshot = maybe_unserialize( $site['snapshot'] );
                if ( ! empty( $snapshot['partner_id'] ) ) {
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

        if ( dt_is_current_multisite_dashboard_approved() ) {
            $sites = dt_multisite_dashboard_snapshots();
            foreach ( $sites as $key => $site ) {
                $snapshot = maybe_unserialize( $site );
                if ( ! empty( $snapshot['partner_id'] ) ) {
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
        $data = [
                'data_types' => $this->location_data_types(),
                'current_state' => [
                    'active_countries' => 0,
                    'active_countries_geonames' => [],
                    'active_admin1' => 0,
                    'active_admin1_geonames' => [],
                    'active_admin2' => 0,
                    'active_admin2_geonames' => [],
                ],
                'list' => [],
        ];

        $sites = $this->get_sites();
        if ( empty( $sites ) ) {
            return [];
        }

        foreach ( $sites as $id => $site ) {

            // list
            foreach ( $site['locations']['list'] as $grid_id => $stats ) {
                if ( ! isset( $data['list'][$grid_id] ) ) {
                    $data['list'][$grid_id] = $this->location_data_types( true );
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


            // current state
            if ( ! empty( $site['locations']['current_state']['active_countries_geonames'] ) ) {
                foreach ( $site['locations']['current_state']['active_countries_geonames'] as $grid_id ) {
                    $data['current_state']['active_countries_geonames'][$grid_id] = true;
                }
            }
            if ( ! empty( $site['locations']['current_state']['active_admin1_geonames'] ) ) {
                foreach ( $site['locations']['current_state']['active_admin1_geonames'] as $grid_id ) {
                    $data['current_state']['active_admin1_geonames'][$grid_id] = true;
                }
            }
            if ( ! empty( $site['locations']['current_state']['active_admin2_geonames'] ) ) {
                foreach ( $site['locations']['current_state']['active_admin2_geonames'] as $grid_id ) {
                    $data['current_state']['active_admin2_geonames'][$grid_id] = true;
                }
            }


            if ( ! empty( $data['current_state']['active_countries_geonames'] ) ) {
                $data['current_state']['active_countries'] = count( $data['current_state']['active_countries_geonames'] );
            }
            if ( ! empty( $data['current_state']['active_admin1_geonames'] ) ) {
                $data['current_state']['active_admin1'] = count( $data['current_state']['active_admin1_geonames'] );
            }
            if ( ! empty( $data['current_state']['active_admin2_geonames'] ) ) {
                $data['current_state']['active_admin2'] = count( $data['current_state']['active_admin2_geonames'] );
            }

            // complete list
            $list_geonames = array_keys( $data['list'] );
            $geoname_properties = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_by_grid_id_list( $list_geonames, true ) );
            if ( ! empty( $geoname_properties ) ) {
                foreach ( $geoname_properties as $value ) {
                    foreach ( $value as $k => $v ) {
                        $data['list'][$value['grid_id']][$k] = $v;
                    }
                }
            }
        }
//        dt_write_log($data);

        return $data;
    }

    public function format_geoname_types( $query ) {
        if ( ! empty( $query ) || ! is_array( $query ) ) {
            foreach ( $query as $index => $value ) {
                if ( isset( $value['grid_id'] ) ) {
                    $query[$index]['grid_id'] = (int) $value['grid_id'];
                }
                if ( isset( $value['population'] ) ) {
                    $query[$index]['population'] = (int) $value['population'];
                    $query[$index]['population_formatted'] = number_format( (int) $value['population'] );
                }
                if ( isset( $value['latitude'] ) ) {
                    $query[$index]['latitude'] = (float) $value['latitude'];
                }
                if ( isset( $value['longitude'] ) ) {
                    $query[$index]['longitude'] = (float) $value['longitude'];
                }
                if ( isset( $value['parent_id'] ) ) {
                    $query[$index]['parent_id'] = (float) $value['parent_id'];
                }
                if ( isset( $value['admin0_grid_id'] ) ) {
                    $query[$index]['admin0_grid_id'] = (float) $value['admin0_grid_id'];
                }
                if ( isset( $value['admin1_grid_id'] ) ) {
                    $query[$index]['admin1_grid_id'] = (float) $value['admin1_grid_id'];
                }
                if ( isset( $value['admin2_grid_id'] ) ) {
                    $query[$index]['admin2_grid_id'] = (float) $value['admin2_grid_id'];
                }
                if ( isset( $value['admin3_grid_id'] ) ) {
                    $query[$index]['admin3_grid_id'] = (float) $value['admin3_grid_id'];
                }
            }
        }
        return $query;
    }

    /**
     * NOTE: copy of this function found in network.php:1129
     * and list provided in snapshot_report: locations: data_types.
     * But with customization, not sure if this source is reliable.
     *
     * @param bool $preset
     *
     * @return array
     */
    public function location_data_types( $preset = false ) {
        if ( $preset ) {
            return [
                'contacts' => 0,
                'groups' => 0,
                'churches' => 0,
                'users' => 0,
            ];
        } else {
            return [
                'contacts',
                'groups',
                'churches',
                'users',
            ];
        }

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
            'total_countries' => 0,
        ];
        if ( empty( $sites ) ) {
            return [];
        }

        foreach ( $sites as $key => $site ) {
            $data['total_contacts'] = $data['total_contacts'] + $site['contacts']['current_state']['status']['active'];
            $data['total_groups'] = $data['total_groups'] + $site['groups']['current_state']['total_active'];
            $data['total_users'] = $data['total_users'] + $site['users']['current_state']['total_users'];

            if ( ! empty( $site['locations']['current_state']['active_countries_geonames'] ) ) {
                foreach ( $site['locations']['current_state']['active_countries_geonames'] as $grid_id ) {
                    $data['countries'][$grid_id] = true;
                }
            }
        }
        if ( ! empty( $data['countries'] ) ) {
            $data['total_countries'] = count( $data['countries'] );
        }

        return $data;
    }

}
DT_Network_Dashboard_UI::instance();