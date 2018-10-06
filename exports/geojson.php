<?php
// @codingStandardsIgnoreStart
require( $_SERVER[ 'DOCUMENT_ROOT' ] . '/wp-load.php' ); // loads the wp framework when called
global $wpdb;

header( 'Content-type:application/json;charset=utf-8' ); // add json header to response
header( 'Access-Control-Allow-Origin: *' );
header( 'Access-Control-Allow-Methods: GET, POST, HEAD, OPTIONS' );
header( 'Access-Control-Allow-Credentials: true' );
header( 'Access-Control-Expose-Headers: Link', false );

if ( ! isset( $_GET['map'] ) || ! isset( $_GET['value'] ) ) {
    error_log( 'Missing requirements.' );
    empty_json();
    exit;
}

$value = $_GET['value'];
$map_type = $_GET['map'];

switch ( $map_type ) {

    case 'single':

        $geojson = $wpdb->get_var( $wpdb->prepare( "SELECT geoJSON FROM dt_geonames_polygons WHERE geonameid = %d", $value ) );
        if ( empty( $geojson ) ) {
            dt_write_log( 'geojson.php: No geojson found for this page id' );
            empty_json();
            exit;
        }
        ?>{"type": "FeatureCollection","features": [{"type": "Feature","geometry": <?php print $geojson; ?>}]}<?php
        /* Working single: https://dashboard.mu-zume/wp-content/plugins/disciple-tools-network-dashboard/ui/map.php?map=single&value=5411363 */
        break;

    case 'state':
        $geojson = $wpdb->get_results( $wpdb->prepare( "SELECT geoJSON FROM dt_geonames as g JOIN dt_geonames_polygons as gp ON g.geonameid=gp.geonameid WHERE country_code = %s and admin1_code = %s", $value['country_code'], $value['admin1_code'] ), ARRAY_A );
        if ( empty( $geojson ) ) {
            dt_write_log( 'geojson.php: No geojson found for this page id' );
            empty_json();
            exit;
        }

        ?>{"type": "FeatureCollection","features": [<?php
            $i = 0;
$html = '';
foreach ( $geojson as $geometry ) {
    if ( 0 != $i ) {
        $html .= ',';
    }
    $html .= '{"type": "Feature","geometry": ';
    $html .= $geometry['geoJSON'];
    $html .= '}';
    $i++;
}
            echo $html . ']}';?>
        <?php
        /* working : https://dashboard.mu-zume/wp-content/plugins/disciple-tools-network-dashboard/ui/map.php?map=state&value[country_code]=US&value[admin1_code]=CO */
        break;
    case 'country':
        $geojson = $wpdb->get_results( $wpdb->prepare( "SELECT geoJSON FROM dt_geonames as g JOIN dt_geonames_polygons as gp ON g.geonameid=gp.geonameid WHERE country_code = %s and feature_code = 'ADM1' LIMIT 2", $value ), ARRAY_A );
        if ( empty( $geojson ) ) {
            dt_write_log( 'geojson.php: No geojson found for this page id' );
            empty_json();
            exit;
        }

        ?>{"type": "FeatureCollection","features": [<?php
        $i = 0;
        $html = '';
foreach ( $geojson as $geometry ) {
    if ( 0 != $i ) {
        $html .= ',';
    }
    $html .= '{"type": "Feature","geometry": ';
    $html .= $geometry['geoJSON'];
    $html .= '}';
    $i++;
}
        echo $html . ']}';

        break;

    case 'world':
        $geojson = $wpdb->get_results( "SELECT geoJSON FROM dt_geonames_polygons_low", ARRAY_A );
        dt_write_log( $geojson );
        if ( empty( $geojson ) ) {
            dt_write_log( 'geojson.php: No geojson found for this page id' );
            empty_json();
            exit;
        }

        $i = 0;
        $html = '{"type": "FeatureCollection","features": [';
        foreach ( $geojson as $geometry ) {
            if ( 0 != $i ) {
                $html .= ',';
            }
            $html .= '{"type": "Feature","geometry": ';
            $html .= $geometry['geoJSON'];
            $html .= '}';
            $i++;
        }
        $html .= ']}';
        echo $html;
        break;
    default:
        break;
}

function empty_json() {
    ?>{"type": "FeatureCollection","features": [{"type": "Feature","geometry": []}]}<?php
}