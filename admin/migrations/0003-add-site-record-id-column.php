<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Network_Dashboard_Migration_0002
 */
class DT_Network_Dashboard_Migration_0003 extends DT_Network_Dashboard_Migration {
    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {
        global $wpdb;
        $table = $wpdb->prefix . 'dt_movement_log';

        //@phpcs:disable
        if ( empty( $wpdb->get_results( "SHOW COLUMNS FROM {$table} LIKE 'site_record_id';" ) ) ){
            $wpdb->query( "ALTER TABLE {$table} ADD `site_record_id` BIGINT(22) DEFAULT NULL AFTER `site_id`;" );
        }

        if ( empty( $wpdb->get_results( "SHOW COLUMNS FROM {$table} LIKE 'site_object_id';" ) ) ){
            $wpdb->query( "ALTER TABLE {$table} ADD `site_object_id` BIGINT(22) DEFAULT NULL AFTER `site_record_id`;" );
        }

        $wpdb->query( "ALTER TABLE {$table} CHANGE `site_id` `site_id` VARCHAR(65) NOT NULL DEFAULT '';" );
        $wpdb->query( "ALTER TABLE {$table} CHANGE `action` `action` VARCHAR(50) NOT NULL DEFAULT '';" );
        $wpdb->query( "ALTER TABLE {$table} CHANGE `category` `category` VARCHAR(25) DEFAULT NULL;" );
        $wpdb->query( "ALTER TABLE {$table} CHANGE `lng` `lng` FLOAT DEFAULT NULL;" );
        $wpdb->query( "ALTER TABLE {$table} CHANGE `lat` `lat` FLOAT DEFAULT NULL;" );
        $wpdb->query( "ALTER TABLE {$table} CHANGE `level` `level` VARCHAR(50) DEFAULT NULL;" );
        $wpdb->query( "ALTER TABLE {$table} CHANGE `label` `label` VARCHAR(255) NOT NULL DEFAULT '';" );
        $wpdb->query( "ALTER TABLE {$table} CHANGE `timestamp` `timestamp` INT(11) NOT NULL;" );
        $wpdb->query( "ALTER TABLE {$table} CHANGE `hash` `hash` VARCHAR(65) NOT NULL DEFAULT '';" );
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
