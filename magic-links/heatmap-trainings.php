<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

if ( strpos( dt_get_url_path(), 'network_app' ) !== false ){
    DT_Network_Dashboard_Public_Heatmap_Trainings::instance();
}

add_filter('dt_network_dashboard_supported_public_links', function( $supported_links ){
    $supported_links[] = [
    'name' => 'Training Map',
    'description' => 'Maps training saturation by admin2 counties globally.',
    'key' => 'network_app_trainings_goal_map',
    'url' => 'network_app/trainings_goal_map'
    ];
    return $supported_links;
}, 10, 1 );


class DT_Network_Dashboard_Public_Heatmap_Trainings
{

    public $magic = false;
    public $parts = false;
    public $root = "network_app";
    public $type = 'trainings_goal_map';
    public $post_type = 'trainings';

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {

        // check if enabled in admin area
        $enabled = get_option( 'dt_network_dashboard_supported_public_links' );
        if ( ! ( isset( $enabled[$this->root . '_' . $this->type] ) && 'enable' === $enabled[$this->root . '_' . $this->type] ) ){
            return;
        }

        // register type
        $this->magic = new DT_Magic_URL( $this->root );
        add_filter( 'dt_magic_url_register_types', [ $this, '_register_type' ], 10, 1 );

        // register REST and REST access
        add_filter( 'dt_allow_rest_access', [ $this, '_authorize_url' ], 10, 1 );
        add_action( 'rest_api_init', [ $this, 'add_endpoints' ] );


        // fail if not valid url
        $url = dt_get_url_path();
        if ( strpos( $url, $this->root . '/' . $this->type ) === false ) {
            return;
        }

        // fail to blank if not valid url
        $this->parts = $this->magic->parse_url_parts();
        if ( ! $this->parts ){
            // @note this returns a blank page for bad url, instead of redirecting to login
            add_filter( 'dt_templates_for_urls', function ( $template_for_url ) {
                $url = dt_get_url_path();
                $template_for_url[ $url ] = 'template-blank.php';
                return $template_for_url;
            }, 199, 1 );
            add_filter( 'dt_blank_access', function(){ return true;
            } );
            add_filter( 'dt_allow_non_login_access', function(){ return true;
            }, 100, 1 );
            return;
        }

        // fail if does not match type
        if ( $this->type !== $this->parts['type'] ){
            return;
        }

        // load if valid url
        add_filter( "dt_blank_title", [ $this, "_browser_tab_title" ] );
        add_action( 'dt_blank_head', [ $this, '_header' ] );
        add_action( 'dt_blank_footer', [ $this, '_footer' ] );
        add_action( 'dt_blank_body', [ $this, 'body' ] ); // body for no post key

        // load page elements
        add_action( 'wp_print_scripts', [ $this, '_print_scripts' ], 1500 );
        add_action( 'wp_print_styles', [ $this, '_print_styles' ], 1500 );

        // register url and access
        add_filter( 'dt_templates_for_urls', [ $this, '_register_url' ], 199, 1 );
        add_filter( 'dt_blank_access', [ $this, '_has_access' ] );
        add_filter( 'dt_allow_non_login_access', function(){ return true;
        }, 100, 1 );
    }

    public function _register_type( array $types ) : array {
        if ( ! isset( $types[$this->root] ) ) {
            $types[$this->root] = [];
        }
        $types[$this->root][$this->type] = [
            'name' => 'Magic',
            'root' => $this->root,
            'type' => $this->type,
            'meta_key' => 'public_key',
            'actions' => [
                '' => 'Manage',
            ],
            'post_type' => $this->post_type,
        ];
        return $types;
    }

