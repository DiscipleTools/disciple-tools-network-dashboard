jQuery(document).ready(function(){
    console.log('network-maps-area.js file loaded')

    makeRequest('POST', 'network/maps/area',{'id': 'test'} )
        .done(function(data) {
            "use strict";
            let obj = dt_network_maps_area
            let chartDiv = jQuery('#chart')
            chartDiv.empty().html(`Area`)

            console.log( data )
            console.log(obj)
        })
})