<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Network_Dashboard_Migration_0003
 */
class DT_Network_Dashboard_Migration_0003 extends DT_Network_Dashboard_Migration {
    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $query = array(
            "{$wpdb->prefix}dt_movement_log_meta" =>
                "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dt_movement_log_meta` (
                  `meta_id` bigint(22) unsigned NOT NULL AUTO_INCREMENT,
                  `ml_id` bigint(22) NOT NULL,
                  `meta_key` varchar(255) NOT NULL DEFAULT '',
                  `meta_value` varchar(255) DEFAULT '',
                  PRIMARY KEY (`meta_id`),
                  KEY `ml_id` (`ml_id`),
                  KEY `meta_key` (`meta_key`)
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
