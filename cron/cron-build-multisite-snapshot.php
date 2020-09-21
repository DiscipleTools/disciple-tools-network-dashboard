<?php
/**
 * Scheduled Cron Service
 */


// Load Scheduler
new DT_Network_Multisite_Cron_Scheduler();
try {
    new DT_Get_Network_Multisite_SnapShot_Async();
} catch ( Exception $e ) {
    dt_write_log( $e );
}


/**
 * Class Disciple_Tools_Update_Needed
 */
class DT_Network_Multisite_Cron_Scheduler {

    public function __construct()
    {
        if (!wp_next_scheduled('dt_network_dashboard_collect_multisite_snapshots')) {
            wp_schedule_event(strtotime('tomorrow 2am'), 'daily', 'dt_network_dashboard_collect_multisite_snapshots');
        }
        add_action('dt_network_dashboard_collect_multisite_snapshots', [$this, 'action']);
    }

    public static function action()
    {
        do_action("dt_network_dashboard_collect_multisite_snapshots");
    }
}

class DT_Get_Network_Multisite_SnapShot_Async extends Disciple_Tools_Async_Task {

    protected $action = 'dt_network_dashboard_collect_multisite_snapshots';

    protected function prepare_data( $data ) {
        return $data;
    }

    protected function run_action() {
        dt_network_dashboard_multisite_snapshot_async();
    }

//    public static function force_run_action() { // @todo I think this is unneeded. Sept 21 Watch for bugs and then remove.
//        dt_network_dashboard_multisite_snapshot_async();
//    }
}