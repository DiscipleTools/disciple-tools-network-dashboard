<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Network_Dashboard_Migration_0006
 */
class DT_Network_Dashboard_Migration_0006 extends DT_Network_Dashboard_Migration {
    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {

        global $wpdb;
        $wpdb->dt_activity_log = $wpdb->prefix . 'dt_activity_log';
        $wpdb->dt_movement_log = $wpdb->prefix . 'dt_movement_log';

        DT_Network_Dashboard::get_instance();

        if ( ! file_exists( trailingslashit( WP_CONTENT_DIR ) .  'plugins/disciple-tools-network-dashboard/logging/activity-log.php' ) ) {
            require_once( trailingslashit( WP_CONTENT_DIR ) . 'plugins/disciple-tools-network-dashboard/logging/activity-log.php' );
        }

        $results = DT_Network_Activity_Log::query_new_baptism();
        DT_Network_Activity_Log::local_bulk_insert( $results );

        $results = DT_Network_Activity_Log::query_new_coaching();
        DT_Network_Activity_Log::local_bulk_insert( $results );

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
