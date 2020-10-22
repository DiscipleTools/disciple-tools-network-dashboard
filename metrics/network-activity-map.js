jQuery(document).ready(function(){
    let chartDiv = jQuery('#chart')
    let spinner = '<span class="loading-spinner active"></span>'

    // add highlight to menu
    jQuery('#network_activity_map').prop('style', 'font-weight:900;')

    // write page layout with spinners
    chartDiv.empty().html(`
            <style>
                #activity-list-wrapper {
                    height: ${window.innerHeight - 300}px !important;
                    overflow: scroll;
                }
                #activity-list-wrapper li {
                    font-size:.8em;
                }
                #activity-list-wrapper h2 {
                    font-size:1.2em;
                    font-weight:bold;
                }
            </style>
            <span class="section-header float-left" >Activity Map</span><span class="float-right"><a data-open="activity-filter-modal">modify filter</a></span>
                <hr style="max-width:100%;">
                <div class="grid-x grid-padding-x">
                <div class="medium-9 cell">
                    <div id="map-wrapper">
                        <div id='map'>${spinner}</div>
                    </div>
                </div>
                <div class="medium-3 cell">
                    <div class="grid-x">
                        <div class="cell">
                            <div id="activity-list-wrapper"></div>
                        </div>
                    </div>
                </div>
                </div>
                <div class="reveal" id="activity-filter-modal" data-v-offset="10" data-reveal>
                    <h1></h1>
                    <div id="activity-filter-wrapper"></div>
                    <button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <hr style="max-width:100%;">
                 <div class="grid-x callout">
                  <div class="medium-3 cell center">
                    <h4>Locations<br><span id="has_location">${spinner}</span></h4>
                  </div>
                  <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                    <h4>No Location<br><span id="no_location">${spinner}</span></h4>
                  </div>
                </div>
                <hr style="max-width:100%;">
                <div><button class="button clear" onclick="reset()">reset data</button> <span class="reset-spinner"></span></div>
            `)

    new Foundation.Reveal(jQuery('#activity-filter-modal'))

    window.load_activity_filter()

    function load_map_geojson(){
        if ( typeof window.activity_filter === 'undefined' ){
            window.activity_filter = { 'end': '-7 days' }
        }
        // call for data
        makeRequest('POST', 'network/activity/map', { 'filters': window.activity_filter } )
            .done( data => {
                "use strict";
                window.activity_geojson = data

                write_cluster_map()

                jQuery('#no_location').html( window.activity_geojson.no_location )
                jQuery('#has_location').html( window.activity_geojson.has_location )
            })
    }
    load_map_geojson()

    function write_cluster_map(){
        jQuery('#map').empty() // remove spinner

        mapboxgl.accessToken = network_base_script.map_key;
        var map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/light-v10',
            center: [-98, 38.88],
            minZoom: 0,
            zoom: 0
        });

        map.on('load', function() {
            map.addSource('layer-source-contacts', {
                type: 'geojson',
                data: window.activity_geojson,
                cluster: true,
                clusterMaxZoom: 14,
                clusterRadius: 50
            });
            map.addLayer({
                id: 'clusters',
                type: 'circle',
                source: 'layer-source-contacts',
                filter: ['has', 'point_count'],
                paint: {
                    'circle-color': [
                        'step',
                        ['get', 'point_count'],
                        '#51bbd6',
                        100,
                        '#f1f075',
                        750,
                        '#f28cb1'
                    ],
                    'circle-radius': [
                        'step',
                        ['get', 'point_count'],
                        20,
                        100,
                        30,
                        750,
                        40
                    ]
                }
            });
            map.addLayer({
                id: 'cluster-count-contacts',
                type: 'symbol',
                source: 'layer-source-contacts',
                filter: ['has', 'point_count'],
                layout: {
                    'text-field': '{point_count_abbreviated}',
                    'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
                    'text-size': 12
                }
            });
            map.addLayer({
                id: 'unclustered-point-contacts',
                type: 'circle',
                source: 'layer-source-contacts',
                filter: ['!', ['has', 'point_count']],
                paint: {
                    'circle-color': '#11b4da',
                    'circle-radius':12,
                    'circle-stroke-width': 1,
                    'circle-stroke-color': '#fff'
                }
            });


        });

        map.on('zoomend', function(e){
            window.current_bounds = map.getBounds()
            window.activity_filter.boundary = { 'n_lat': window.current_bounds._ne.lat, 's_lat': window.current_bounds._sw.lat, 'e_lng': window.current_bounds._ne.lng, 'w_lng': window.current_bounds._sw.lng}
            window.load_activity_filter()
        })
        map.on('dragend', function(e){
            window.current_bounds = map.getBounds()
            window.activity_filter.boundary = { 'n_lat': window.current_bounds._ne.lat, 's_lat': window.current_bounds._sw.lat, 'e_lng': window.current_bounds._ne.lng, 'w_lng': window.current_bounds._sw.lng}
            window.load_activity_filter()
        })
    }
})