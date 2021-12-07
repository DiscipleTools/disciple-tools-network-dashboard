<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DT_Network_Activity_Log {

    /**
     * Post Activity Log
     *
     * @param $data
     *
     * @example
     * $data = [
            [
                'site_id' => dt_network_site_id(),
                'action' => 'action',
                'category' => 'complete',
                'location_type' => 'complete', // ip, grid, lnglat
                'location_value' => [
                    'lng' => '-104.968',
                    'lat' => '39.7075',
                    'level' => 'admin2',
                    'label' => 'Denver, Colorado, US',
                    'grid_id' => '100364508'
                ], // ip, grid, lnglat
                'payload' => [
                    'initials' => 'CC',
                    'group_size' => '3',
                    'country' => 'United States',
                    'language' => 'en',
                    'note' => 'This is the full note'.time()
                ],
                'timestamp' => time()
            ]
        ];
     */
    public static function post_activity( $data ) {

        $sites = Site_Link_System::get_list_of_sites_by_type( [ 'network_dashboard_both', 'network_dashboard_sending' ], 'post_ids' );

        foreach ( $sites as $site ) {
            if ( 'yes' === get_post_meta( $site, 'send_activity_log', true ) ) {
                $site_vars = Site_Link_System::get_site_connection_vars( $site );

                $args = [
                    'method' => 'POST',
                    'body' => [
                        'transfer_token' => $site_vars['transfer_token'],
                        'data' => $data
                    ]
                ];
                $response = wp_remote_post( 'https://' . $site_vars['url'] . '/wp-content/plugins/disciple-tools-network-dashboard/public/log.php', $args );
                self::insert_log( $data );

                dt_write_log( 'remote post' );
                if ( ! is_wp_error( $response ) ) {
                    dt_write_log( json_decode( $response['body'], true ) );
                } else {
                    dt_write_log( $response );
                    dt_write_log( $site_vars );
                }
            }
        }
    }

//    public static function convert_log_for_sending( $results ) {
//        $data = [];
//
//        foreach( $results as $row ){
//            $data[] = [
//                'site_id' => $row['site_id'],
//                'site_record_id' => $row['id'],
//                'action' => $row['action'],
//                'category' => $row['category'],
//                'location_type' => 'complete', // ip, grid, lnglat
//                'location_value' => [
//                    'lng' => $row['lng'],
//                    'lat' => $row['lat'],
//                    'level' => $row['level'],
//                    'label' => $row['label'],
//                    'grid_id' => $row['grid_id']
//                ], // ip, grid, lnglat
//                'payload' => maybe_unserialize( $row['payload'] ),
//                'timestamp' => $row['timestamp']
//            ];
//        }
//
//        return $data;
//    }

    public static function insert_log( $data_array ) {

        global $wpdb;
        if ( ! isset( $wpdb->dt_movement_log ) ) {
            $wpdb->dt_movement_log = $wpdb->prefix . 'dt_movement_log';
        }
        if ( ! isset( $wpdb->dt_location_grid ) ) {
            $wpdb->dt_location_grid = $wpdb->prefix . 'dt_location_grid';
        }
        if ( ! isset( $wpdb->dt_location_grid_meta ) ) {
            $wpdb->dt_location_grid_meta = $wpdb->prefix . 'dt_location_grid_meta';
        }

        $process_status = [];
        $process_status['start'] = microtime( true ); // @todo remove after development

        foreach ( $data_array as $activity ) {

            $data = [
                'site_id' => '',
                'site_object_id' => '',
                'action' => '',
                'category' => '',
                'lng' => '',
                'lat' => '',
                'level' => '',
                'label' => '',
                'grid_id' => '',
                'payload' => [],
                'timestamp' => '',
                'hash' => '',
            ];

            // SITE ID
            if ( ! ( isset( $activity['site_id'] ) && ! empty( $activity['site_id'] ) ) ) {
                $process_status[] = [
                    'error' => 'no site id found',
                    'data' => $activity
                ];
                continue;
            }
            $data['site_id'] = sanitize_text_field( wp_unslash( $activity['site_id'] ) );

            // SITE RECORD ID
            if ( isset( $activity['site_record_id'] ) && ! empty( $activity['site_record_id'] ) ) {
                $data['site_record_id'] = sanitize_text_field( wp_unslash( $activity['site_record_id'] ) );
            } else {
                $data['site_record_id'] = null;
            }

            // SITE RECORD ID
            if ( isset( $activity['site_object_id'] ) && ! empty( $activity['site_object_id'] ) ) {
                $data['site_object_id'] = sanitize_text_field( wp_unslash( $activity['site_object_id'] ) );
            } else {
                $data['site_object_id'] = null;
            }

            // ACTION
            if ( ! ( isset( $activity['action'] ) && ! empty( $activity['action'] ) ) ) {
                $process_status[] = [
                    'error' => 'no action found',
                    'data' => $activity
                ];
                continue;
            }
            $data['action'] = sanitize_text_field( wp_unslash( $activity['action'] ) );

            // CATEGORY
            if ( ! isset( $activity['category'] ) ) {
                $process_status[] = [
                    'error' => 'no category found in array',
                    'data' => $activity
                ];
                continue;
            }
            $data['category'] = sanitize_text_field( wp_unslash( $activity['category'] ) );

            // LOCATION TYPE
            if ( ! ( isset( $activity['location_type'] ) && ! empty( $activity['location_type'] ) ) ) {
                $process_status[] = [
                    'error' => 'no location_type found. must be grid, ip, lnglat, complete, no_location',
                    'data' => $activity
                ];
                continue;
            }
            $location_type = sanitize_text_field( wp_unslash( $activity['location_type'] ) );

            // LOCATION VALUE
            if ( ! isset( $activity['location_value'] ) ) {
                $process_status[] = [
                    'error' => 'no location value found in array',
                    'data' => $activity
                ];
                continue;
            }

            // PAYLOAD
            if ( ! isset( $activity['payload'] ) || empty( $activity['payload'] ) ) {
                $activity['payload'] = [];
            }
            $data['payload'] = dt_recursive_sanitize_array( $activity['payload'] );

            // PREPARE LOCATION DATA
            switch ( $location_type ) {
                case 'ip':  /* @param string expects string containing ip address */
                    $data['payload']['location_type'] = 'ip';

                    // validate expected fields
                    if ( ! ( isset( $activity['location_value'] ) && ! empty( $activity['location_value'] ) && ! is_array( $activity['location_value'] ) ) ) {
                        $process_status[] = [
                            'error' => 'did not find all elements of location_value. (ip) location type must have an ip address as a string.',
                            'data' => $activity
                        ];
                        continue 2;
                    }

                    // sanitize string
                    $ip_address = sanitize_text_field( wp_unslash( $activity['location_value'] ) );

                    $ipstack = new DT_Ipstack_API();
                    $response = $ipstack::geocode_ip_address( $ip_address );

                    // set lng and lat
                    $data['lng'] = $response['longitude'] ?? '';
                    $data['lat'] = $response['latitude'] ?? '';

                    // set level
                    // @note blank level means lowest level possible.
                    $level = '';
                    if ( ! empty( $response['city'] ) ) {
                        $level = '';
                    } else if ( ! empty( $response['region_name'] ) ) {
                        $level = 'admin1';
                    } else if ( ! empty( $response['country_name'] ) ) {
                        $level = 'admin0';
                    }
                    $data['level'] = $level;

                    // set label and country
                    $country = $ipstack::parse_raw_result( $response, 'country_name' );
                    $region = $ipstack::parse_raw_result( $response, 'region_name' );
                    if ( $country || $region ) {
                        $data['label'] = $region . ( ! empty( $region ) ? ", " : "" ) . $country;
                    }
                    $data['payload']['country'] = $country;
                    $data['payload']['unique_id'] = hash( 'sha256', $ip_address ); // required so that same activity from same location but different people does not count as duplicate.

                    // set grid id
                    if ( ! empty( $data['lng'] ) || ! empty( $data['lat'] ) ) {
                        $geocoder = new Location_Grid_Geocoder();
                        $grid_response = $geocoder->get_grid_id_by_lnglat( $data['lng'], $data['lat'], $ipstack::parse_raw_result( $response, 'country_code' ), $level );
                        if ( ! empty( $grid_response ) ) {
                            $data['grid_id'] = $grid_response['grid_id'];
                        }
                    }

                    break;
                case 'grid':  /* @param string  expects string containing grid_id */
                    $data['payload']['location_type'] = 'grid';

                    // validate expected fields
                    if ( ! ( isset( $activity['location_value'] ) && ! empty( $activity['location_value'] ) ) ) {
                        $process_status[] = [
                            'error' => 'did not find all elements of location_value. (grid) location type must have (grid_id) number from location_grid database.',
                            'data' => $activity
                        ];
                        continue 2;
                    }

                    $geocoder = new Location_Grid_Geocoder();
                    $grid_response = $geocoder->query_by_grid_id( sanitize_text_field( wp_unslash( $activity['location_value'] ) ) );
                    if ( ! empty( $grid_response ) ) {
                        $data['lng'] = $grid_response['longitude'];
                        $data['lat'] = $grid_response['latitude'];
                        $data['level'] = $grid_response['level_name'];
                        $data['grid_id'] = $grid_response['grid_id'];

                        switch ( $grid_response['level_name'] ) {
                            case 'admin5':
                            case 'admin4':
                            case 'admin3':
                            case 'admin2':
                                $label = $grid_response['name'] . ', ' . $grid_response['admin1_name'] . ', '. $grid_response['country_code'];
                                break;
                            case 'admin1':
                                $label = $grid_response['name'] . ', ' . $grid_response['country_code'];
                                break;
                            case 'admin0':
                            default:
                                $label = $grid_response['admin0_name'];
                                break;
                        }
                        $data['label'] = $label;

                        $data['payload']['country'] = $grid_response['admin0_name'];

                    }

                    break;
                case 'lnglat': /* @param array expects associative array containing (lng, lat, level) strings */
                    $data['payload']['location_type'] = 'lnglat';

                    // validate expected fields
                    if ( ! (
                        is_array( $activity['location_value'] )
                        && isset( $activity['location_value']['lng'] ) && ! empty( $activity['location_value']['lng'] )
                        && isset( $activity['location_value']['lat'] ) && ! empty( $activity['location_value']['lat'] )
                        && isset( $activity['location_value']['level'] )
                    ) ) {
                        $process_status[] = [
                            'error' => 'did not find all elements of location_value. (lnglat) location type must have (lng, lat, level) array elements.',
                            'data' => $activity
                        ];
                        continue 2;
                    }

                    // build location section
                    $data['lng'] = sanitize_text_field( wp_unslash( $activity['location_value']['lng'] ) );
                    $data['lat'] = sanitize_text_field( wp_unslash( $activity['location_value']['lat'] ) );
                    $data['level'] = sanitize_text_field( wp_unslash( $activity['location_value']['level'] ) );

                    if ( isset( $activity['location_value']['label'] ) && ! empty( $activity['location_value']['label'] ) ) {
                        $data['label'] = sanitize_text_field( wp_unslash( $activity['location_value']['label'] ) );
                    }

                    $geocoder = new Location_Grid_Geocoder();
                    $grid_response = $geocoder->get_grid_id_by_lnglat( $data['lng'], $data['lat'], null, $data['level'] );
                    if ( ! empty( $grid_response ) ) {
                        $data['level'] = $grid_response['level_name'];
                        $data['grid_id'] = $grid_response['grid_id'];
                        $data['payload']['country'] = $grid_response['admin0_name'];

                        if ( empty( $data['label'] ) ) {
                            switch ( $grid_response['level_name'] ) {
                                case 'admin5':
                                case 'admin4':
                                case 'admin3':
                                case 'admin2':
                                    $label = $grid_response['name'] . ', ' . $grid_response['admin1_name'] . ', '. $grid_response['country_code'];
                                    break;
                                case 'admin1':
                                    $label = $grid_response['name'] . ', ' . $grid_response['country_code'];
                                    break;
                                case 'admin0':
                                default:
                                    $label = $grid_response['admin0_name'];
                                    break;
                            }
                            $data['label'] = $label;
                        }
                    }

                    break;
                case 'complete': /* @param array expects array with (lng, lat, level, label, grid_id) strings */
                    $data['payload']['location_type'] = 'complete';

                    // validate expected fields
                    if ( ! (
                        is_array( $activity['location_value'] )
                        && isset( $activity['location_value']['lng'] )
                        && isset( $activity['location_value']['lat'] )
                        && isset( $activity['location_value']['level'] )
                        && isset( $activity['location_value']['label'] )
                        && isset( $activity['location_value']['grid_id'] )
                    ) ) {
                        $process_status[] = [
                            'error' => 'did not find all elements of location_value. (Complete) location type must have (lng, lat, level, label, grid_id) array elements.',
                            'data' => $activity
                        ];
                        continue 2;
                    }

                    // build location section
                    $data['lng'] = sanitize_text_field( wp_unslash( $activity['location_value']['lng'] ) );
                    $data['lat'] = sanitize_text_field( wp_unslash( $activity['location_value']['lat'] ) );
                    $data['level'] = sanitize_text_field( wp_unslash( $activity['location_value']['level'] ) );
                    $data['label'] = sanitize_text_field( wp_unslash( $activity['location_value']['label'] ) );
                    $data['grid_id'] = sanitize_text_field( wp_unslash( $activity['location_value']['grid_id'] ) );

                    break;
                case 'no_location':
                    $data['lng'] = null;
                    $data['lat'] = null;
                    $data['level'] = null;
                    $data['label'] = null;
                    $data['grid_id'] = null;
                    break;
                default:
                    $process_status[] = [
                        'error' => 'did not find location_type. Must be ip, grid, lnglat, or complete.',
                        'data' => $activity
                    ];
                    continue 2;
            }

            $data['payload'] = maybe_serialize( $data['payload'] );

            $data['hash'] = hash( 'sha256', serialize( $data ) );

            $data['timestamp'] = ( empty( $params['timestamp'] ) ) ? time() : $params['timestamp'];


            // test if duplicate
            $time = new DateTime();
            $time->modify( '-30 minutes' );
            $past_stamp = $time->format( 'U' );
            $results = $wpdb->get_col( $wpdb->prepare( "SELECT hash FROM $wpdb->dt_movement_log WHERE timestamp > %d", $past_stamp ) );
            if ( array_search( $data['hash'], $results ) !== false ) {
                $process_status[] = [
                    'error' => 'Duplicate',
                    'data' => $activity
                ];
                continue;
            }

            // insert log record
            $wpdb->query( $wpdb->prepare( "
            INSERT INTO $wpdb->dt_movement_log (
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
            VALUES (
                    %s,
                    %s,
                    %s,
                    %s,
                    %s,
                    %f,
                    %f,
                    %s,
                    %s,
                    %d,
                    %s,
                    %d,
                    %s
                    )",
                $data['site_id'],
                $data['site_record_id'],
                $data['site_object_id'],
                $data['action'],
                $data['category'],
                $data['lng'],
                $data['lat'],
                $data['level'],
                $data['label'],
                $data['grid_id'],
                $data['payload'],
                $data['timestamp'],
                $data['hash']
            ) );

            $process_status[] = 'Success: Created id ' . $wpdb->insert_id;
        }

        $process_status['stop'] = microtime( true );

        do_action( 'dt_network_dashboard_post_activity_log_insert' );

        return $process_status;
    }

    public static function insert( $record ) {
        global $wpdb;

        $hash = hash( 'sha256', maybe_serialize( $record ) );

        $args = wp_parse_args(
            $record,
            [
                'site_id' => null,
                'site_record_id' => null,
                'site_object_id' => null,
                'action' => null,
                'category' => null,
                'lng' => null,
                'lat' => null,
                'level' => null,
                'label' => null,
                'grid_id' => null,
                'payload' => [
                    'language_code' => get_locale()
                ],
                'timestamp' => time(),
                'hash' => $hash,
            ]
        );

        // Make sure for non duplicate.
        $check_duplicate = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT
                    `id`
                FROM
                    `$wpdb->dt_movement_log`
                WHERE hash = %s;",
                $hash
            )
        );

        if ( $check_duplicate ) {
            return false;
        }

        if ( is_array( $args['payload'] ) ) {
            $args['payload'] = maybe_serialize( $args['payload'] );
        }

        $wpdb->insert(
            $wpdb->dt_movement_log,
            [
                'site_id' => $args['site_id'],
                'site_record_id' => $args['site_record_id'],
                'site_object_id' => $args['site_object_id'],
                'action' => $args['action'],
                'category' => $args['category'],
                'lng' => $args['lng'],
                'lat' => $args['lat'],
                'level' => $args['level'],
                'label' => $args['label'],
                'grid_id' => $args['grid_id'],
                'payload' => $args['payload'],
                'timestamp' => time(),
                'hash' => $hash,
            ],
            [
                '%s', // site_id
                '%d', // site_record_id
                '%d', // site_object_id
                '%s', // action
                '%s', // category
                '%f', // lng
                '%f', // lat
                '%s', // level
                '%s', // label
                '%d', // grid_id
                '%s', // payload
                '%d', // timestamp
                '%s', // hash
            ]
        );

        $log_id = $wpdb->insert_id;

        return $log_id;
    }

    public static function transfer_insert( array $record ) {
        global $wpdb;

        // @todo check for duplicate??

        // insert log record
        $wpdb->query( $wpdb->prepare( "
            INSERT INTO $wpdb->dt_movement_log (
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
            VALUES (
                    %s,
                    %s,
                    %s,
                    %s,
                    %s,
                    %f,
                    %f,
                    %s,
                    %s,
                    %d,
                    %s,
                    %d,
                    %s
                    )",
            $record['site_id'],
            $record['id'], // this site_record_id is the id of the sending system
            $record['site_object_id'],
            $record['action'],
            $record['category'],
            $record['lng'],
            $record['lat'],
            $record['level'],
            $record['label'],
            $record['grid_id'],
            $record['payload'],
            $record['timestamp'],
            $record['hash']
        ) );

    }

    /**
     * Insert multiple transfer rows from a single site.
     * This INSERT query is written to handle large number of inserts at a single time. It inserts 100 rows at a time.
     *
     * @param $rows
     * @param null $site_id
     */
    public static function transfer_insert_multiple( $rows, $site_id = null ){
        global $wpdb;

        if ( empty( $site_id ) ){
            $site_id = $rows[0]['site_id'];
        }

        $converted = $wpdb->get_col( $wpdb->prepare( "SELECT site_object_id FROM $wpdb->dt_movement_log WHERE site_id = %s AND action = 'new_contact'", $site_id ) );

        $hunk = array_chunk( $rows, 100 );
        foreach ( $hunk as $results ) {
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
            foreach ( $results as $value ){
                if ( ! in_array( $value['site_object_id'], $converted ) ){
                    if ( is_array( $value["payload"] ) ) {
                        $value["payload"] = maybe_serialize( $value["payload"] );
                    }
                    else if ( ! is_serialized( $value["payload"] ) ) {
                        $value["payload"] = maybe_serialize( $value["payload"] );
                    }

                    $index++;
                    $query .= $wpdb->prepare( "( %s, %s, %s, %s, %s, %f, %f, %s, %s, %d, %s, %s, %s ), ",
                        $value["site_id"],
                        $value["id"], // the site id becomes the next site's site_record_id (foreign key)
                        $value["site_object_id"],
                        $value["action"],
                        $value["category"],
                        $value["lng"],
                        $value["lat"],
                        $value["level"],
                        $value["label"],
                        $value["grid_id"],
                        $value["payload"],
                        $value["timestamp"],
                        $value["hash"]
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
     * Rows must have:
     * site_object_id
     * action
     * timestamp
     *
     * @param $results
     */
    public static function local_bulk_insert( $results ) {
        global $wpdb;
        $site_id = dt_network_site_id();
        $hunk = array_chunk( $results, 100 );
        foreach ( $hunk as $results ) {
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
            foreach ( $results as $value ){
                $index++;
                $location = self::get_location_details( $value['site_object_id'] );
                $data = [
                    'site_id' => $site_id,
                    'site_record_id' => null,
                    'site_object_id' => $value['site_object_id'],
                    'action' => $value['action'],
                    'category' => '',
                    'lng' => empty( $location['location_value'] ) ? null : $location['location_value']['lng'] ?? null,
                    'lat' => empty( $location['location_value'] ) ? null : $location['location_value']['lat'] ?? null,
                    'level' => empty( $location['location_value'] ) ? null : $location['location_value']['level'] ?? null,
                    'label' => empty( $location['location_value'] ) ? null : $location['location_value']['label'] ?? null,
                    'grid_id' => empty( $location['location_value'] ) ? null : $location['location_value']['grid_id'] ?? null,
                    'payload' => [
                        'language' => get_locale(),
                    ],
                    'timestamp' => $value['timestamp'],
                ];
                $data['payload'] = maybe_serialize( $data['payload'] );
                $data['hash'] = hash( 'sha256', serialize( $data ) );

                $query .= $wpdb->prepare( "( %s, %s, %s, %s, %s, %f, %f, %s, %s, %d, %s, %s, %s ), ",
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

            $query .= ';';
            $query = str_replace( ", ;", ";", $query ); //remove last comma
            if ( $index > 0 ){
                $wpdb->query( $query ); //phpcs:ignore
            }
        }
    }

    public static function get_location_details( $post_id ) {
        $location = [
            'location_type' => 'no_location',
            'location_value' => [],
        ];

        $grid = get_post_meta( $post_id, 'location_grid', true );
        if ( ! empty( $grid ) ){
            $row = Disciple_Tools_Mapping_Queries::get_by_grid_id( $grid );
            if ( empty( $row ) ){
                return $location;
            }
            $object = new Location_Grid_Geocoder();
            $label = $object->_format_full_name( $row );
            $location = [
                'location_type' => 'complete',
                'location_value' => [
                    'lng' => $row['longitude'],
                    'lat' => $row['latitude'],
                    'level' => $row['level_name'],
                    'label' => $label,
                    'grid_id' => $row['grid_id'],
                ],
            ];
        }

        return $location;
    }

    /**
     * Query gets all new contacts with site_object_id, action, and timestamp from dt_activity_log
     *
     * site_object_id (post_id)
     * action
     * timestamp
     *
     * @return array|object|null
     */
    public static function query_new_contacts(){
        global $wpdb;
        $site_id = dt_network_site_id();
        return $wpdb->get_results( $wpdb->prepare( "
                SELECT
                   distinct(a.object_id) as site_object_id,
                   'new_contact' as action,
                   a.hist_time as timestamp
                FROM $wpdb->dt_activity_log as a
                LEFT JOIN $wpdb->dt_movement_log as m
                    ON a.object_id=m.site_object_id
                    AND m.site_id = %s
                    AND m.action = 'new_contact'
                WHERE a.object_type = 'contacts'
                    AND a.action = 'created'
                    AND m.id IS NULL
                ORDER BY a.object_id;", $site_id ),
        ARRAY_A );
    }

    /**
     * Query gets all new groups by types with site_object_id, action, and timestamp from dt_activity_log
     *
     * site_object_id (post_id)
     * action
     * timestamp
     *
     * @return array|object|null
     */
    public static function query_new_groups(){
        global $wpdb;
        $site_id = dt_network_site_id();
        return $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT a.object_id as site_object_id,
            CASE
                WHEN pm.meta_value = 'pre-group'  THEN 'new_pre-group'
                WHEN pm.meta_value = 'group'  THEN 'new_group'
                WHEN pm.meta_value = 'church'  THEN 'new_church'
                WHEN pm.meta_value = 'team'  THEN 'new_team'
                ELSE 'new_group'
            END as action,
            a.hist_time as timestamp
            FROM $wpdb->dt_activity_log as a
            LEFT JOIN $wpdb->postmeta as pm ON a.object_id=pm.post_id
                AND pm.meta_key = 'group_type'
            LEFT JOIN $wpdb->dt_movement_log as m
                ON a.object_id=m.site_object_id
                AND m.site_id = %s
                AND ( m.action = 'new_pre-group' OR m.action = 'new_group' OR m.action = 'new_church' OR m.action = 'new_team' )
            WHERE a.object_type = 'groups'
            AND a.action = 'created'
            AND m.id IS NULL
            ORDER BY a.object_id;", $site_id ), ARRAY_A );
    }

    /**
     * Query gets all new baptisms by types with site_object_id, action, and timestamp from dt_activity_log
     *
     * site_object_id (post_id)
     * action
     * timestamp
     *
     * @return array|object|null
     */
    public static function query_new_baptism(){
        global $wpdb;
        $site_id = dt_network_site_id();
        return $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT
                DISTINCT( al.object_id ) as site_object_id,
                'new_baptism' as action,
                al.hist_time as timestamp
            FROM $wpdb->dt_activity_log as al
            LEFT JOIN $wpdb->dt_movement_log as m
                ON al.object_id=m.site_object_id
                AND m.site_id = %s
                AND m.action = 'new_baptism'
            WHERE al.object_type = 'contacts'
                AND al.meta_value = 'milestone_baptized'
                AND m.id IS NULL
            ORDER BY al.object_id;", $site_id), ARRAY_A );
    }

    /**
     * Query gets all new baptisms by types with site_object_id, action, and timestamp from dt_activity_log
     *
     * site_object_id (post_id)
     * action
     * timestamp
     *
     * @return array|object|null
     */
    public static function query_new_coaching(){
        global $wpdb;
        $site_id = dt_network_site_id();
        return $results = $wpdb->get_results( $wpdb->prepare( "
           SELECT
                DISTINCT( al.object_id ) as site_object_id,
                'new_coaching' as action,
                al.hist_time as timestamp
            FROM $wpdb->dt_activity_log as al
            LEFT JOIN $wpdb->dt_movement_log as m
                ON al.object_id=m.site_object_id
                AND m.site_id = %s
                AND m.action = 'new_coaching'
            WHERE al.object_type = 'contacts'
                AND al.meta_key = 'contacts_to_contacts'
                AND al.field_type = 'connection from'
                AND m.id IS NULL
            ORDER BY al.object_id;", $site_id), ARRAY_A );
    }

    /**
     * Query gets all new baptisms by types with site_object_id, action, and timestamp from dt_activity_log
     *
     * site_object_id (post_id)
     * action
     * timestamp
     *
     * @return array|object|null
     */
    public static function query_new_group_generations(){
        global $wpdb;
        $site_id = dt_network_site_id();
        return $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT
                DISTINCT( al.object_id ) as site_object_id,
                CASE
                    WHEN pm.meta_value = 'pre-group'  THEN 'generation_pre-group'
                    WHEN pm.meta_value = 'group'  THEN 'generation_group'
                    WHEN pm.meta_value = 'church'  THEN 'generation_church'
                    WHEN pm.meta_value = 'team'  THEN 'generation_team'
                    ELSE 'generation_group'
                END as action,
                al.hist_time as timestamp,
                al.object_subtype
            FROM $wpdb->dt_activity_log as al
            LEFT JOIN $wpdb->postmeta as pm ON al.object_id=pm.post_id
                AND pm.meta_key = 'group_type'
            LEFT JOIN $wpdb->dt_movement_log as m
                ON al.object_id=m.site_object_id
                AND m.site_id = %s
                AND ( m.action = 'generation_pre-group' OR m.action = 'generation_group' OR m.action = 'generation_church' OR m.action = 'generation_team' )
            WHERE al.object_type = 'groups'
                AND al.meta_key = 'groups_to_groups'
                AND al.field_type = 'connection from'
                AND m.id IS NULL
            ORDER BY al.object_id;", $site_id), ARRAY_A );
    }



    /**
     * Delete Activity by Partner/Site ID
     * @param $id
     * @param string $type
     */
    public static function delete_activity( $id, $type = 'partner_id' ){
        global $wpdb;
        switch ( $type ){
            case 'site_id':
            case 'partner_id':
                $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->dt_movement_log WHERE site_id = %s", $id ) );
                break;
            default:
                break;
        }

    }


}
