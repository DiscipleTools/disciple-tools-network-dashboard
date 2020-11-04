<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Network_Dashboard_Migration_0002
 */
class DT_Network_Dashboard_Migration_0002 extends DT_Network_Dashboard_Migration {
    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $query = array(
            "{$wpdb->prefix}dt_movement_log" =>
                "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dt_movement_log` (
                  `id` BIGINT(22) unsigned NOT NULL AUTO_INCREMENT,
                  `site_id` VARCHAR(65) NOT NULL DEFAULT '',
                  `site_record_id` BIGINT(22) DEFAULT NULL,
                  `site_object_id` BIGINT(22) DEFAULT NULL,
                  `action` VARCHAR(50) NOT NULL DEFAULT '',
                  `category` VARCHAR(25) DEFAULT NULL,
                  `lng` FLOAT DEFAULT NULL,
                  `lat` FLOAT DEFAULT NULL,
                  `level` VARCHAR(50) DEFAULT NULL,
                  `label` VARCHAR(255) NOT NULL DEFAULT '',
                  `grid_id` BIGINT(22) DEFAULT NULL,
                  `payload` LONGTEXT NOT NULL,
                  `timestamp` INT(11) NOT NULL,
                  `hash` VARCHAR(65) NOT NULL DEFAULT '',
                  PRIMARY KEY (`id`),
                  KEY `site_id` (`site_id`),
                  KEY `site_record_id` (`site_record_id`),
                  KEY `site_object_id` (`site_object_id`),
                  KEY `action` (`action`),
                  KEY `category` (`category`),
                  KEY `level` (`level`),
                  KEY `grid_id` (`grid_id`)
                ) $charset_collate;",
        );

        foreach ( $query as $name => $table) {
            $rv = $wpdb->query( $table ); // WPCS: unprepared SQL OK
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
