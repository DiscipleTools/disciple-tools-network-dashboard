<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Network_Dashboard_Migration_0009
 * Delete timestamp unneeded activity from activity_log
 */
class DT_Network_Dashboard_Migration_0009 extends DT_Network_Dashboard_Migration {

    public function up() {
        global $wpdb;

        $activity_timestamp_logs = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}dt_activity_log WHERE object_type = 'dt_network_dashboard' AND `object_subtype` LIKE 'activity_timestamp'" );

        if ( $activity_timestamp_logs > 0 ) {
            foreach ( range( 1, ( $activity_timestamp_logs / 10000 ) + 1 ) as $i ){
                wp_queue()->push( new DT_Delete_Activity_Timestamps() );
            }
        }

    }

    /**
     * @throws \Exception  Got error when dropping table $name.
     */
    public function down() {

    }

    /**
     * Test function
     */
    public function test() {
    }

}
