<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly


/**
 * Class Disciple_Tools_Update_Needed
 */
class DT_Network_Multisite_Cron_Scheduler {

    public static $token = 'dt_network_dashboard_collect_multisite';

    public function __construct() {

        $crons = DT_Network_Dashboard_Cron::get_crons();
        if ( isset( $crons[self::$token])){

            $cron = $crons[self::$token];
            if ( ! wp_next_scheduled( self::$token ) ) {

                $schedules = wp_get_schedules();
                if ( isset( $schedules[$cron['recurrence']] ) ){
                    $time = time() + $schedules[$cron['recurrence']]['interval'];
                } else {
                    $time = strtotime( 'tomorrow 2am' );
                }

                wp_schedule_event( $time, $cron['recurrence'], self::$token );
            }
            add_action( self::$token, [ $this, 'action' ] );
        }
    }

    public static function action(){
        do_action( self::$token );
    }
}

class DT_Get_Network_Multisite_SnapShot_Async extends Disciple_Tools_Async_Task {

    protected $action = 'dt_network_dashboard_collect_multisite';

    protected function prepare_data( $data ) {
        return $data;
    }

    protected function run_action() {
        $file = 'multisite';
        dt_reset_log( $file );

        dt_save_log( $file, '', false );
        dt_save_log( $file, '*********************************************', false );
        dt_save_log( $file, 'MULTISITE SNAPSHOT LOGS', false );
        dt_save_log( $file, 'Timestamp: ' . current_time( 'mysql' ), false );
        dt_save_log( $file, '*********************************************', false );
        dt_save_log( $file, '', false );

        // Get list of sites
        $sites = dt_multisite_dashboard_snapshots();

        // Loop sites through a second async task, so that each will become and individual async process.

        foreach ( $sites as $key => $site ) {
            try {
                $task = new DT_Get_Single_Multisite_Snapshot();
                $task->launch(
                    [
                        'blog_id' => $key
                    ]
                );
            } catch ( Exception $e ) {
                dt_write_log( $e );
            }
        }
    }

    public static function force_run_action() {
        try {
            $object = new DT_Get_Network_Multisite_SnapShot_Async();
            $object->run_action();
            return true;
        } catch ( Exception $e ) {
            dt_write_log( $e );
            return $e;
        }
    }
}

/**
 * Class DT_Get_Single_Site_Snapshot
 *
 * Headless async service that retrieves the single site snapshot
 */
class DT_Get_Single_Multisite_Snapshot extends Disciple_Tools_Async_Task
{
    protected $action = 'multisite_snapshot';
    protected function prepare_data( $data ) {
        return $data;
    }

    public function get_multisite_snapshot() {

        if ( isset( $_POST[0]['blog_id'] ) ) {
            dt_network_dashboard_collect_multisite( $_POST[0]['blog_id'] );
        }
        else {
            dt_write_log( __METHOD__ . ' : Failed on post array' );
        }
    }

    protected function run_action() {}
}
function dt_load_async_multisite_snapshot() {
    if ( isset( $_POST['_wp_nonce'] )
        && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wp_nonce'] ) ) )
        && isset( $_POST['action'] )
        && sanitize_key( wp_unslash( $_POST['action'] ) ) == 'dt_async_multisite_snapshot' ) {
        try {
            $object = new DT_Get_Single_Multisite_Snapshot();
            $object->get_multisite_snapshot();
        } catch ( Exception $e ) {
            dt_write_log( __METHOD__ . ': Failed to call site snapshot' );
        }
    }
}
add_action( 'init', 'dt_load_async_multisite_snapshot' );

/**
 * @param $blog_id
 *
 * @return bool
 */
function dt_network_dashboard_collect_multisite( $blog_id ) {

    $file = 'multisite';
    dt_save_log( $file, 'START ID: ' . $blog_id );

    switch_to_blog( $blog_id );

    $snapshot = DT_Network_Dashboard_Snapshot_Report::snapshot_report( true );
    if ( $snapshot['status'] == 'FAIL' ) {
        // retry connection in 3 seconds
        sleep( 5 );
        dt_save_log( $file, 'RETRY ID: ' . $blog_id . ' (Payload = FAIL)' );
        $snapshot = DT_Network_Dashboard_Snapshot_Report::snapshot_report( true ); // @todo remove true after development
        if ( $snapshot['status'] == 'FAIL' ) {

            dt_save_log( $file, 'FAIL ID: ' . $blog_id . ' (Unable to run snapshot report for '.$blog_id.')' );
            dt_save_log( $file, maybe_serialize( $snapshot ) );
            restore_current_blog();
            return false;
        }
    }

    restore_current_blog();

    dt_save_log( $file, 'SUCCESS ID: ' . $blog_id );

    return true;
}