    public function _register_url( $template_for_url ){
        $parts = $this->parts;

        // test 1 : correct url root and type
        if ( ! $parts ){ // parts returns false
            return $template_for_url;
        }

        // test 2 : only base url requested
        if ( empty( $parts['public_key'] ) ){ // no public key present
            $template_for_url[ $parts['root'] . '/'. $parts['type'] ] = 'template-blank.php';
            return $template_for_url;
        }

        // test 3 : no specific action requested
        if ( empty( $parts['action'] ) ){ // only root public key requested
            $template_for_url[ $parts['root'] . '/'. $parts['type'] . '/' . $parts['public_key'] ] = 'template-blank.php';
            return $template_for_url;
        }

        // test 4 : valid action requested
        $actions = $this->magic->list_actions( $parts['type'] );
        if ( isset( $actions[ $parts['action'] ] ) ){
            $template_for_url[ $parts['root'] . '/'. $parts['type'] . '/' . $parts['public_key'] . '/' . $parts['action'] ] = 'template-blank.php';
        }

        return $template_for_url;
    }
    public function _has_access() : bool {
        $parts = $this->parts;

        // test 1 : correct url root and type
        if ( $parts ){ // parts returns false
            return true;
        }

        return false;
    }
    public function _header(){
        wp_head();
        $this->header_style();
        $this->header_javascript();
    }
    public function _footer(){
        wp_footer();
    }
    public function _authorize_url( $authorized ){
        if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->root . '/v1/'.$this->type ) !== false ) {
            $authorized = true;
        }
        return $authorized;
    }
    public function _print_scripts(){
        // @link /disciple-tools-theme/dt-assets/functions/enqueue-scripts.php
        $allowed_js = [
            'jquery',
            'lodash',
            'moment',
            'datepicker',
            'site-js',
            'shared-functions',
            'mapbox-gl',
            'mapbox-cookie',
            'mapbox-search-widget',
            'google-search-widget',
            'jquery-cookie',
        ];

        global $wp_scripts;

        if ( isset( $wp_scripts ) ){
            foreach ( $wp_scripts->queue as $key => $item ){
                if ( ! in_array( $item, $allowed_js ) ){
                    unset( $wp_scripts->queue[$key] );
                }
            }
        }
        unset( $wp_scripts->registered['mapbox-search-widget']->extra['group'] );
    }
    public function _print_styles(){
        // @link /disciple-tools-theme/dt-assets/functions/enqueue-scripts.php
        $allowed_css = [
            'foundation-css',
            'jquery-ui-site-css',
            'site-css',
            'datepicker-css',
            'mapbox-gl-css'
        ];

        global $wp_styles;
        if ( isset( $wp_styles ) ) {
            foreach ($wp_styles->queue as $key => $item) {
                if ( !in_array( $item, $allowed_css )) {
                    unset( $wp_styles->queue[$key] );
                }
            }
        }
    }
    public function _browser_tab_title( $title ){
        /**
         * Places a title on the web browser tab.
         */
        return __( "Zúme Trainings Map", 'disciple_tools' );
    }

    public function header_style(){
        ?>
        <style>
            body {
                background: white;
            }
        </style>
        <?php
    }
    public function header_javascript(){
        ?>
        <script>
            let jsObject = [<?php echo json_encode([
                'map_key' => DT_Mapbox_API::get_key(),
                'theme_uri' => trailingslashit( get_stylesheet_directory_uri() ),
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'parts' => $this->parts,
                'trans' => [
                    'add' => __( 'Add Magic', 'disciple_tools' ),
                ],
            ]) ?>][0]

            jQuery(document).ready(function(){
                clearInterval(window.fiveMinuteTimer)
            })

            window.get_geojson = () => {
                return jQuery.ajax({
                    type: "POST",
                    data: JSON.stringify({ action: 'get', parts: jsObject.parts }),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    url: jsObject.root + jsObject.parts.root + '/v1/' + jsObject.parts.type,
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', jsObject.nonce )
                    }
                })
                    .fail(function(e) {
                        console.log(e)
                        jQuery('#error').html(e)
                    })
            }
            window.get_grid_data = () => {
                return jQuery.ajax({
                    type: "POST",
                    data: JSON.stringify({ action: 'POST', parts: jsObject.parts }),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    url: jsObject.root + jsObject.parts.root + '/v1/' + jsObject.parts.type + '/grid_totals',
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', jsObject.nonce )
                    }
                })
                    .fail(function(e) {
                        console.log(e)
                        jQuery('#error').html(e)
                    })
            }

            window.load_data = ( data ) => {
                let content = jQuery('#content')
                let spinner = jQuery('.loading-spinner')

                content.empty()
                jQuery.each(data, function(i,v){
                    content.prepend(`
                         <div class="cell">
                             ${v.name}
                         </div>
                     `)
                })

                spinner.removeClass('active')

            }
        </script>
