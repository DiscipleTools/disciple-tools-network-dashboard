
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
                        <div class="cell small-1 center info-bar-font">
                            Contacts: Red 
                        </div>
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


    // makeRequest( "POST", `network/maps/cluster/cluster_geojson`, { post_type: post_type, status: null} )
    //     .then(data=>{
    //         console.log(data)
    //
    //
    //
    //         set_info_boxes()
    //         function set_info_boxes() {
    //             let map_wrapper = jQuery('#map-wrapper')
    //             jQuery('.legend').css( 'width', map_wrapper.innerWidth() - 20 )
    //             jQuery( window ).resize(function() {
    //                 jQuery('.legend').css( 'width', map_wrapper.innerWidth() - 20 )
    //             });
    //         }
    //
    //
    //
    //
    //
    //         jQuery('#status').on('change', function() {
    //             window.current_status = jQuery('#status').val()
    //             makeRequest( "POST", `network/maps/cluster/cluster_geojson`, { post_type: post_type, status: window.current_status} )
    //                 .then(data=> {
    //                     clear_layer()
    //                     load_layer( data )
    //                 })
    //         })
    //
    //         function clear_layer() {
    //             map.removeLayer( 'clusters' )
    //             map.removeLayer( 'cluster-count' )
    //             map.removeLayer( 'unclustered-point' )
    //             map.removeSource( post_type )
    //         }
    //
    //         function load_layer( geojson ) {
    //             map.addSource(post_type, {
    //                 type: 'geojson',
    //                 data: geojson,
    //                 cluster: true,
    //                 clusterMaxZoom: 14,
    //                 clusterRadius: 50
    //             });
    //             map.addLayer({
    //                 id: 'clusters',
    //                 type: 'circle',
    //                 source: post_type,
    //                 filter: ['has', 'point_count'],
    //                 paint: {
    //                     'circle-color': [
    //                         'step',
    //                         ['get', 'point_count'],
    //                         '#51bbd6',
    //                         100,
    //                         '#f1f075',
    //                         750,
    //                         '#f28cb1'
    //                     ],
    //                     'circle-radius': [
    //                         'step',
    //                         ['get', 'point_count'],
    //                         20,
    //                         100,
    //                         30,
    //                         750,
    //                         40
    //                     ]
    //                 }
    //             });
    //             map.addLayer({
    //                 id: 'cluster-count',
    //                 type: 'symbol',
    //                 source: post_type,
    //                 filter: ['has', 'point_count'],
    //                 layout: {
    //                     'text-field': '{point_count_abbreviated}',
    //                     'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
    //                     'text-size': 12
    //                 }
    //             });
    //             map.addLayer({
    //                 id: 'unclustered-point',
    //                 type: 'circle',
    //                 source: post_type,
    //                 filter: ['!', ['has', 'point_count']],
    //                 paint: {
    //                     'circle-color': '#11b4da',
    //                     'circle-radius':12,
    //                     'circle-stroke-width': 1,
    //                     'circle-stroke-color': '#fff'
    //                 }
    //             });
    //             map.on('click', 'clusters', function(e) {
    //                 var features = map.queryRenderedFeatures(e.point, {
    //                     layers: ['clusters']
    //                 });
    //
    //                 var clusterId = features[0].properties.cluster_id;
    //                 map.getSource(post_type).getClusterExpansionZoom(
    //                     clusterId,
    //                     function(err, zoom) {
    //                         if (err) return;
    //
    //                         map.easeTo({
    //                             center: features[0].geometry.coordinates,
    //                             zoom: zoom
    //                         });
    //                     }
    //                 );
    //             })
    //             map.on('click', 'unclustered-point', function(e) {
    //
    //                 let content = jQuery('#geocode-details-content')
    //                 content.empty()
    //
    //                 jQuery('#geocode-details').show()
    //
    //                 jQuery.each( e.features, function(i,v) {
    //                     var address = v.properties.address;
    //                     var post_id = v.properties.post_id;
    //                     var name = v.properties.name
    //
    //                     content.append(`<p><a href="/trainings/${post_id}">${name}</a><br>${address}</p>`)
    //                 })
    //
    //             });
    //             map.on('mouseenter', 'clusters', function() {
    //                 map.getCanvas().style.cursor = 'pointer';
    //             });
    //             map.on('mouseleave', 'clusters', function() {
    //                 map.getCanvas().style.cursor = '';
    //             });
    //         }
    //
    //         jQuery('.close-details').on('click', function() {
    //             jQuery('#geocode-details').hide()
    //         })
    //
    //     }).catch(err=>{
    //     console.log("error")
    //     console.log(err)
    // })
}
