<?php


// Begin Schedule daily cron build
class DT_Network_Dashboard_Cron_Snapshot_Scheduler
{

    public function __construct()
    {
        if (!wp_next_scheduled('dt_load_snapshot_report')) {
            wp_schedule_event(strtotime('tomorrow 1am'), 'twicedaily', 'dt_load_snapshot_report');
        }
        add_action('dt_load_snapshot_report', [$this, 'action']);
    }

    public static function action()
    {
        do_action("dt_load_snapshot_report");
    }
}

class DT_Network_Dashboard_Cron_Snapshot_Async extends Disciple_Tools_Async_Task
{

    protected $action = 'dt_load_snapshot_report';

    protected function prepare_data($data)
    {
        return $data;
    }

    protected function run_action()
    {
        DT_Network_Dashboard_Snapshot_Report::snapshot_report();
    }
}

new DT_Network_Dashboard_Cron_Snapshot_Scheduler();
try {
    new DT_Network_Dashboard_Cron_Snapshot_Async();
} catch (Exception $e) {
    dt_write_log($e);
}