<!--        <script src="--><?php //echo plugin_dir_url(__FILE__) . 'training-maps.js?ver=' . filemtime( plugin_dir_path( __FILE__ ) . 'training-maps.js' ) ?><!--" type="text/javascript" defer=""></script>-->
        <?php
        return true;
    }
    public function body(){
        ?>
        <div id="custom-style"></div>
        <div id="wrapper">
            <div id="map-wrapper">
                <div id='map'><span class="loading-spinner active"></span></div>
            </div>
        </div>
        <script>
            jQuery(document).ready(function($){
                clearInterval(window.fiveMinuteTimer)

                /* LOAD */
                let spinner = $('.loading-spinner')

                /* set vertical size the form column*/
                $('#custom-style').append(`
                    <style>
                        #wrapper {
                            height: ${window.innerHeight}px !important;
                        }
                        #map-wrapper {
                            height: ${window.innerHeight}px !important;
                        }
                        #map {
                            height: ${window.innerHeight}px !important;
                        }
                    </style>`)


                window.get_grid_data().then(function(grid_data){
                    $('#map').empty()
                    mapboxgl.accessToken = jsObject.map_key;
                    var map = new mapboxgl.Map({
                        container: 'map',
                        style: 'mapbox://styles/mapbox/light-v10',
                        center: [-98, 38.88],
                        minZoom: 2,
                        zoom: 2
                    });

                    // disable map rotation using right click + drag
                    // disable map rotation using touch rotation gesture
                    map.dragRotate.disable();
                    map.touchZoomRotate.disableRotation();

                    // grid memory vars
                    window.previous_grid_id = 0
                    window.previous_grid_list = []

                    map.on('load', function() {
                        window.previous_grid_id = '1'
                        window.previous_grid_list.push('1')
                        jQuery.get('https://storage.googleapis.com/location-grid-mirror/collection/1.geojson', null, null, 'json')
                            .done(function (geojson) {

                                // jQuery.each(geojson.features, function (i, v) {
                                //     if (window.grid_data[geojson.features[i].properties.id]) {
                                //         geojson.features[i].properties.value = parseInt(window.grid_data[geojson.features[i].properties.id].count)
                                //     } else {
                                //         geojson.features[i].properties.value = 0
                                //     }
                                // })
                                map.addSource('1', {
                                    'type': 'geojson',
                                    'data': geojson
                                });
                                map.addLayer({
                                    'id': '1',
                                    'type': 'fill',
                                    'source': '1',
                                    'paint': {
                                        'fill-color': [
                                            'interpolate',
                                            ['linear'],
                                            ['get', 'value'],
                                            0,
                                            'rgba(0, 0, 0, 0)',
                                            1,
                                            '#547df8',
                                            50,
                                            '#3754ab',
                                            100,
                                            '#22346a'
                                        ],
                                        'fill-opacity': 0.75
                                    }
                                });
                                map.addLayer({
                                    'id': '1line',
                                    'type': 'line',
                                    'source': '1',
                                    'paint': {
                                        'line-color': 'black',
                                        'line-width': 1
                                    }
                                });
                            })
                        // map.addSource('layer-source', {
                        //     type: 'geojson',
                        //     data: data,
                        // });
                        //
                        //
                        // spinner.removeClass('active')
                        //
                        // // SET BOUNDS
                        // window.map_bounds_token = 'report_activity_map'
                        // window.map_start = get_map_start( window.map_bounds_token )
                        // if ( window.map_start ) {
                        //     map.fitBounds( window.map_start, {duration: 0});
                        // }
                        // map.on('zoomend', function() {
                        //     set_map_start( window.map_bounds_token, map.getBounds() )
                        // })
                        // map.on('dragend', function() {
                        //     set_map_start( window.map_bounds_token, map.getBounds() )
                        // })
                        // // end set bounds
                    });

                })
            })
        </script>
        <?php
    }

    /**
     * Register REST Endpoints
     * @link https://github.com/DiscipleTools/disciple-tools-theme/wiki/Site-to-Site-Link for outside of wordpress authentication
     */
    public function add_endpoints() {
        $namespace = $this->root . '/v1';
        register_rest_route(
            $namespace, '/'.$this->type, [
                [
                    'methods'  => "POST",
                    'callback' => [ $this, 'endpoint' ],
                ],
            ]
        );

        register_rest_route(
            $namespace,
            '/'.$this->type .'/grid_totals/',
            array(
                array(
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => array( $this, 'grid_totals' ),
                ),
            )
        );
        register_rest_route(
            $namespace,
            '/get_grid_list',
            array(
                array(
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => array( $this, 'get_grid_list' ),
                ),
            )
        );
        register_rest_route(
            $namespace,
            '/grid_country_totals',
            array(
                array(
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => array( $this, 'grid_country_totals' ),
                ),
            )
        );
        register_rest_route(
            $namespace,
            '/points_geojson',
            array(
                array(
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => array( $this, 'points_geojson' ),
                ),
            )
        );
    }

    public function grid_totals( WP_REST_Request $request ){
        $params = $request->get_json_params() ?? $request->get_body_params();

        $sites = DT_Network_Dashboard_Metrics_Base::get_sites();
        $grid_list = array();
        if ( ! empty( $sites ) ) {
            foreach ( $sites as $key => $site ) {
//                foreach ( $site['locations'][$post_type][$status] as $grid ) {
//                    if ( ! isset( $grid_list[$grid['grid_id']] ) ) {
//                        $grid_list[$grid['grid_id']] = array(
//                            'grid_id' => $grid['grid_id'],
//                            'count' => 0
//                        );
//                    }
//
//                    $grid_list[$grid['grid_id']]['count'] = $grid_list[$grid['grid_id']]['count'] + $grid['count'];
//                }
            }
        }

        return $grid_list;
    }
    public function endpoint( WP_REST_Request $request ) {
        $params = $request->get_params();

        if ( ! isset( $params['parts'], $params['action'] ) ) {
            return new WP_Error( __METHOD__, "Missing parameters", [ 'status' => 400 ] );
        }

        $params = dt_recursive_sanitize_array( $params );
        $action = sanitize_text_field( wp_unslash( $params['action'] ) );


        switch ( $action ) {
            case 'get':
                return $this->endpoint_get();
            case 'geojson':
                return $this->get_geojson();

            // add other cases

            default:
                return new WP_Error( __METHOD__, "Missing valid action", [ 'status' => 400 ] );
        }
    }
    public function endpoint_get() {
        $data = [];

        $data[] = [ 'name' => 'List item' ]; // @todo remove example
        $data[] = [ 'name' => 'List item' ]; // @todo remove example

        return $data;
    }

    public function get_geojson() {
        global $wpdb;
        $results = $wpdb->get_results( "SELECT * FROM $wpdb->dt_location_grid WHERE post_type = 'trainings'", ARRAY_A );

        if ( empty( $results ) ) {
            return $this->_empty_geojson();
        }


        // @todo sum multiple reports for same area

        $features = [];
        foreach ($results as $result) {


            // build feature
            $features[] = array(
                'type' => 'Feature',
                'properties' => array(),
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

    private function _empty_geojson() {
        return array(
            'type' => 'FeatureCollection',
            'features' => array()
        );
    }


}

