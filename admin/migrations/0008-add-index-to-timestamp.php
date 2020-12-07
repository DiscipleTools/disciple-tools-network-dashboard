<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Network_Dashboard_Migration_0008
 */
class DT_Network_Dashboard_Migration_0008 extends DT_Network_Dashboard_Migration {
    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {
        global $wpdb;
        $table = $wpdb->prefix . 'dt_movement_log';

        $wpdb->query( "ALTER TABLE {$table} ADD INDEX `timestamp` (`timestamp`);" );
        $wpdb->query( "ALTER TABLE {$table} ADD INDEX `site_record_id` (`site_record_id`);" );
        $wpdb->query( "ALTER TABLE {$table} ADD INDEX `site_object_id` (`site_object_id`);" );
        $wpdb->query( "ALTER TABLE {$table} ADD INDEX `lng` (`lng`);" );
        $wpdb->query( "ALTER TABLE {$table} ADD INDEX `lat` (`lat`);" );

        // @phpcs:enable
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
