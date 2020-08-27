jQuery(document).ready(function($) {

    if('/network/activity/livefeed' === window.location.pathname || '/network/activity/livefeed/' === window.location.pathname) {
        console.log(dtDashboardActivity)
        write_livefeed()
    }
    if('/network/activity/map' === window.location.pathname || '/network/activity/map/' === window.location.pathname) {
        console.log(dtDashboardActivity)
        write_map()
    }
    if('/network/activity/stats' === window.location.pathname || '/network/activity/stats/' === window.location.pathname) {
        console.log(dtDashboardActivity)
        write_stats()
    }

})

function write_livefeed(){
    "use strict";
    let obj = dtDashboardActivity
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`
        <style>
            #chart {
                height: ${window.innerHeight - 100}px !important;
                position:relative;
            }
            #chart-inner {
                height: ${window.innerHeight - 100}px !important;
            }
            #individual-activity {
                height: ${window.innerHeight - 250}px !important;
                overflow: scroll;
            }
            #collective-activity {
                height: ${window.innerHeight - 250}px !important;
                overflow: scroll;
            }
        </style>
        <div id="chart-inner">
            <span class="section-header">Live Feed</span>
            <hr style="max-width:100%;">
            <div class="grid-x grid-padding-x grid-padding-y">
              <div class="cell medium-6">
                <strong>${obj.settings.site_profile.partner_name} Activity</strong><br>
                <hr>
                <span class="loading-spinner active"></span>
                <div id="individual-activity"></div>
              </div>
              <div class="cell medium-6">
                <strong>Collective Network Activity</strong><br>
                <hr>
                <span class="loading-spinner active"></span>
                <div id="collective-activity"></div>
              </div>
            </div>
        </div>
  `);

    makeRequest('POST', obj.settings.livefeed_rest_url, {}, obj.settings.rest_base_url )
        .then(data => {
            console.log(data)
            let individual_list = $('#individual-activity')
            $.each(data, function(i,v){
                if ( v.site_id === obj.settings.site_profile.partner_id ){
                    individual_list.append(v.message + '<br>')
                }
            })
            let collective_list = $('#collective-activity')
            $.each(data, function(i,v){
                collective_list.append(v.message + '<br>')
            })

            $('.loading-spinner').removeClass('active')
        })



}

