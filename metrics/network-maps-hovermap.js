jQuery(document).ready(function(){
    console.log('network-maps-hovermap.js file loaded')

    makeRequest('POST', 'network/maps/hovermap',{'id': 'test'} )
        .done(function(data) {
            "use strict";
            let obj = dt_network_maps_hovermap
            let chartDiv = jQuery('#chart')
            chartDiv.empty().html(`Hover Map`)

            console.log( data )
            console.log(obj)
        })
})