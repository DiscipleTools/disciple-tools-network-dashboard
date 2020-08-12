<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.


class DT_Dashboard_Mapbox_Metrics  {

    //slug and titile of the top menu folder
//    public $base_slug = 'mapbox'; // lowercase
//    public $base_title = "Mapping";

//    public $slugs;
//    public $title = 'Map';
//    public $js_object_name = 'dt_mapbox_metrics'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $sites;
    public $js_file_name = 'mapbox-metrics.js'; // should be full file name plus extension
    public $permissions = [ 'view_any_contacts', 'view_project_metrics' ];
    public $namespace = "network/mapbox";

    private static $_instance = null;

    public static function instance() {
        if (is_null( self::$_instance )) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {

        if ( !$this->has_permission() ){
            return;
        }


        add_action('rest_api_init', [$this, 'add_api_routes']);

        add_filter('dashboard_cluster_geojson', [$this, 'cluster_geojson_contacts'], 10, 3 );
        add_filter('dashboard_cluster_geojson', [$this, 'cluster_geojson_groups'], 10, 3);

        add_filter('dashboard_grid_totals', [$this, 'grid_totals_contacts'], 10, 3);
        add_filter('dashboard_grid_totals', [$this, 'grid_totals_groups'], 10, 3);
        add_filter('dashboard_grid_totals', [$this, 'grid_totals_by_type'], 10, 3);

        add_filter('dashboard_points_geojson', [$this, 'points_geojson_contacts'], 10, 3);
        add_filter('dashboard_points_geojson', [$this, 'points_geojson_groups'], 10, 3);
    }

    public function add_api_routes() {

        register_rest_route(
            $this->namespace, '/cluster_geojson', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'cluster_geojson' ],
                ],
            ]
        );
        register_rest_route(
            $this->namespace, '/grid_totals', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'grid_totals' ],
                ],
            ]
        );
        register_rest_route(
            $this->namespace, '/get_grid_list', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'get_grid_list' ],
                ],
            ]
        );
        register_rest_route(
            $this->namespace, '/grid_country_totals', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'grid_country_totals' ],
                ],
            ]
        );
        register_rest_route(
            $this->namespace, '/points_geojson', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'points_geojson' ],
                ],
            ]
        );
    }

    public function has_permission(){
        $permissions = $this->permissions;
        $pass = count( $permissions ) === 0;
        foreach ( $this->permissions as $permission ){
            if ( current_user_can( $permission ) ){
                $pass = true;
            }
        }
        return $pass;
    }

    public function grid_totals( WP_REST_Request $request ) {
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }
        $params = $request->get_json_params() ?? $request->get_body_params();
        if ( ! isset( $params['post_type'] ) || empty( $params['post_type'] ) ) {
            return new WP_Error( __METHOD__, "Missing Post Types", [ 'status' => 400 ] );
        }
        $post_type = sanitize_text_field( wp_unslash( $params['post_type'] ) );

        $status = null;
        if ( isset( $params['status'] ) && $params['status'] !== 'all' ) {
            $status = sanitize_text_field( wp_unslash( $params['status'] ) );
        }

        return apply_filters( 'dashboard_grid_totals', $grid_list = [], $post_type, $status );

    }
    public function grid_totals_contacts( $grid_list, $post_type, $status ) {
        if ( 'contacts' !== $post_type ) {
            return $grid_list;
        }

        if ( array_search( $status, ['all', 'active', 'paused', 'closed' ] ) === false ) {
            $status = 'all';
        }

        $sites = DT_Network_Dashboard_UI::instance()->get_sites();
        $grid_list = [];
        if ( ! empty( $sites ) ) {
            foreach ( $sites as $key => $site ) {
                foreach( $site['locations'][$post_type][$status] as $grid ) {
                    if ( ! isset( $grid_list[$grid['grid_id']] ) ) {
                        $grid_list[$grid['grid_id']] = [
                            'grid_id' => $grid['grid_id'],
                            'count' => 0
                        ];
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

        if ( array_search( $status, ['all', 'active', 'inactive' ] ) === false ) {
            $status = 'all';
        }

        $sites = DT_Network_Dashboard_UI::instance()->get_sites();
        $grid_list = [];
        if ( ! empty( $sites ) ) {
            foreach ( $sites as $key => $site ) {
                foreach( $site['locations'][$post_type][$status] as $grid ) {
                    if ( ! isset( $grid_list[$grid['grid_id']] ) ) {
                        $grid_list[$grid['grid_id']] = [
                            'grid_id' => $grid['grid_id'],
                            'count' => 0
                        ];
                    }

                    $grid_list[$grid['grid_id']]['count'] = $grid_list[$grid['grid_id']]['count'] + $grid['count'];
                }
            }
        }

        return $grid_list;
    }
    public function grid_totals_by_type( $grid_list, $type, $status ) {
        if ( array_search( $type, ['contacts', 'groups', 'churches', 'users' ] ) === false ) {
            return $grid_list;
        }

        switch( $type ) {
            case 'contacts':
                if ( array_search( $status, ['all', 'active', 'paused', 'closed' ] ) === false ) {
                    $status = 'all';
                }
                break;
            case 'groups':
            case 'churches':
            case 'users':
                if ( array_search( $status, ['all', 'active', 'inactive' ] ) === false ) {
                    $status = 'all';
                }
                break;
            default:
                return $grid_list;
                break;
        }

        $sites = DT_Network_Dashboard_UI::instance()->get_sites();
        $grid_list = [];
        if ( ! empty( $sites ) ) {
            foreach ( $sites as $key => $site ) {
                foreach( $site['locations'][$type][$status] as $grid ) {
                    if ( ! isset( $grid_list[$grid['grid_id']] ) ) {
                        $grid_list[$grid['grid_id']] = [
                            'grid_id' => $grid['grid_id'],
                            'count' => 0
                        ];
                    }

                    $grid_list[$grid['grid_id']]['count'] = $grid_list[$grid['grid_id']]['count'] + $grid['count'];
                }
            }
        }

        return $grid_list;
    }

    public function cluster_geojson( WP_REST_Request $request ) {
        if ( ! $this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }

        $params = $request->get_json_params() ?? $request->get_body_params();
        if ( ! isset( $params['post_type'] ) || empty( $params['post_type'] ) ) {
            return new WP_Error( __METHOD__, "Missing Post Types", [ 'status' => 400 ] );
        }

        $status = null;
        if ( isset( $params['status'] ) && $params['status'] !== 'all' ) {
            $status = sanitize_text_field( wp_unslash( $params['status'] ) );
        }

        $post_type = sanitize_text_field( wp_unslash( $params['post_type'] ) );

        return apply_filters( 'dashboard_cluster_geojson', $this->_empty_geojson(), $post_type, $status );

    }
    public function cluster_geojson_contacts( $geojson, $post_type, $status ) {
        if ( 'contacts' !== $post_type ) {
            return $geojson;
        }

        $results = [
            [
                "lng" => "0",
                "lat" => "0",
                "count" => "10",
                "name" => "test location",
                "level" => "0",
            ]
        ];

        $features = [];
        foreach ( $results as $result ) {
            $features[] = array(
                'type' => 'Feature',
                'properties' => array(
                    "name" => $result['name'],
                    "count" => $result['count'],
                    "level" => $result['level'],
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
    public function cluster_geojson_groups( $geojson, $post_type, $status ) {
        if ( 'groups' !== $post_type ) {
            return $geojson;
        }

        //        if ( $post_type === 'contacts' ) {
//            $results = Disciple_Tools_Mapping_Queries::get_contacts_grid_totals( $status );
//        } else if ( $post_type === 'groups' ) {
//            $results = Disciple_Tools_Mapping_Queries::get_groups_grid_totals( $status );
//        } else {
//            return new WP_Error( __METHOD__, "Invalid post type", [ 'status' => 400 ] );
//        }


        return $geojson;
    }



    public function points_geojson( WP_REST_Request $request ) {
        if ( ! $this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }
        $params = $request->get_json_params() ?? $request->get_body_params();
        if ( ! isset( $params['post_type'] ) || empty( $params['post_type'] ) ) {
            return new WP_Error( __METHOD__, "Missing Post Types", [ 'status' => 400 ] );
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
            ", ARRAY_A );

        $results = [
            [
                'lng' => 0,
                'lat' => 0,
                'l' => 'label',
                'count' => '10',
                "n" => 'name',
                "a0" => 'a0',
                "a1" => 'a1'
            ]
        ];

        $features = [];
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
            'features' => []
        );
    }

    public function get_groups_geojson( $status = null ) {
        if ( ! $this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }
        global $wpdb;

        if ( $status ) {
            $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT lg.label as address, p.post_title as name, lg.post_id, lg.lng, lg.lat 
            FROM $wpdb->dt_location_grid_meta as lg 
                JOIN $wpdb->posts as p ON p.ID=lg.post_id 
                LEFT JOIN $wpdb->postmeta as pm ON pm.post_id=p.ID AND pm.meta_key = 'group_status'
            WHERE lg.post_type = 'groups' AND pm.meta_value = %s", $status), ARRAY_A );
        } else {
            $results = $wpdb->get_results("
            SELECT lg.label as address, p.post_title as name, lg.post_id, lg.lng, lg.lat 
            FROM $wpdb->dt_location_grid_meta as lg 
                JOIN $wpdb->posts as p ON p.ID=lg.post_id 
                LEFT JOIN $wpdb->postmeta as pm ON pm.post_id=p.ID AND pm.meta_key = 'group_status'
            WHERE lg.post_type = 'groups'", ARRAY_A);
        }

        $features = [];
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
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
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
            AND pm.meta_value = %s ", $status), ARRAY_A );
        } else {
            $results = $wpdb->get_results("
            SELECT lg.label as address, p.post_title as name, lg.post_id, lg.lng, lg.lat 
            FROM $wpdb->dt_location_grid_meta as lg 
                JOIN $wpdb->posts as p ON p.ID=lg.post_id 
                LEFT JOIN $wpdb->postmeta as pm ON pm.post_id=p.ID AND pm.meta_key = 'overall_status'
            WHERE lg.post_type = 'contacts'
            AND pm.post_id NOT IN (SELECT u.post_id FROM $wpdb->postmeta as u WHERE ( u.meta_key = 'corresponds_to_user' AND u.meta_value != '') OR ( u.meta_key = 'overall_status' AND u.meta_value = 'closed') )", ARRAY_A);
        }

        $features = [];
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
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }

        $params = $request->get_json_params() ?? $request->get_body_params();
        if ( ! isset( $params['post_type'] ) || empty( $params['post_type'] ) ) {
            return new WP_Error( __METHOD__, "Missing Post Types", [ 'status' => 400 ] );
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
            return new WP_Error( __METHOD__, "Invalid post type", [ 'status' => 400 ] );
        }

    }

    public function get_contacts_grid_list( $status = null ) {
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
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
            ;", $status ), ARRAY_A );
        } else {
            $results = $wpdb->get_results( "
            SELECT DISTINCT lgm.grid_id as grid_id, lgm.grid_meta_id, lgm.post_id, po.post_title as name 
            FROM $wpdb->dt_location_grid_meta as lgm 
            LEFT JOIN $wpdb->posts as po ON po.ID=lgm.post_id          
            WHERE lgm.post_type ='contacts' 
                AND po.ID NOT IN (SELECT DISTINCT(u.post_id) FROM $wpdb->postmeta as u WHERE ( u.meta_key = 'corresponds_to_user' AND u.meta_value != '') OR ( u.meta_key = 'overall_status' AND u.meta_value = 'closed'))
                AND lgm.grid_id IS NOT NULL 
                ORDER BY po.post_title
            ;", ARRAY_A );
        }


        $list = [];
        foreach ( $results as $result ) {
            if ( ! isset( $list[$result['grid_id']] ) ) {
                $list[$result['grid_id']] = [];
            }
            $list[$result['grid_id']][] = $result;
        }

        return $list;
    }

    public function get_groups_grid_list( $status = null ) {
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }

        global $wpdb;

        $results = $wpdb->get_results( "
            SELECT DISTINCT lgm.grid_id as grid_id, lgm.grid_meta_id, lgm.post_id, po.post_title as name 
            FROM $wpdb->dt_location_grid_meta as lgm 
            LEFT JOIN $wpdb->posts as po ON po.ID=lgm.post_id          
            WHERE lgm.post_type ='groups' 
            	AND lgm.grid_id IS NOT NULL 
            	ORDER BY po.post_title
            ;", ARRAY_A );

        $list = [];
        foreach ( $results as $result ) {
            if ( ! isset( $list[$result['grid_id']] ) ) {
                $list[$result['grid_id']] = [];
            }
            $list[$result['grid_id']][] = $result;
        }

        return $list;
    }

    public function grid_country_totals( WP_REST_Request $request ) {
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }
        global $wpdb;
        $results = $wpdb->get_results( "
            SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count 
            FROM (
             SELECT lg.admin0_grid_id FROM $wpdb->dt_location_grid as lg LEFT JOIN $wpdb->dt_location_grid_meta as lgm ON lg.grid_id=lgm.grid_id WHERE lgm.post_type = 'trainings'
            ) as t0
            GROUP BY t0.admin0_grid_id
            ", ARRAY_A );

        $list = [];
        foreach ( $results as $result ) {
            $list[$result['grid_id']] = $result;
        }



        return $list;

    }

}
DT_Dashboard_Mapbox_Metrics::instance();