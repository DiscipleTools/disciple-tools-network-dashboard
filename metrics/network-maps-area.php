<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.


class DT_Network_Dashboard_Metrics_Maps_Area extends DT_Network_Dashboard_Metrics_Base
{
    public function __construct() {
        if ( empty( DT_Mapbox_API::get_key() ) ){
            return;
        }
        parent::__construct();

        $this->base_slug = 'maps';
        $this->slug = 'area';
        $this->base_title = __( 'Area Maps', 'disciple-tools-network-dashboard' );
        $this->title = __( 'Area Maps', 'disciple-tools-network-dashboard' );
        $this->menu_title = __( 'Area Maps', 'disciple-tools-network-dashboard' );
        $this->url = $this->root_slug . '/' . $this->base_slug . '/'  . $this->slug;

        $this->key = $this->root_slug . '_' . $this->base_slug . '_' . $this->slug;
        $this->js_file_name = $this->root_slug . '-' . $this->base_slug . '-' . $this->slug . '.js';
        $this->js_object_name = $this->key;

        add_filter( 'dt_network_dashboard_build_menu', array( $this, 'menu' ), 50 );
        add_filter( 'dt_templates_for_urls', array( $this, 'add_url' ), 199 );
        add_action( 'rest_api_init', array( $this, 'add_api_routes' ) );

        if ( $this->url === substr( $this->url_path, 0, 17 ) ) {
            add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ), 99 );
        }

        if ( dt_is_rest( 'network' ) ){
            add_filter( 'dashboard_cluster_geojson', array( $this, 'cluster_geojson_contacts' ), 10, 3 );
            add_filter( 'dashboard_cluster_geojson', array( $this, 'cluster_geojson_groups' ), 10, 3 );

            add_filter( 'dashboard_grid_totals', array( $this, 'grid_totals_contacts' ), 10, 3 );
            add_filter( 'dashboard_grid_totals', array( $this, 'grid_totals_groups' ), 10, 3 );
            add_filter( 'dashboard_grid_totals', array( $this, 'grid_totals_by_type' ), 10, 3 );

            add_filter( 'dashboard_points_geojson', array( $this, 'points_geojson_contacts' ), 10, 3 );
            add_filter( 'dashboard_points_geojson', array( $this, 'points_geojson_groups' ), 10, 3 );
        }

    }

    public function add_scripts() {
        wp_enqueue_script( $this->js_object_name .'_script',
            plugin_dir_url( __FILE__ ) . $this->js_file_name,
            array(
            'jquery',
            'network_base_script',
            ),
            filemtime( plugin_dir_path( __FILE__ ) . $this->js_file_name ),
        true );
        wp_localize_script(
            $this->js_object_name .'_script',
            $this->js_object_name,
            array(
                'endpoint' => $this->url,
                'map_key' => DT_Mapbox_API::get_key(),
                'theme_uri' => trailingslashit( get_stylesheet_directory_uri() ),
                'root' => esc_url_raw( rest_url() ),
                'plugin_uri' => plugin_dir_url( __DIR__ ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_id' => get_current_user_id(),
                "spinner_url" => get_stylesheet_directory_uri() . '/spinner.svg',
                "translations" => array(
                    'add' => __( 'add', 'disciple-tools-network-dashboard' )
                ),
                'contact_settings' => array(
                    'post_type' => 'contacts',
                    'title' => __( 'Contacts', 'disciple-tools-network-dashboard' ),
                    'status_list' => array(
                    'active' => array( "label" => __( 'Active', 'disciple-tools-network-dashboard' ) ),
                    'paused' => array( "label" => __( 'Paused', 'disciple-tools-network-dashboard' ) ),
                    'closed' => array( "label" => __( 'Closed', 'disciple-tools-network-dashboard' ) )
                        )
                        ),
                        'group_settings' => array(
                            'post_type' => 'groups',
                            'title' => __( 'Groups', 'disciple-tools-network-dashboard' ),
                            'status_list' => array(
                    'active' => array( "label" => __( 'Active', 'disciple-tools-network-dashboard' ) ),
                    'inactive' => array( "label" => __( 'Inactive', 'disciple-tools-network-dashboard' ) )
                        )
                        ),
                        'user_settings' => array(
                            'post_type' => 'users',
                            'title' => __( 'Users', 'disciple-tools-network-dashboard' ),
                            'status_list' => array(
                    'active' => array( "label" => __( 'Active', 'disciple-tools-network-dashboard' ) ),
                    'inactive' => array( "label" => __( 'Inactive', 'disciple-tools-network-dashboard' ) )
                        )
                        ),
                        'church_settings' => array(
                            'post_type' => 'churches',
                            'title' => __( 'Churches', 'disciple-tools-network-dashboard' ),
                            'status_list' => array(
                    'active' => array( "label" => __( 'Active', 'disciple-tools-network-dashboard' ) ),
                    'inactive' => array( "label" => __( 'Inactive', 'disciple-tools-network-dashboard' ) )
                        )
                        )
                    )
        );
    }

    public function menu( $tree ){
        $tree[$this->base_slug]['children'][$this->slug] = array(
            'key' => $this->key,
            'label' => __( 'Area Maps', 'disciple-tools-network-dashboard' ),
            'url' => '/'.$this->url,
            'children' => array(
                array(
                    'key' => $this->key . '_contacts',
                    'label' => __( 'Contacts', 'disciple-tools-network-dashboard' ),
                    'url' => '/'.$this->url . '/contacts',
                    'children' => array()
                ),
                array(
                    'key' => $this->key . '_groups',
                    'label' => __( 'Groups', 'disciple-tools-network-dashboard' ),
                    'url' => '/'.$this->url. '/groups',
                    'children' => array()
                ),
                array(
                    'key' => $this->key . '_churches',
                    'label' => __( 'Churches', 'disciple-tools-network-dashboard' ),
                    'url' => '/'.$this->url. '/churches',
                    'children' => array()
                ),
                array(
                    'key' => $this->key . '_users',
                    'label' => __( 'Users', 'disciple-tools-network-dashboard' ),
                    'url' => '/'.$this->url. '/users',
                    'children' => array()
                ),
            )
        );
        return $tree;
    }

    public function add_url( $template_for_url ) {
//        $template_for_url[$this->url] = 'template-metrics.php';
        $template_for_url[$this->url.'/contacts'] = 'template-metrics.php';
        $template_for_url[$this->url.'/groups'] = 'template-metrics.php';
        $template_for_url[$this->url.'/churches'] = 'template-metrics.php';
        $template_for_url[$this->url.'/users'] = 'template-metrics.php';
        return $template_for_url;
    }

    public function add_api_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->url . '/',
            array(
                array(
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => array( $this, 'endpoint' ),
                    'permission_callback' => '__return_true',
                ),
            )
        );


        register_rest_route(
            $this->namespace,
            '/' . $this->url . '/grid_totals/',
            array(
                array(
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => array( $this, 'grid_totals' ),
                    'permission_callback' => '__return_true',
                ),
            )
        );
        register_rest_route(
            $this->namespace,
            '/' . $this->url . '/get_grid_list',
            array(
                array(
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => array( $this, 'get_grid_list' ),
                    'permission_callback' => '__return_true',
                ),
            )
        );
        register_rest_route(
            $this->namespace,
            '/' . $this->url . '/grid_country_totals',
            array(
                array(
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => array( $this, 'grid_country_totals' ),
                    'permission_callback' => '__return_true',
                ),
            )
        );
        register_rest_route(
            $this->namespace,
            '/' . $this->url . '/points_geojson',
            array(
                array(
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => array( $this, 'points_geojson' ),
                    'permission_callback' => '__return_true',
                ),
            )
        );
    }

    public function endpoint( WP_REST_Request $request ){
        if ( !$this->has_permission() ) {
            return new WP_Error( __METHOD__, "Missing Permissions", array( 'status' => 400 ) );
        }
        $params = $request->get_params();

        return $params;
    }

    public function grid_totals( WP_REST_Request $request ) {
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", array( 'status' => 400 ) );
        }
        $params = $request->get_json_params() ?? $request->get_body_params();
        if ( ! isset( $params['post_type'] ) || empty( $params['post_type'] ) ) {
            return new WP_Error( __METHOD__, "Missing Post Types", array( 'status' => 400 ) );
        }
        $post_type = sanitize_text_field( wp_unslash( $params['post_type'] ) );

        $status = null;
        if ( isset( $params['status'] ) && $params['status'] !== 'all' ) {
            $status = sanitize_text_field( wp_unslash( $params['status'] ) );
        }

        return apply_filters( 'dashboard_grid_totals', $grid_list = array(), $post_type, $status );

    }
    public function grid_totals_contacts( $grid_list, $post_type, $status ) {
        if ( 'contacts' !== $post_type ) {
            return $grid_list;
        }

        if ( array_search( $status, array( 'all', 'active', 'paused', 'closed' ) ) === false ) {
            $status = 'all';
        }

        $sites = $this->get_sites();
        $grid_list = array();
        if ( ! empty( $sites ) ) {
            foreach ( $sites as $key => $site ) {
                foreach ( $site['locations'][$post_type][$status] as $grid ) {
                    if ( ! isset( $grid_list[$grid['grid_id']] ) ) {
                        $grid_list[$grid['grid_id']] = array(
                            'grid_id' => $grid['grid_id'],
                            'count' => 0
                        );
                    }

                    $grid_list[$grid['grid_id']]['count'] = $grid_list[$grid['grid_id']]['count'] + $grid['count'];
                }
            }
        }

        return $grid_list;
    }
    public function grid_totals_groups( $grid_list, $post_type, $status ) {
        if ( 'groups' !== $post_type ) {
            return $grid_list;
        }

        if ( array_search( $status, array( 'all', 'active', 'inactive' ) ) === false ) {
            $status = 'all';
        }

        $sites = $this->get_sites();
        $grid_list = array();
        if ( ! empty( $sites ) ) {
            foreach ( $sites as $key => $site ) {
                foreach ( $site['locations'][$post_type][$status] as $grid ) {
                    if ( ! isset( $grid_list[$grid['grid_id']] ) ) {
                        $grid_list[$grid['grid_id']] = array(
                            'grid_id' => $grid['grid_id'],
                            'count' => 0
                        );
                    }

                    $grid_list[$grid['grid_id']]['count'] = $grid_list[$grid['grid_id']]['count'] + $grid['count'];
                }
            }
        }

        return $grid_list;
    }
    public function grid_totals_by_type( $grid_list, $type, $status ) {
        if ( array_search( $type, array( 'contacts', 'groups', 'churches', 'users' ) ) === false ) {
            return $grid_list;
        }

        switch ( $type ) {
            case 'contacts':
                if ( array_search( $status, array( 'all', 'active', 'paused', 'closed' ) ) === false ) {
                    $status = 'all';
                }
                break;
            case 'groups':
            case 'churches':
            case 'users':
                if ( array_search( $status, array( 'all', 'active', 'inactive' ) ) === false ) {
                    $status = 'all';
                }
                break;
            default:
                return $grid_list;
                break;
        }

        $sites = $this->get_sites();
        $grid_list = array();
        if ( ! empty( $sites ) ) {
            foreach ( $sites as $key => $site ) {
                foreach ( $site['locations'][$type][$status] as $grid ) {
                    if ( ! isset( $grid_list[$grid['grid_id']] ) ) {
                        $grid_list[$grid['grid_id']] = array(
                            'grid_id' => $grid['grid_id'],
                            'count' => 0
                        );
                    }

                    $grid_list[$grid['grid_id']]['count'] = $grid_list[$grid['grid_id']]['count'] + $grid['count'];
                }
            }
        }

        return $grid_list;
    }

    public function points_geojson( WP_REST_Request $request ) {
        if ( ! $this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", array( 'status' => 400 ) );
        }
        $params = $request->get_json_params() ?? $request->get_body_params();
        if ( ! isset( $params['post_type'] ) || empty( $params['post_type'] ) ) {
            return new WP_Error( __METHOD__, "Missing Post Types", array( 'status' => 400 ) );
        }

        $status = null;
        if ( isset( $params['status'] ) && $params['status'] !== 'all' ) {
            $status = sanitize_text_field( wp_unslash( $params['status'] ) );
        }

        $post_type = sanitize_text_field( wp_unslash( $params['post_type'] ) );

        return apply_filters( 'dashboard_points_geojson', $this->_empty_geojson(), $post_type, $status );
    }
    public function points_geojson_contacts( $geojson, $post_type, $status ) {
        if ( 'contacts' !== $post_type ) {
            return $geojson;
        }

        global $wpdb;

        /* pulling 30k from location_grid_meta table */
        $results = $wpdb->get_results("
            SELECT lgm.label as l, p.post_title as n, lgm.post_id as pid, lgm.lng, lgm.lat, lg.admin0_grid_id as a0, lg.admin1_grid_id as a1
            FROM $wpdb->dt_location_grid_meta as lgm
                 LEFT JOIN $wpdb->posts as p ON p.ID=lgm.post_id
                 LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
            WHERE lgm.post_type = 'trainings'
            LIMIT 40000;
            ",
        ARRAY_A );

        $results = array(
            array(
                'lng' => 0,
                'lat' => 0,
                'l' => 'label',
                'count' => '10',
                "n" => 'name',
                "a0" => 'a0',
                "a1" => 'a1'
            )
        );

        $features = array();
        foreach ( $results as $result ) {
            $features[] = array(
                'type' => 'Feature',
                'properties' => array(
                    "l" => $result['l'],
                    "pid" => $result['pid'],
                    "n" => $result['n'],
                    "a0" => $result['a0'],
                    "a1" => $result['a1']
                ),
                'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => array(
                        $result['lng'],
                        $result['lat'],
                        1
                    ),
                ),
            );
        }

        $geojson = array(
            'type' => 'FeatureCollection',
            'features' => $features,
        );

        return $geojson;
    }
    public function points_geojson_groups( $geojson, $post_type, $status ) {
        if ( 'groups' !== $post_type ) {
            return $geojson;
        }

        return $geojson;
    }

    public function _empty_geojson() {
        return array(
            'type' => 'FeatureCollection',
            'features' => array()
        );
    }

    public function get_groups_geojson( $status = null ) {
        if ( ! $this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", array( 'status' => 400 ) );
        }
        global $wpdb;

        if ( $status ) {
            $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT lg.label as address, p.post_title as name, lg.post_id, lg.lng, lg.lat
            FROM $wpdb->dt_location_grid_meta as lg
                JOIN $wpdb->posts as p ON p.ID=lg.post_id
                LEFT JOIN $wpdb->postmeta as pm ON pm.post_id=p.ID AND pm.meta_key = 'group_status'
            WHERE lg.post_type = 'groups' AND pm.meta_value = %s", $status),
            ARRAY_A );
        } else {
            $results = $wpdb->get_results("
            SELECT lg.label as address, p.post_title as name, lg.post_id, lg.lng, lg.lat
            FROM $wpdb->dt_location_grid_meta as lg
                JOIN $wpdb->posts as p ON p.ID=lg.post_id
                LEFT JOIN $wpdb->postmeta as pm ON pm.post_id=p.ID AND pm.meta_key = 'group_status'
            WHERE lg.post_type = 'groups'",
            ARRAY_A);
        }

        $features = array();
        foreach ( $results as $result ) {
            $features[] = array(
                'type' => 'Feature',
                'properties' => array(
                    "address" => $result['address'],
                    "post_id" => $result['post_id'],
                    "name" => $result['name']
                ),
                'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => array(
                        $result['lng'],
                        $result['lat'],
                        1
                    ),
                ),
            );
        }

        $new_data = array(
            'type' => 'FeatureCollection',
            'features' => $features,
        );

        return $new_data;
    }

    public function get_contacts_geojson( $status = null ) {
        if ( ! $this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", array( 'status' => 400 ) );
        }
        global $wpdb;

        if ( $status ) {
            $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT lg.label as address, p.post_title as name, lg.post_id, lg.lng, lg.lat
            FROM $wpdb->dt_location_grid_meta as lg
                JOIN $wpdb->posts as p ON p.ID=lg.post_id
                LEFT JOIN $wpdb->postmeta as pm ON pm.post_id=p.ID AND pm.meta_key = 'overall_status'
            WHERE lg.post_type = 'contacts'
            AND pm.post_id NOT IN (SELECT u.post_id FROM $wpdb->postmeta as u WHERE u.meta_key = 'corresponds_to_user' AND u.meta_value != '' )
            AND pm.meta_value = %s ", $status),
            ARRAY_A );
        } else {
            $results = $wpdb->get_results("
            SELECT lg.label as address, p.post_title as name, lg.post_id, lg.lng, lg.lat
            FROM $wpdb->dt_location_grid_meta as lg
                JOIN $wpdb->posts as p ON p.ID=lg.post_id
                LEFT JOIN $wpdb->postmeta as pm ON pm.post_id=p.ID AND pm.meta_key = 'overall_status'
            WHERE lg.post_type = 'contacts'
            AND pm.post_id NOT IN (SELECT u.post_id FROM $wpdb->postmeta as u WHERE ( u.meta_key = 'corresponds_to_user' AND u.meta_value != '') OR ( u.meta_key = 'overall_status' AND u.meta_value = 'closed') )",
            ARRAY_A);
        }

        $features = array();
        foreach ( $results as $result ) {
            $features[] = array(
                'type' => 'Feature',
                'properties' => array(
                    "address" => $result['address'],
                    "post_id" => $result['post_id'],
                    "name" => $result['name']
                ),
                'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => array(
                        $result['lng'],
                        $result['lat'],
                        1
                    ),
                ),
            );
        }

        $new_data = array(
            'type' => 'FeatureCollection',
            'features' => $features,
        );

        return $new_data;
    }

    public function get_grid_list( WP_REST_Request $request ){
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", array( 'status' => 400 ) );
        }

        $params = $request->get_json_params() ?? $request->get_body_params();
        if ( ! isset( $params['post_type'] ) || empty( $params['post_type'] ) ) {
            return new WP_Error( __METHOD__, "Missing Post Types", array( 'status' => 400 ) );
        }

        $status = null;
        if ( isset( $params['status'] ) && $params['status'] !== 'all' ) {
            $status = sanitize_text_field( wp_unslash( $params['status'] ) );
        }

        $post_type = sanitize_text_field( wp_unslash( $params['post_type'] ) );
        if ( $post_type === 'contacts' ) {
            return $this->get_contacts_grid_list( $status );
        } else if ( $post_type === 'groups' ) {
            return $this->get_groups_grid_list( $status );
        } else {
            return new WP_Error( __METHOD__, "Invalid post type", array( 'status' => 400 ) );
        }

    }

    public function get_contacts_grid_list( $status = null ) {
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", array( 'status' => 400 ) );
        }

        global $wpdb;
        if ( $status ) {
            $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT DISTINCT lgm.grid_id as grid_id, lgm.grid_meta_id, lgm.post_id, po.post_title as name
            FROM $wpdb->dt_location_grid_meta as lgm
            LEFT JOIN $wpdb->posts as po ON po.ID=lgm.post_id
            JOIN $wpdb->postmeta as pm ON pm.post_id=lgm.post_id AND meta_key = 'overall_status' AND meta_value = 'active'
            WHERE lgm.post_type ='contacts'
            AND po.ID NOT IN (SELECT DISTINCT(u.post_id) FROM $wpdb->postmeta as u WHERE u.meta_key = 'corresponds_to_user' AND u.meta_value != '')
                AND lgm.grid_id IS NOT NULL
                ORDER BY po.post_title
            ;", $status ),
            ARRAY_A );
        } else {
            $results = $wpdb->get_results( "
            SELECT DISTINCT lgm.grid_id as grid_id, lgm.grid_meta_id, lgm.post_id, po.post_title as name
            FROM $wpdb->dt_location_grid_meta as lgm
            LEFT JOIN $wpdb->posts as po ON po.ID=lgm.post_id
            WHERE lgm.post_type ='contacts'
                AND po.ID NOT IN (SELECT DISTINCT(u.post_id) FROM $wpdb->postmeta as u WHERE ( u.meta_key = 'corresponds_to_user' AND u.meta_value != '') OR ( u.meta_key = 'overall_status' AND u.meta_value = 'closed'))
                AND lgm.grid_id IS NOT NULL
                ORDER BY po.post_title
            ;",
            ARRAY_A );
        }


        $list = array();
        foreach ( $results as $result ) {
            if ( ! isset( $list[$result['grid_id']] ) ) {
                $list[$result['grid_id']] = array();
            }
            $list[$result['grid_id']][] = $result;
        }

        return $list;
    }

    public function get_groups_grid_list( $status = null ) {
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", array( 'status' => 400 ) );
        }

        global $wpdb;

        $results = $wpdb->get_results( "
            SELECT DISTINCT lgm.grid_id as grid_id, lgm.grid_meta_id, lgm.post_id, po.post_title as name
            FROM $wpdb->dt_location_grid_meta as lgm
            LEFT JOIN $wpdb->posts as po ON po.ID=lgm.post_id
            WHERE lgm.post_type ='groups'
            	AND lgm.grid_id IS NOT NULL
            	ORDER BY po.post_title
            ;",
        ARRAY_A );

        $list = array();
        foreach ( $results as $result ) {
            if ( ! isset( $list[$result['grid_id']] ) ) {
                $list[$result['grid_id']] = array();
            }
            $list[$result['grid_id']][] = $result;
        }

        return $list;
    }

    public function grid_country_totals( WP_REST_Request $request ) {
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", array( 'status' => 400 ) );
        }
        global $wpdb;
        $results = $wpdb->get_results( "
            SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
            FROM (
             SELECT lg.admin0_grid_id FROM $wpdb->dt_location_grid as lg LEFT JOIN $wpdb->dt_location_grid_meta as lgm ON lg.grid_id=lgm.grid_id WHERE lgm.post_type = 'trainings'
            ) as t0
            GROUP BY t0.admin0_grid_id
            ",
        ARRAY_A );

        $list = array();
        foreach ( $results as $result ) {
            $list[$result['grid_id']] = $result;
        }

        return $list;
    }
}
new DT_Network_Dashboard_Metrics_Maps_Area();
