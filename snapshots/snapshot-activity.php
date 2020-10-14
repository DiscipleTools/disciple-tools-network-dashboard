<?php

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class DT_Network_Dashboard_Snapshot_Activity {

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
//        add_filter( 'dt_network_dashboard_snapshot_report', [ $this, 'report' ], 10, 1 ); // @todo not clear this is a good collection process.
    }

    public function report( $report_data ) {

        global $wpdb;

        $report_data['activity'] = [
            'actions' => [],
            'totals' => [],
            'thirty_days' => [],
        ];

        $site_id = dt_network_site_id();

        $timestamp = strtotime('-30 days' );
        $results = $wpdb->get_results( $wpdb->prepare( "
                SELECT action, category, lng, lat, label, payload, timestamp FROM $wpdb->dt_movement_log WHERE timestamp > %s AND site_id = %s ORDER BY timestamp DESC
                ", $timestamp, $site_id ), ARRAY_A );

        if ( empty( $results ) ) {
            return $report_data;
        }

        // action keys
//        $actions = [];
//        foreach ($results as $result ){
//            $actions[$result['action']] = true;
//        }
//        $report_data['activity']['actions'] = array_keys( $actions );

        // 30 day list
        $report_data['activity']['thirty_days'] = empty( $results ) ? [] : $results ;

        return $report_data;
    }
}
DT_Network_Dashboard_Snapshot_Activity::instance();