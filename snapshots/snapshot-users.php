<?php

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class DT_Network_Dashboard_Snapshot_Users {

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

        $report_data['users'] = [
            'current_state' => self::users_current_state(),
            'login_activity' => [
                'sixty_days' => DT_Network_Dashboard_Snapshot_Queries::counted_by_day( 'logged_in' ),
                'twenty_four_months' => DT_Network_Dashboard_Snapshot_Queries::counted_by_month( 'logged_in' ),
            ],
            'last_thirty_day_engagement' => self::user_logins_last_thirty_days(),
        ];

        return $report_data;
    }

    public static function user_logins_last_thirty_days() {

        $active = DT_Network_Dashboard_Snapshot_Queries::user_logins_last_thirty_days();

        $total_users = count_users();

        $inactive = $total_users['total_users'] - $active;
        if ($inactive < 1) {
            $inactive = 0;
        }

        $data = [
            [
                'label' => 'Active',
                'value' => $active,
            ],
            [
                'label' => 'Inactive',
                'value' => $inactive,
            ]
        ];

        return $data;
    }

    public static function users_current_state() {
        $data = [
            'total_users' => 0,
            'roles' => [
                'responders' => 0,
                'dispatchers' => 0,
                'multipliers' => 0,
                'strategists' => 0,
                'admins' => 0,
            ],
        ];

        // Add types and status
        $users = count_users();

        $data['total_users'] = (int) $users['total_users'];

        foreach ($users['avail_roles'] as $role => $count) {
            if ($role === 'marketer') {
                $data['roles']['responders'] = $data['roles']['responders'] + $count;
            }
            if ($role === 'dispatcher') {
                $data['roles']['dispatchers'] = $data['roles']['dispatchers'] + $count;
            }
            if ($role === 'multiplier') {
                $data['roles']['multipliers'] = $data['roles']['multipliers'] + $count;
            }
            if ($role === 'administrator' || $role === 'dt_admin') {
                $data['roles']['admins'] = $data['roles']['admins'] + $count;
            }
            if ($role === 'strategist') {
                $data['roles']['strategists'] = $data['roles']['strategists'] + $count;
            }
        }

        return $data;
    }

}
DT_Network_Dashboard_Snapshot_Users::instance();