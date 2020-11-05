<?php

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class DT_Network_Dashboard_Snapshot_Contacts {

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_filter( 'dt_network_dashboard_snapshot_report', array( $this, 'report' ), 10, 1 );
    }

    public function report( $report_data ) {

        $report_data['contacts'] = array(
            'current_state' => DT_Network_Dashboard_Snapshot_Queries::contacts_current_state(),
            'added' => array(
                'sixty_days' => DT_Network_Dashboard_Snapshot_Queries::counted_by_day(),
                'twenty_four_months' => DT_Network_Dashboard_Snapshot_Queries::counted_by_month(),
            ),
            'baptisms' => array(
                'current_state' => array(
                    'all_baptisms' => DT_Network_Dashboard_Snapshot_Queries::total_baptisms(),
                ),
                'added' => array(
                    'sixty_days' => DT_Network_Dashboard_Snapshot_Queries::counted_by_day( 'baptisms' ),
                    'twenty_four_months' => DT_Network_Dashboard_Snapshot_Queries::counted_by_month( 'baptisms' ),
                ),
                'generations' => DT_Network_Dashboard_Snapshot_Queries::generations( 'baptisms' ),
            ),
            'follow_up_funnel' => array(
                'funnel' => DT_Network_Dashboard_Snapshot_Queries::funnel(),
                'ongoing_meetings' => DT_Network_Dashboard_Snapshot_Queries::ongoing_meetings(),
                'coaching' => DT_Network_Dashboard_Snapshot_Queries::coaching(),
            ),
        );

        return $report_data;

    }
}
DT_Network_Dashboard_Snapshot_Contacts::instance();