function write_map(){
    "use strict";
    let obj = dtDashboardActivity
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`
        <style>
            /**
            Custom Styles
             */
            .blessing {
                background-color: #21336A;
                border: 1px solid white;
                color: white;
                font-weight: bold;
                margin:0;
            }
            .great-blessing {
                background-color: #2CACE2;
                border: 1px solid white;
                color: white;
                font-weight: bold;
                margin:0;
            }
            .greater-blessing {
                background-color: #90C741;
                border: 1px solid white;
                color: white;
                font-weight: bold;
                margin:0;
            }
            .greatest-blessing {
                background-color: #FAEA38;
                border: 1px solid white;
                color: #21336A;
                font-weight: bold;
                margin:0;
            }
            .blessing:hover {
                border: 1px solid #21336A;
            }
            .great-blessing:hover {
                border: 1px solid #21336A;
                background-color: #2CACE2;
            }
            .greater-blessing:hover {
                border: 1px solid #21336A;
                background-color: #90C741;
            }
            .greatest-blessing:hover {
                border: 1px solid #21336A;
                background-color: #FAEA38;
                color: #21336A;
            }
            .filtered {
                background-color: lightgrey;
                color: white;
            }
            .filtered:hover {
                background-color: lightgrey;
                border: 1px solid #21336A;
                color: white;
            }
            #activity-list {
                font-size:.7em;
                list-style-type:none;
            }
            #map-loader {
                position: absolute;
                top:40%;
                left:50%;
                z-index: 20;
            }
            #map-header {
                position: absolute;
                top:10px;
                left:10px;
                z-index: 20;
                background-color: white;
                padding:1em;
                opacity: 0.8;
                border-radius: 5px;
            }
            .center-caption {
                font-size:.8em;
                text-align:center;
                color:darkgray;
            }
            .caption {
                font-size:.8em;
                color:darkgray;
                padding-bottom:1em;
            }
        </style>
        <span class="section-header">Feed Map</span>
        <hr style="max-width:100%;">
        <div class="grid-x">
                <div class="cell medium-8">
                    <div id="dynamic-styles"></div>
                    <div id="map-wrapper">
                        <div id='map'></div>
                    </div>
                </div>
                <div class="cell medium-4 padding-1">
                    <!-- Activity List -->
                    <div id="activity-wrapper">
                        <ul id="activity-list"></ul>
                    </div>
                </div>
            </div>
        `);

    let blessing_button = jQuery('#blessing-button')
    let great_blessing_button = jQuery('#great-blessing-button')
    let greater_blessing_button = jQuery('#greater-blessing-button')
    let greatest_blessing_button = jQuery('#greatest-blessing-button')

    window.blessing = 'visible'
    window.great_blessing = 'visible'
    window.greater_blessing = 'visible'
    window.greatest_blessing = 'visible'

    window.refresh_timer = ''
    function set_timer() {
        clear_timer()
        window.refresh_timer = setTimeout(function(){
            get_points( )
        }, 10000);
    }
    function clear_timer() {
        clearTimeout(window.refresh_timer)
    }

    let tz_select = jQuery('#timezone-select')

    let dynamic_styles = jQuery('#dynamic-styles')
    dynamic_styles.empty().html(`
        <style>
            #map-wrapper {
                height: ${window.innerHeight - 100}px !important;
                position:relative;
            }
            #map {
                height: ${window.innerHeight - 100}px !important;
            }
            #activity-wrapper {
                height: ${window.innerHeight - 350}px !important;
                overflow: scroll;
            }
        </style>
     `)

    mapboxgl.accessToken = obj.settings.map_key;
    var map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/light-v10',
        center: [-30, 20],
        minZoom: 1,
        maxZoom: 8,
        zoom: 1
    });

    // disable map rotation using right click + drag
    map.dragRotate.disable();
    map.touchZoomRotate.disableRotation();

    // load sources
    map.on('load', function () {
        let spinner = jQuery('#spinner')
        spinner.show()
        get_points( )
    })
    map.on('zoomstart', function(){
        clear_timer()
    })
    map.on('zoomend', function(){
        set_timer()
    })
    map.on('dragstart', function(){
        clear_timer()
    })
    map.on('dragend', function(){
        set_timer()
    })

    tz_select.on('change', function() {
        let tz = tz_select.val()
        get_points( tz )

        jQuery('#timezone-changer').foundation('close');
        jQuery('#timezone-current').html(tz);
    })

    function get_points( tz ) {
        if ( ! tz ) {
            tz = tz_select.val()
        }
        makeRequest('POST', obj.settings.points_rest_url, { timezone_offset: tz }, obj.settings.rest_base_url )
            .then(points => {
                load_layer( points )
                load_list( points )
            })
        set_timer()
    }

    function load_layer( points ) {
        var blessing = map.getLayer('blessing');
        if(typeof blessing !== 'undefined') {
            map.removeLayer( 'blessing' )
        }
        var greatBlessing = map.getLayer('greatBlessing');
        if(typeof greatBlessing !== 'undefined') {
            map.removeLayer( 'greatBlessing' )
        }
        var greaterBlessing = map.getLayer('greaterBlessing');
        if(typeof greaterBlessing !== 'undefined') {
            map.removeLayer( 'greaterBlessing' )
        }
        var greatestBlessing = map.getLayer('greatestBlessing');
        if(typeof greatestBlessing !== 'undefined') {
            map.removeLayer( 'greatestBlessing' )
        }
        var mapSource= map.getSource('pointsSource');
        if(typeof mapSource !== 'undefined') {
            map.removeSource( 'pointsSource' )
        }
        map.addSource('pointsSource', {
            'type': 'geojson',
            'data': points
        });
        map.addLayer({
            id: 'blessing',
            type: 'circle',
            source: 'pointsSource',
            paint: {
                'circle-radius': {
                    'base': 4,
                    'stops': [
                        [3, 4],
                        [4, 6],
                        [5, 8],
                        [6, 10],
                        [7, 12],
                        [8, 14],
                    ]
                },
                'circle-color': '#21336A'
            },
            filter: ["==", "category", "blessing" ]
        });
        map.setLayoutProperty('blessing', 'visibility', window.blessing);

        map.addLayer({
            id: 'greatBlessing',
            type: 'circle',
            source: 'pointsSource',
            paint: {
                'circle-radius': {
                    'base': 6,
                    'stops': [
                        [3, 6],
                        [4, 8],
                        [5, 10],
                        [6, 12],
                        [7, 14],
                        [8, 16],
                    ]
                },
                'circle-color': '#2CACE2'
            },
            filter: ["==", "category", "great_blessing" ]
        });
        map.setLayoutProperty('greatBlessing', 'visibility', window.great_blessing);

        map.addLayer({
            id: 'greaterBlessing',
            type: 'circle',
            source: 'pointsSource',
            paint: {
                'circle-radius': {
                    'base': 8,
                    'stops': [
                        [3, 8],
                        [4, 12],
                        [5, 16],
                        [6, 20],
                        [7, 24],
                        [8, 28],
                    ]
                },
                'circle-color': '#90C741'
            },
            filter: ["==", "category", "greater_blessing" ]
        });
        map.setLayoutProperty('greaterBlessing', 'visibility', window.greater_blessing);

        map.addLayer({
            id: 'greatestBlessing',
            type: 'circle',
            source: 'pointsSource',
            paint: {
                'circle-radius': {
                    'base': 10,
                    'stops': [
                        [3, 10],
                        [4, 14],
                        [5, 18],
                        [6, 22],
                        [7, 26],
                        [8, 30],
                    ]
                },
                'circle-color': '#FAEA38'
            },
            filter: ["==", "category", "greatest_blessing" ]
        });
        map.setLayoutProperty('greatestBlessing', 'visibility', window.greatest_blessing);

        // @link https://docs.mapbox.com/mapbox-gl-js/example/popup-on-hover/
        var popup = new mapboxgl.Popup({
            closeButton: false,
            closeOnClick: false
        });

        map.on('mouseenter', 'blessing', function (e) {
            mouse_enter( e )
        });
        map.on('mouseleave', 'blessing', function (e) {
            mouse_leave( e )
        });
        map.on('mouseenter', 'greatBlessing', function (e) {
            mouse_enter( e )
        });
        map.on('mouseleave', 'greatBlessing', function (e) {
            mouse_leave( e )
        });
        map.on('mouseenter', 'greaterBlessing', function (e) {
            mouse_enter( e )
        });
        map.on('mouseleave', 'greaterBlessing', function (e) {
            mouse_leave( e )
        });
        map.on('mouseenter', 'greatestBlessing', function (e) {
            mouse_enter( e )
        });
        map.on('mouseleave', 'greatestBlessing', function (e) {
            mouse_leave( e )
        });

        function mouse_enter( e ) {
            map.getCanvas().style.cursor = 'pointer';

            var coordinates = e.features[0].geometry.coordinates.slice();
            var description = e.features[0].properties.note;

            while (Math.abs(e.lngLat.lng - coordinates[0]) > 180) {
                coordinates[0] += e.lngLat.lng > coordinates[0] ? 360 : -360;
            }

            popup
                .setLngLat(coordinates)
                .setHTML(description)
                .addTo(map);
        }
        function mouse_leave( e ) {
            map.getCanvas().style.cursor = '';
            popup.remove();
        }

        jQuery('#map-loader').hide()
    }

    function load_list( points ) {
        let list_container = jQuery('#activity-list')
        list_container.empty()
        let filter_blessing = blessing_button.hasClass('filtered')
        let filter_great_blessing = great_blessing_button.hasClass('filtered')
        let filter_greater_blessing = greater_blessing_button.hasClass('filtered')
        let filter_greatest_blessing = greatest_blessing_button.hasClass('filtered')
        jQuery.each( points.features, function(i,v){
            let visible = 'block'
            if ( 'blessing' === v.properties.category && filter_blessing ) {
                visible = 'none'
            }
            if ( 'great_blessing' === v.properties.category && filter_great_blessing ) {
                visible = 'none'
            }
            if ( 'greater_blessing' === v.properties.category && filter_greater_blessing ) {
                visible = 'none'
            }
            if ( 'greatest_blessing' === v.properties.category && filter_greatest_blessing ) {
                visible = 'none'
            }

            if ( v.properties.note ) {
                list_container.append(`<li class="${v.properties.category}-activity" style="display:${visible}"><strong>${v.properties.time}</strong> - ${v.properties.note}</li>`)
            }
        })
        jQuery('#list-loader').hide()

        jQuery('.blessing-count').empty().append(points.counts.blessing)
        jQuery('.great-blessing-count').empty().append(points.counts.great_blessing)
        jQuery('.greater-blessing-count').empty().append(points.counts.greater_blessing)
        jQuery('.greatest-blessing-count').empty().append(points.counts.greatest_blessing)

    }

    // Filter button controls
    blessing_button.on('click', function(){
        if ( blessing_button.hasClass('filtered') ) {
            blessing_button.removeClass('filtered')
            jQuery('.blessing-activity').show()
            window.blessing = 'visible'
            map.setLayoutProperty('blessing', 'visibility', 'visible');
        } else {
            blessing_button.addClass('filtered')
            jQuery('.blessing-activity').hide()
            window.blessing = 'none'
            map.setLayoutProperty('blessing', 'visibility', 'none');
        }
    })
    great_blessing_button.on('click', function(){
        if ( great_blessing_button.hasClass('filtered') ) {
            great_blessing_button.removeClass('filtered')
            jQuery('.great_blessing-activity').show()
            window.great_blessing = 'visible'
            map.setLayoutProperty('greatBlessing', 'visibility', 'visible');
        } else {
            great_blessing_button.addClass('filtered')
            jQuery('.great_blessing-activity').hide()
            window.great_blessing = 'none'
            map.setLayoutProperty('greatBlessing', 'visibility', 'none');
        }
    })
    greater_blessing_button.on('click', function(){
        if ( greater_blessing_button.hasClass('filtered') ) {
            greater_blessing_button.removeClass('filtered')
            jQuery('.greater_blessing-activity').show()
            window.greater_blessing = 'visible'
            map.setLayoutProperty('greaterBlessing', 'visibility', 'visible');
        } else {
            greater_blessing_button.addClass('filtered')
            jQuery('.greater_blessing-activity').hide()
            window.greater_blessing = 'none'
            map.setLayoutProperty('greaterBlessing', 'visibility', 'none');
        }
    })
    greatest_blessing_button.on('click', function(){
        if ( greatest_blessing_button.hasClass('filtered') ) {
            greatest_blessing_button.removeClass('filtered')
            jQuery('.greatest_blessing-activity').show()
            window.greatest_blessing = 'visible'
            map.setLayoutProperty('greatestBlessing', 'visibility', 'visible');
        } else {
            greatest_blessing_button.addClass('filtered')
            jQuery('.greatest_blessing-activity').hide()
            window.greatest_blessing = 'none'
            map.setLayoutProperty('greatestBlessing', 'visibility', 'none');
        }
    })
}

function write_stats(){
    "use strict";
    let obj = dtDashboardActivity
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`
    <span class="section-header">Feed Stats</span>
    
    <hr style="max-width:100%;">
    <div class="grid-x grid-padding-x grid-padding-y">
      <div class="cell">
      <span class="section-header">Active Totals for All Sites</span><br>
        <div class="grid-x callout">
          <div class="medium-3 cell center">
            <h4>Contacts<br></h4>
          </div>
          <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
            <h4>Groups<br></h4>
          </div>
          <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
            <h4>Users<br></h4>
          </div>
          <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
            <h4>Countries<br></h4>
          </div>
        </div>
      </div>
    </div>
    
    <hr style="max-width:100%;">
    
    <div class="grid-x grid-padding-x grid-padding-y">
      <div class="cell">
      <span class="section-header">Active Totals for All Sites</span><br>
        <div class="grid-x callout">
          <div class="medium-3 cell center">
            <h4>Contacts<br></h4>
          </div>
          <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
            <h4>Groups<br></h4>
          </div>
          <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
            <h4>Users<br></h4>
          </div>
          <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
            <h4>Countries<br></h4>
          </div>
        </div>
      </div>
    </div>
    
  `);
}