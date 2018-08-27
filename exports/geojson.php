<?php
// @codingStandardsIgnoreLine
require( $_SERVER[ 'DOCUMENT_ROOT' ] . '/wp-load.php' ); // loads the wp framework when called
header('Content-type:application/json;charset=utf-8'); // add json header to response

// Test for required URL elements
if ( ! isset( $_GET['page'] ) || ! isset( $_GET['nonce']) ) {
    error_log('geojson.php: Missing required url variables: "page", "nonce"');
    ?>
    {
    "type": "FeatureCollection",
    "features": []
    }
    <?php
    return;
}

$page_id = $_GET['page'];

//  nonce check
if ( ! wp_verify_nonce( $_GET['nonce'], 'dt_location_map_'.$page_id ) ) {
    error_log('geojson.php: Invalid nonce');
    ?>
    {
    "type": "FeatureCollection",
    "features": []
    }
    <?php
    return;
}

// get geojson
global $wpdb;
$geojson = $wpdb->get_var($wpdb->prepare( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %s AND meta_key = 'geojson'", $page_id) );
if ( empty( $geojson ) ) {
    error_log('geojson.php: No geojson found for this page id');
    $geojson = "{}";
}


?>
{
    "type": "FeatureCollection",
    "features": [
        {
            "type": "Feature",
            "geometry":
            <?php print $geojson; ?>
        }
    ]
}