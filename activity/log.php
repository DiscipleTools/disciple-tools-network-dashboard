<?php

if (defined('ABSPATH')) {
    exit;
}

function _dt_network_doing_it_wrong( string $message ) {
    header('Content-type: application/json');
    echo json_encode(['error' => $message]);
    exit();
}
if ( !function_exists( 'dt_write_log' ) ) {
    function dt_write_log( $log ) {
        if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }
}
if ( ! function_exists( 'recursive_sanitize_text_field') ) {
    function recursive_sanitize_text_field( array $array ) : array {
        foreach ( $array as $key => &$value ) {
            if ( is_array( $value ) ) {
                $value = recursive_sanitize_text_field($value);
            }
            else {
                $value = sanitize_text_field( wp_unslash( $value ) );
            }
        }
        return $array;
    }
}

/**
 * @link https://stackoverflow.com/questions/45421976/wordpress-rest-api-slow-response-time
 *       https://deliciousbrains.com/wordpress-rest-api-vs-custom-request-handlers/
 *
 * @version 1.0 Initialization
 */

define('DOING_AJAX', true);

//Tell WordPress to only load the basics
define('SHORTINIT', 1);

/**** LOAD NEEDED FILES *****/
if (!isset($_SERVER['DOCUMENT_ROOT'])) {
    _dt_network_doing_it_wrong('missing server info');
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'; //@phpcs:ignore
require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-includes/post.php'; //@phpcs:ignore
require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-includes/meta.php'; //@phpcs:ignore

if (!defined('WP_CONTENT_URL')) {
    define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
}

$mapping_path = ABSPATH . 'wp-content/themes/disciple-tools-theme/dt-mapping/';
if (file_exists($mapping_path . 'geocode-api/location-grid-geocoder.php')) {
    require_once( $mapping_path. 'geocode-api/location-grid-geocoder.php'); // Location grid geocoder
} else {
    _dt_network_doing_it_wrong('did not find geocoder file');
}
if (file_exists($mapping_path . 'geocode-api/ipstack-api.php')) {
    require_once( $mapping_path. 'geocode-api/ipstack-api.php'); // Location grid geocoder
} else {
    _dt_network_doing_it_wrong('did not find ipstack file');
}
$theme_path = ABSPATH . 'wp-content/themes/disciple-tools-theme/';
if (file_exists($theme_path . 'dt-core/admin/site-link-post-type.php')) {
    require_once( $theme_path. 'dt-core/admin/site-link-post-type.php'); // Location grid geocoder
} else {
    _dt_network_doing_it_wrong('did not find site linking file');
}

// add dt database tables
global $wpdb;
$wpdb->dt_movement_log = $wpdb->prefix . 'dt_movement_log';
$wpdb->dt_location_grid = $wpdb->prefix . 'dt_location_grid';
$wpdb->dt_location_grid_meta = $wpdb->prefix . 'dt_location_grid_meta';

/************* EXAMPLE PAYLOAD **********************************/
$params = [
    'transfer_token' => Site_Link_System::create_transfer_token_for_site( Site_Link_System::instance()->get_site_key_by_id(8739) ),
    'data' => [
        [
            'site_id' => hash('sha256', 'site_id1'.rand ( 0 , 19999 )),
            'action' => 'action',
            'category' => 'ip',
            'location_type' => 'ip', // ip, grid, lnglat
            'location_value' => '184.96.211.187',
            'payload' => [
                'initials' => 'CC',
                'group_size' => '3',
                'country' => 'United States',
                'language' => 'en',
                'note' => 'This is the full note'.time()
            ],
            'timestamp' => ''
        ],
        [
            'site_id' => hash('sha256', 'site_id5'.rand ( 0 , 19999 )),
            'action' => 'action',
            'category' => 'grid',
            'location_type' => 'grid', // ip, grid, lnglat
            'location_value' => '100364508',
            'payload' => [
                'initials' => 'CC',
                'group_size' => '3',
                'country' => 'United States',
                'language' => 'en',
                'note' => 'This is the full note'.time()
            ],
            'timestamp' => ''
        ],
        [
            'site_id' => hash('sha256', 'site_id2'.rand ( 0 , 19999 )),
            'action' => 'action',
            'category' => 'lnglat',
            'location_type' => 'lnglat', // ip, grid, lnglat
            'location_value' => [
                'lng' => '-104.968',
                'lat' => '39.7075',
                'level' => 'admin2',
            ],
            'payload' => [
                'initials' => 'CC',
                'group_size' => '3',
                'country' => 'Slovenia',
                'language' => 'en',
                'note' => 'This is the full note'.time()
            ],
            'timestamp' => ''
        ],
        [
            'site_id' => hash('sha256', 'site_id3'.rand ( 0 , 19999 )),
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
            'timestamp' => ''
        ],
    ]
];
/************* END EXAMPLE PAYLOAD *******************************/

// Validate Transfer Token
if ( ! Site_Link_System::verify_transfer_token( $params['transfer_token'] ) ) {
    _dt_network_doing_it_wrong('transfer token failed');
}

// Qualify data payload
if ( ! ( isset( $params['data'] ) && ! empty( $params['data'] ) && is_array( $params['data'] ) ) ) {
    _dt_network_doing_it_wrong('no data id found or data is not an array');
}

/**
 * LOOP THROUGH ACTIVITY ELEMENTS
 */
$process_status = [];
$process_status['start'] = microtime(true);

foreach( $params['data'] as $index => $activity ) {

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
        _dt_network_doing_it_wrong('no site id found');
    }
    $data['site_id'] = sanitize_text_field( wp_unslash( $activity['site_id'] ) );

    // ACTION
    if ( ! ( isset( $activity['action'] ) && ! empty( $activity['action'] ) ) ) {
        _dt_network_doing_it_wrong('no action found');
    }
    $data['action'] = sanitize_text_field( wp_unslash( $activity['action'] ) );

    // CATEGORY
    if ( ! ( isset( $activity['category'] ) && ! empty( $activity['category'] ) ) ) {
        _dt_network_doing_it_wrong('no category found');
    }
    $data['category'] = sanitize_text_field( wp_unslash( $activity['category'] ) );

    // LOCATION TYPE
    if ( ! ( isset( $activity['location_type'] ) && ! empty( $activity['location_type'] ) ) ) {
        _dt_network_doing_it_wrong('no location type found');
    }
    $location_type = sanitize_text_field( wp_unslash( $activity['location_type'] ) );

    // LOCATION VALUE
    if ( ! ( isset( $activity['location_value'] ) && ! empty( $activity['location_value'] ) ) ) {
        _dt_network_doing_it_wrong('no location value found');
    }

    // PAYLOAD
    if ( ! isset( $activity['payload'] ) || empty( $activity['payload'] ) ) {
        $activity['payload'] = [];
    }
    $data['payload'] = recursive_sanitize_text_field( $activity['payload'] );

    // PREPARE LOCATION DATA
    switch ( $location_type ) {
        case 'ip':  /* @param string expects string containing ip address */

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
            }

            break;
        case 'lnglat': /* @param array expects associative array containing (lng, lat, level) strings */

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

            $geocoder = new Location_Grid_Geocoder();
            $grid_response = $geocoder->get_grid_id_by_lnglat( $data['lng'], $data['lat'], null, $data['level'] );
            if ( ! empty( $grid_response ) ) {
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
            }

            break;
        case 'complete': /* @param array expects array with (lng, lat, level, label, grid_id) strings */

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

//    $data['lng'] = '35';
//
//
//    $data['lat'] = '35';
//
//
//    $data['level'] = 'country';
//
//
//    $data['label'] = 'Ocean';
//
//
//    $data['grid_id'] = time();


    $data['payload'] = serialize( $activity['payload'] );


    $data['hash'] = hash('sha256', serialize( $data ) );


    $data['timestamp'] = ( empty( $params['timestamp'] ) ) ? time() : $params['timestamp'];


    // test if duplicate
    global $wpdb;
    $time = new DateTime();
    $time->modify('-30 minutes');
    $past_stamp = $time->format('U');
    $results = $wpdb->get_col( $wpdb->prepare( "SELECT hash FROM $wpdb->dt_movement_log WHERE timestamp > %d", $past_stamp ) );
    if ( array_search( $data['hash'], $results ) !== false ) {
        _dt_network_doing_it_wrong('Duplicate');
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

    $process_status[] = 'Success: Created id ' . $wpdb->insert_id;
}

$process_status['stop'] = microtime(true);

header('Content-type: application/json');
echo json_encode($process_status);
exit();