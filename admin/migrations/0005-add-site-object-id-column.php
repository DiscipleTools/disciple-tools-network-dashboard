<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Network_Dashboard_Migration_0005
 */
class DT_Network_Dashboard_Migration_0005 extends DT_Network_Dashboard_Migration {
    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {
        global $wpdb;
        $table = $wpdb->prefix . 'dt_movement_log';

        if ( empty( $wpdb->get_results( "SHOW COLUMNS FROM {$table} LIKE 'site_object_id';" ) ) ){
            $wpdb->query( "ALTER TABLE {$table} ADD `site_object_id` BIGINT(22)  NULL  DEFAULT NULL  AFTER `site_record_id`;" );
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
