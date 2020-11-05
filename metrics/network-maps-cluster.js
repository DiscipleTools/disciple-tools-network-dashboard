
if('/network/maps/cluster' === window.location.pathname) {
    write_cluster( 'contact_settings' )
}

function write_cluster( settings ) {
    let obj = network_maps_cluster

    jQuery('#network_maps_cluster').prop('style', 'font-weight:900;')


    let chart = jQuery('#chart')
    let spinner = ' <span class="loading-spinner users-spinner active"></span> '

    chart.empty().html(`
            <style>
                    #map-wrapper {
                        position: relative;
                        height: ${window.innerHeight - 100}px; 
                        width:100%;
                    }
                    #map { 
                        position: absolute;
                        top: 0;
                        left: 0;
                        z-index: 1;
                        width:100%;
                        height: ${window.innerHeight - 100}px; 
                     }
                     #legend {
                        position: absolute;
                        top: 10px;
                        left: 10px;
                        z-index: 2;
                     }
                     #data {
                        word-wrap: break-word;
                     }
                    .legend {
                        background-color: #fff;
                        border-radius: 3px;
                        box-shadow: 0 1px 2px rgba(0,0,0,0.10);
                        font: 12px/20px 'Roboto', Arial, sans-serif;
                        padding: 10px;
                        opacity: .9;
                    }
                    .legend h4 {
                        margin: 0 0 10px;
                    }    
                    .legend div span {
                        border-radius: 50%;
                        display: inline-block;
                        height: 10px;
                        margin-right: 5px;
                        width: 10px;
                    }
                    #cross-hair {
                        position: absolute;
                        z-index: 20;
                        font-size:30px;
                        font-weight: normal;
                        top:50%;
                        left:50%;
                        display:none;
                        pointer-events: none;
                    }
                    #spinner {
                        position: absolute;
                        top:50%;
                        left:50%;
                        z-index: 20;
                        display:none;
                    }
                    .spinner-image {
                        width: 30px;
                    }
                    .info-bar-font {
                        font-size: 1.5em;
                        padding-top: 9px;
                    }
                    .border-left {
                        border-left: 1px lightgray solid;
                    }
                    #geocode-details {
                        position: absolute;
                        top: 100px;
                        right: 10px;
                        z-index: 2;
                    }
                    .geocode-details {
                        background-color: #fff;
                        border-radius: 3px;
                        box-shadow: 0 1px 2px rgba(0,0,0,0.10);
                        font: 12px/20px 'Roboto', Arial, sans-serif;
                        padding: 10px;
                        opacity: .9;
                        width: 300px;
                        display:none;
                    }
                    .close-details {
                        cursor:pointer;
                    }
                </style>
            <div id="map-wrapper">
                <div id='map'></div>
                <div id='legend' class='legend'>
                    <div class="grid-x grid-margin-x grid-padding-x">
                        
                    </div>
                </div>
                <div id="spinner">${spinner}</div>
                <div id="cross-hair">&#8982</div>
                <div id="geocode-details" class="geocode-details">
                   <span class="close-details" style="float:right;"><i class="fi-x"></i></span>
                    <hr style="margin:10px 5px;">
                    <div id="geocode-details-content"></div>
                </div>
            </div>
            `)

    mapboxgl.accessToken = obj.map_key;
    var map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/light-v10',
        center: [-98, 38.88],
        minZoom: 0,
        zoom: 0
    });

    // SET BOUNDS
    window.map_bounds_token = 'network_maps_cluster'
    window.map_start = get_map_start( window.map_bounds_token )
    if ( window.map_start ) {
        map.fitBounds( window.map_start, {duration: 0});
    }
    map.on('zoomend', function() {
        set_map_start( window.map_bounds_token, map.getBounds() )
    })
    map.on('dragend', function() {
        set_map_start( window.map_bounds_token, map.getBounds() )
    })
    // end set bounds

    map.on('load', function() {
        makeRequest( "POST", `network/maps/cluster`, { post_type: 'contacts', status: null} )
            .then(data=>{
                console.log('contacts')
                console.log(data)
                map.addSource('layer-source-contacts', {
                    type: 'geojson',
                    data: data,
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
            })
        makeRequest( "POST", `network/maps/cluster`, { post_type: 'groups', status: null} )
            .then(data=>{
                console.log('groups')
                console.log(data)
            })
        makeRequest( "POST", `network/maps/cluster`, { post_type: 'churches', status: null} )
            .then(data=>{
                console.log('churches')
                console.log(data)
            })
        makeRequest( "POST", `network/maps/cluster`, { post_type: 'users', status: null} )
            .then(data=>{
                console.log('users')
                console.log(data)
            })
    });

}
