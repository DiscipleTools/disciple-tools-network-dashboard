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

        $timestamp_index = $wpdb->get_results( "SHOW INDEX FROM {$wpdb->prefix}dt_movement_log WHERE Key_name = 'timestamp'" );
        if ( empty( $timestamp_index ) ) {
            $wpdb->query( "ALTER TABLE {$wpdb->prefix}dt_movement_log ADD INDEX timestamp (timestamp)" );
        }
        $site_record_index = $wpdb->get_results( "SHOW INDEX FROM {$wpdb->prefix}dt_movement_log WHERE Key_name = 'site_record_id'" );
        if ( empty( $site_record_index ) ) {
            $wpdb->query( "ALTER TABLE {$wpdb->prefix}dt_movement_log ADD INDEX site_record_id (site_record_id)" );
        }
        $site_object_id_index = $wpdb->get_results( "SHOW INDEX FROM {$wpdb->prefix}dt_movement_log WHERE Key_name = 'site_object_id'" );
        if ( empty( $site_object_id_index ) ) {
            $wpdb->query( "ALTER TABLE {$wpdb->prefix}dt_movement_log ADD INDEX site_object_id (site_object_id)" );
        }
        $lng_index = $wpdb->get_results( "SHOW INDEX FROM {$wpdb->prefix}dt_movement_log WHERE Key_name = 'lng'" );
        if ( empty( $lng_index ) ) {
            $wpdb->query( "ALTER TABLE {$wpdb->prefix}dt_movement_log ADD INDEX lng (lng)" );
        }
        $lat_index = $wpdb->get_results( "SHOW INDEX FROM {$wpdb->prefix}dt_movement_log WHERE Key_name = 'lat'" );
        if ( empty( $lat_index ) ) {
            $wpdb->query( "ALTER TABLE {$wpdb->prefix}dt_movement_log ADD INDEX lat (lat)" );
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
