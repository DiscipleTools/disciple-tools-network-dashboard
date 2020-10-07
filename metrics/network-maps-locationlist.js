jQuery(document).ready(function(){
    console.log('network-maps-locationlist.js file loaded')

    makeRequest('POST', 'network/maps/locationlist',{'id': 'test'} )
        .done(function(data) {
            "use strict";
            let obj = dt_network_maps_locationlist
            let chartDiv = jQuery('#chart')
            chartDiv.empty().html(`Location List`)

            console.log( data )
            console.log(obj)
        })
})