<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Network_Dashboard_Migration_0002
 */
class DT_Network_Dashboard_Migration_0004 extends DT_Network_Dashboard_Migration {
    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {
        global $wpdb;
        $table = $wpdb->prefix . 'dt_movement_log';
        $wpdb->query( "ALTER TABLE {$table} MODIFY category VARCHAR(20);");
        $wpdb->query( "ALTER TABLE {$table} MODIFY lng FLOAT;");
        $wpdb->query( "ALTER TABLE {$table} MODIFY lat FLOAT;");
        $wpdb->query( "ALTER TABLE {$table} MODIFY label VARCHAR(255);");
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
