<?php


use WP_Queue\Job;
class DT_Delete_Activity_Timestamps extends Job {

    /**
     * Job constructor.
     */
    public function __construct(){

    }

    /**
     * Handle job logic.
     */
    public function handle(){
        global $wpdb;
        $wpdb->query( "DELETE FROM $wpdb->dt_activity_log WHERE object_type = 'dt_network_dashboard' AND `object_subtype` LIKE 'activity_timestamp' LIMIT 10000;" );

    }
}