<?php

if (defined('ABSPATH')) {
    exit;
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
    exit('missing server info');
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'; //@phpcs:ignore

if (!defined('WP_CONTENT_URL')) {
    define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
}

$mapping_url = ABSPATH . 'wp-content/themes/disciple-tools-theme/dt-mapping/';
if (file_exists($mapping_url . 'geocode-api/location-grid-geocoder.php')) {
    require_once( $mapping_url. 'geocode-api/location-grid-geocoder.php'); // Location grid geocoder
} else {
    echo json_encode(['error' => 'did not find geocoder file']);
    return;
}
if (file_exists($mapping_url . 'geocode-api/ipstack-api.php')) {
    require_once( $mapping_url. 'geocode-api/ipstack-api.php'); // Location grid geocoder
} else {
    echo json_encode(['error' => 'did not find ipstack file']);
    return;
}

// register global database
global $wpdb;
$wpdb->dt_movement_log = $wpdb->prefix . 'dt_movement_log';

require_once ('filters.php');

// @todo get params


$data = [
    'site_id' => '',
    'initials' => '',
    'action' => '',
    'category' => '',
    'count' => '',
    'lng' => '',
    'lat' => '',
    'level' => '',
    'label' => '',
    'grid_id' => '',
    'country' => '',
    'language' => '',
    'note' => '',
    'timestamp' => '',
    'hash' => '',
];

// set site_id
$data['site_id'] = hash('sha256', 'test');


$data['initials'] = 'CC';


$data['action'] = 'register';


$data['category'] = 'register';


$data['count'] = '1';


$data['lng'] = '35';


$data['lat'] = '35';


$data['level'] = 'country';


$data['label'] = 'Ocean';


$data['grid_id'] = time();


$data['country'] = 'Taugu';


$data['language'] = 'en';


$data['note'] = apply_filters('movement_log_note', $data['note'], $data );

// set hash
$data['hash'] = hash('sha256', serialize( $data ) );

// set timestamp
$data['timestamp'] = time();


// test if duplicate
global $wpdb;
$time = new DateTime();
$time->modify('-30 minutes');
$past_stamp = $time->format('U');
$results = $wpdb->get_col( $wpdb->prepare( "SELECT hash FROM $wpdb->dt_movement_log WHERE timestamp > %d",$past_stamp ) );
if ( array_search( $data['hash'], $results ) !== false ) {
    header('Content-type: application/json');
    echo json_encode([ 'error' => 'Duplicate']);
    exit();
}

// insert log record
$wpdb->query( $wpdb->prepare( "
    INSERT INTO $wpdb->dt_movement_log (
        site_id,
        initials,
        action,
        category,
        count,
        lng,
        lat,
        level,
        label,
        grid_id,
        country,
        language,
        note,
        timestamp,
        hash
    )
    VALUES (
            %s,
            %s,
            %s,
            %s,
            %d,
            %f,
            %f,
            %s,
            %s,
            %s,
            %s,
            %s,
            %s,
            %s,
            %s
            )",
    $data['site_id'],
    $data['initials'],
    $data['action'],
    $data['category'],
    $data['count'],
    $data['lng'],
    $data['lat'],
    $data['level'],
    $data['label'],
    $data['grid_id'],
    $data['country'],
    $data['language'],
    $data['note'],
    $data['timestamp'],
    $data['hash']
) );


header('Content-type: application/json');
echo json_encode($data);
exit();
