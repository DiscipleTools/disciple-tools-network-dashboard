<?php
// @codingStandardsIgnoreStart
require( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' ); // loads the wp framework when called
if ( ! isset( $_GET['map'] ) || ! isset( $_GET['value'] ) ) {
    error_log( 'Missing requirements.' );
    wp_die( 'missing either map or value parameters' );
}
$value = $_GET['value'];
$map_type = $_GET['map'];


?>

<!DOCTYPE html>
<html>
<head>
    <title>Map</title>
    <meta name="viewport" content="initial-scale=1.0">
    <meta charset="utf-8">
    <style>
        /* Always set the map height explicitly to define the size of the div
         * element that contains the map. */
        #map {
            height: 100%;
        }
        /* Optional: Makes the sample page fill the window. */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
<div id="map"></div>
<script>
    var map, marker, infoWindow;

    var CENTER = {lat: 39.89941, lng: -104.607};
    var MARKER = {lat: 39.89941, lng: -104.607};
    function initMap() {
        // Create a map in the usual way.
        map = new google.maps.Map(
            document.getElementById('map'), {center: CENTER, zoom: 3});

        // Create a marker. Markers behave smoothly with the beta renderer.
        marker = new google.maps.Marker({position: MARKER, map: map});

        // Create info window content.
        var content = document.createElement('div');
        content.textContent = 'new renderer ';
        var zoomInButton = document.createElement('button');
        zoomInButton.textContent = 'zoom in';
        content.appendChild(zoomInButton);

        // Create open an info window attached to the marker.
        infoWindow = new google.maps.InfoWindow({content: content});
        infoWindow.open(map, marker);

        // When the zoom-in button is clicked, zoom in and pan to the Opera House.
        // The zoom and pan animations are smoother with the new renderer.
        zoomInButton.onclick = function() {
            map.setZoom(Math.max(15, map.getZoom() + 1));
            map.panTo(MARKER);
        };

        map.data.loadGeoJson('https://mu-zume/wp-content/plugins/disciple-tools-network-dashboard/ui/geojson.php?map=<?php echo $map_type; ?><?php
        if ( is_array( $value ) ) {
            foreach ( $value as $key => $item ) {
                echo '&value['.$key.']=' . $item;
            }
        } else {
            echo '&value=' . $value;
        }
        ?>');
    }
</script>
<!-- Working single: https://dashboard.mu-zume/wp-content/plugins/disciple-tools-network-dashboard/ui/map.php?map=single&value=5411363 -->
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=<?php echo dt_get_option( 'map_key' ) ?>&callback=initMap">
</script>
</body>
</html>

