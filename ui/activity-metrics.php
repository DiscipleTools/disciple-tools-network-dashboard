<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.


class DT_Dashboard_Activity_Metrics  {
    public $sites;
    public $js_file_name = 'activity-metrics.js'; // should be full file name plus extension
    public $permissions = [ 'view_any_contacts', 'view_project_metrics' ];
    public $namespace = "network/activity";

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
    }

    public function add_api_routes() {

        register_rest_route(
            $this->namespace, '/points_geojson', [
                [
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => [$this, 'points_geojson'],
                ],
            ]
        );
        register_rest_route(
            $this->namespace, '/livefeed', [
                [
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => [$this, 'livefeed'],
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

    public function points_geojson( WP_REST_Request $request ) {
        $params = $request->get_json_params() ?? $request->get_body_params();

        return self::query_contacts_points_geojson( $params );
    }

    public function livefeed( WP_REST_Request $request ) {
        $params = $request->get_json_params() ?? $request->get_body_params();

        global $wpdb;
        $timestamp = strtotime('-10000 hours' );
        $results = $wpdb->get_results( $wpdb->prepare( "
                SELECT action, category, lng, lat, payload, timestamp, site_id FROM $wpdb->dt_movement_log WHERE timestamp > %s ORDER BY timestamp DESC
                ", $timestamp ), ARRAY_A );

        $features = [];
        foreach ( $results as $result ) {
            $payload = maybe_unserialize( $result['payload']);

            if ( $result['timestamp'] > strtotime('-24 hour') ) {
                $time = self::time_elapsed_string('@'.$result['timestamp']);
            }
            else {
                $time = date( 'D g:i a', $result['timestamp']);
            }

            $features[] = [
                'timestamp' => $result['timestamp'],
                'message' => '<strong>' . $time . '</strong>' . ' Pray Now! ' . $payload['note'],
                'site_id' => $result['site_id']
            ];
        }

        return $features;
    }

    public static function query_contacts_points_geojson( $params ) {
        global $wpdb;

        $timestamp = strtotime('-100 hours' );
        $results = $wpdb->get_results( $wpdb->prepare( "
                SELECT action, category, lng, lat, payload, timestamp FROM $wpdb->dt_movement_log WHERE timestamp > %s ORDER BY timestamp DESC
                ", $timestamp ), ARRAY_A );

        $features = [];
        foreach ( $results as $result ) {

            if ( $result['timestamp'] > strtotime('-24 hour') ) {
                $time = self::time_elapsed_string('@'.$result['timestamp']);
            }
            else {
                $time = date( 'D g:i a', $result['timestamp']);
            }

            $features[] = array(
                'type' => 'Feature',
                'properties' => array(
                    "note" => $result['note'],
                    "action" => $result['action'],
                    "category" => $result['category'],
                    "time" => $time
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

    public static function  time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

}
DT_Dashboard_Activity_Metrics::instance();