<?php
/**
 * Scheduled Cron Service
 */

new DT_Network_Dashboard_Cron_Snapshot_Scheduler();
try {
    new DT_Network_Dashboard_Cron_Snapshot_Async();
} catch (Exception $e) {
    dt_write_log($e);
}


// Begin Schedule daily cron build
class DT_Network_Dashboard_Cron_Snapshot_Scheduler
{

    public function __construct()
    {
        if (!wp_next_scheduled('dt_network_dashboard_build_snapshot')) {
            wp_schedule_event(strtotime('tomorrow 1am'), 'daily', 'dt_network_dashboard_build_snapshot');
        }
        add_action('dt_network_dashboard_build_snapshot', [$this, 'action']);
    }

    public static function action()
    {
        do_action("dt_network_dashboard_build_snapshot");
    }
}

class DT_Network_Dashboard_Cron_Snapshot_Async extends Disciple_Tools_Async_Task
{

    protected $action = 'dt_network_dashboard_build_snapshot';

    protected function prepare_data($data)
    {
        return $data;
    }

    protected function run_action()
    {
        DT_Network_Dashboard_Snapshot_Report::snapshot_report();
    }
}

