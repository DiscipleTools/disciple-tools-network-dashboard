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
        add_filter( 'dt_network_dashboard_snapshot_report', [ $this, 'report' ], 10, 1 );
    }

    public function report( $report_data ) {

        $report_data['activity'] = [
            [
                'site_id' => 'test',
            ]
        ];

        return $report_data;
    }
}
DT_Network_Dashboard_Snapshot_Activity::instance();