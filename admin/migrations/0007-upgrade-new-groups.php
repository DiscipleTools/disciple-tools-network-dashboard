<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Network_Dashboard_Migration_0005
 */
class DT_Network_Dashboard_Migration_0007 extends DT_Network_Dashboard_Migration {
    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {

        global $wpdb;
        $wpdb->dt_activity_log = $wpdb->prefix . 'dt_activity_log';
        $wpdb->dt_movement_log = $wpdb->prefix . 'dt_movement_log';

        DT_Network_Dashboard::get_instance();

        $site_id = dt_network_site_id();

        $full_results = $wpdb->get_results("SELECT object_id as post_id, hist_time, pm.meta_value as type FROM $wpdb->dt_activity_log as al LEFT JOIN $wpdb->postmeta as pm ON pm.post_id=al.object_id AND pm.meta_key = 'type' WHERE object_type = 'groups' AND action = 'created';", ARRAY_A);
        $converted = $wpdb->get_col( $wpdb->prepare( "SELECT site_object_id FROM $wpdb->dt_movement_log WHERE site_id = %s AND ( action = 'new_pre-group' OR action = 'new_group' OR action = 'new_church' OR action = 'new_team'   )", $site_id ) );

        $hunk = array_chunk($full_results, 100 );
        foreach( $hunk as $results ) {
            if ( empty( $results ) ){
                continue;
            }
            $query = " INSERT INTO $wpdb->dt_movement_log
                        ( 
                            site_id,
                            site_record_id,
                            site_object_id,
                            action,
                            category,
                            lng,
                            lat,
                            level,
                            label,
                            grid_id,
                            payload,
                            timestamp,
                            hash 
                            )
                        VALUES ";

            $index = 0;
            foreach( $results as $value ){
                if ( ! in_array( $value['post_id'], $converted ) ){
                    $index++;
                    $location = DT_Network_Activity_Log::get_location_details( $value['post_id'] );
                    $data = [
                        'site_id' => $site_id,
                        'site_record_id' => NULL,
                        'site_object_id' => $value['post_id'],
                        'action' => empty( $value['type'] ) ? 'new_group' : 'new_'.$value['type'],
                        'category' => '',
                        'lng' => empty( $location['location_value'] ) ? NULL : $location['location_value']['lng'] ?? NULL,
                        'lat' => empty( $location['location_value'] ) ? NULL : $location['location_value']['lat'] ?? NULL,
                        'level' => empty( $location['location_value'] ) ? NULL : $location['location_value']['level'] ?? NULL,
                        'label' => empty( $location['location_value'] ) ? NULL : $location['location_value']['label'] ?? NULL,
                        'grid_id' => empty( $location['location_value'] ) ? NULL : $location['location_value']['grid_id'] ?? NULL,
                        'payload' => [
                            'language' => get_locale(),
                        ],
                        'timestamp' => $value['hist_time'],
                    ];
                    $data['payload'] = serialize( $data['payload'] );
                    $data['hash'] = hash('sha256', serialize( $data ) );
                    $query .= $wpdb->prepare( "( %s, %s, %s, %s, %s, %d, %d, %s, %s, %d, %s, %s, %s ), ",
                        $data["site_id"],
                        $data["site_record_id"],
                        $data["site_object_id"],
                        $data["action"],
                        $data["category"],
                        $data["lng"],
                        $data["lat"],
                        $data["level"],
                        $data["label"],
                        $data["grid_id"],
                        $data["payload"],
                        $data["timestamp"],
                        $data["hash"]
                    );
                }
            }

            $query .= ';';
            $query = str_replace( ", ;", ";", $query ); //remove last comma
            if ( $index > 0 ){
                $wpdb->query( $query ); //phpcs:ignore
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
