<?php

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class DT_Network_Dashboard_Snapshot_Groups {

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

        $report_data['groups'] = [
            'current_state' => DT_Network_Dashboard_Snapshot_Queries::groups_current_state(),
            'by_types' => DT_Network_Dashboard_Snapshot_Queries::groups_by_type(),
            'added' => [
                'sixty_days' => DT_Network_Dashboard_Snapshot_Queries::counted_by_day('groups'),
                'twenty_four_months' => DT_Network_Dashboard_Snapshot_Queries::counted_by_month('groups'),
            ],
            'health' => DT_Network_Dashboard_Snapshot_Queries::group_health(),
            'church_generations' => DT_Network_Dashboard_Snapshot_Queries::generations('church'),
            'group_generations' => DT_Network_Dashboard_Snapshot_Queries::generations('groups'),
        ];

        return $report_data;
    }
}
DT_Network_Dashboard_Snapshot_Groups::instance();