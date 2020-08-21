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
                  `id` bigint(22) unsigned NOT NULL AUTO_INCREMENT,
                  `site_id` varchar(64) NOT NULL DEFAULT '',
                  `action` varchar(20) NOT NULL DEFAULT '',
                  `category` varchar(20) NOT NULL DEFAULT '',
                  `lng` float NOT NULL,
                  `lat` float NOT NULL,
                  `level` varchar(11) DEFAULT NULL,
                  `label` varchar(255) NOT NULL DEFAULT '',
                  `grid_id` bigint(22) DEFAULT NULL,
                  `payload` longtext NOT NULL,
                  `timestamp` int(11) NOT NULL,
                  `hash` char(64) NOT NULL DEFAULT '',
                  PRIMARY KEY (`id`),
                  KEY `site_id` (`site_id`),
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
