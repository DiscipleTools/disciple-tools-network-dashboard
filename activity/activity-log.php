<?php
if ( ! defined('ABSPATH')) {
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

        $sites = Site_Link_System::get_list_of_sites_by_type(['network_dashboard_both', 'network_dashboard_sending'], 'post_ids');

        foreach( $sites as $site ) {
            if ( 'yes' === get_post_meta( $site, 'send_activity_log', true ) ) {
                $site_vars = Site_Link_System::get_site_connection_vars( $site );

                $args = [
                    'method' => 'POST',
                    'body' => [
                        'transfer_token' => $site_vars['transfer_token'],
                        'data' => $data
                    ]
                ];
                $response = wp_remote_post( 'https://' . $site_vars['url'] . '/wp-content/plugins/disciple-tools-network-dashboard/activity/log.php', $args );
                DT_Network_Activity_Log::insert_log( $data );

                dt_write_log('remote post');
                if ( ! is_wp_error( $response ) ) {
                    dt_write_log( json_decode( $response['body'], true ) );
                } else {
                    dt_write_log($response);
                    dt_write_log($site_vars);
                }
            }
        }
    }

    public static function insert_log( $data_array ) {

        global $wpdb;
        if ( ! isset( $wpdb->dt_movement_log ) ) {
            $wpdb->dt_movement_log = $wpdb->prefix . 'dt_movement_log';
        }
        if ( ! isset( $wpdb->dt_movement_log_meta ) ) {
            $wpdb->dt_movement_log_meta = $wpdb->prefix . 'dt_movement_log_meta';
        }
        if ( ! isset( $wpdb->dt_location_grid ) ) {
            $wpdb->dt_location_grid = $wpdb->prefix . 'dt_location_grid';
        }
        if ( ! isset( $wpdb->dt_location_grid_meta ) ) {
            $wpdb->dt_location_grid_meta = $wpdb->prefix . 'dt_location_grid_meta';
        }

        $process_status = [];
        $process_status['start'] = microtime(true); // @todo remove after development

        foreach( $data_array as $activity ) {

            $data = [
                'site_id' => '',
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
            if ( ! ( isset( $activity['category'] ) && ! empty( $activity['category'] ) ) ) {
                $process_status[] = [
                    'error' => 'no category found',
                    'data' => $activity
                ];
                continue;
            }
            $data['category'] = sanitize_text_field( wp_unslash( $activity['category'] ) );

            // LOCATION TYPE
            if ( ! ( isset( $activity['location_type'] ) && ! empty( $activity['location_type'] ) ) ) {
                $process_status[] = [
                    'error' => 'no location_type found',
                    'data' => $activity
                ];
                continue;
            }
            $location_type = sanitize_text_field( wp_unslash( $activity['location_type'] ) );

            // LOCATION VALUE
            if ( ! ( isset( $activity['location_value'] ) && ! empty( $activity['location_value'] ) ) ) {
                $process_status[] = [
                    'error' => 'no location value found',
                    'data' => $activity
                ];
                continue;
            }

            // PAYLOAD
            if ( ! isset( $activity['payload'] ) || empty( $activity['payload'] ) ) {
                $activity['payload'] = [];
            }
            $data['payload'] = self::recursive_sanitize_text_field( $activity['payload'] );

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
                    $ip_address = sanitize_text_field( wp_unslash( $activity['location_value']  ) );

                    $ipstack = new DT_Ipstack_API();
                    $response = $ipstack::geocode_ip_address($ip_address);

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

                        switch ($grid_response['level_name']) {
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
                        && isset( $activity['location_value']['lat'] ) && ! empty( $activity['location_value']['lat']  )
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
                            switch ($grid_response['level_name']) {
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
                default:
                    $process_status[] = [
                        'error' => 'did not find location_type. Must be ip, grid, lnglat, or complete.',
                        'data' => $activity
                    ];
                    continue 2;
            }

            $payload = $data['payload'];
            $data['payload'] = serialize( $data['payload'] );

            $data['hash'] = hash('sha256', serialize( $data ) );

            $data['timestamp'] = ( empty( $params['timestamp'] ) ) ? time() : $params['timestamp'];


            // test if duplicate
            $time = new DateTime();
            $time->modify('-30 minutes');
            $past_stamp = $time->format('U');
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

//            self::build_meta( $wpdb->insert_id, $payload, true ); // @todo evaluating strategy

            $process_status[] = 'Success: Created id ' . $wpdb->insert_id;
        }

        $process_status['stop'] = microtime(true);

        return $process_status;
    }

    public static function build_meta( $id, $payload = [], $force = false ) {
        global $wpdb;
        if ( ! $force ){
            $exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(meta_id) as count FROM $wpdb->dt_movement_log_meta WHERE ml_id = %d", $id ) );
            if ( $exists > 0 ){
                return;
            }
        }

        if ( empty( $payload ) ) {
            $payload = $wpdb->get_var( "SELECT payload FROM $wpdb->dt_movement_log WHERE id = %d", $id );
            $payload = maybe_unserialize( $payload );
            if ( empty( $payload ) ) {
                return;
            }
        }
        else {
            $payload = maybe_unserialize( $payload );
        }

        // $payload is intended to be a single dimensional array key/value. Accommodations for nested arrays, but is not recommended.
        foreach ( $payload as $key => $value ) {
            $wpdb->query($wpdb->prepare( "INSERT INTO $wpdb->dt_movement_log_meta (`meta_id`, `ml_id`, `meta_key`, `meta_value`) VALUES (NULL, %d, %s, %s);", $id, $key, $value )  );
        }
    }

    public static function recursive_sanitize_text_field( array $array ) : array {
        foreach ( $array as $key => &$value ) {
            if ( is_array( $value ) ) {
                $value = self::recursive_sanitize_text_field($value);
            }
            else {
                $value = sanitize_text_field( wp_unslash( $value ) );
            }
        }
        return $array;
    }
}