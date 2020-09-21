<?php
/**
 * Scheduled Cron Service
 */

new DT_Network_Dashboard_Cron_Sites_Trigger_Scheduler();
try {
    new DT_Network_Dashboard_Cron_Sites_Trigger_Async();
} catch (Exception $e) {
    dt_write_log($e);
}


// Begin Schedule daily cron build
class DT_Network_Dashboard_Cron_Sites_Trigger_Scheduler
{

    public function __construct()
    {
        if (!wp_next_scheduled('dt_network_dashboard_trigger_sites')) {
            wp_schedule_event(strtotime('tomorrow 3am'), 'daily', 'dt_network_dashboard_trigger_sites');
        }
        add_action('dt_network_dashboard_trigger_sites', [$this, 'action']);
    }

    public static function action()
    {
        do_action("dt_network_dashboard_trigger_sites");
    }
}

class DT_Network_Dashboard_Cron_Sites_Trigger_Async extends Disciple_Tools_Async_Task
{

    protected $action = 'dt_network_dashboard_trigger_sites';

    protected function prepare_data($data)
    {
        return $data;
    }

    protected function run_action()
    {

    }
}

