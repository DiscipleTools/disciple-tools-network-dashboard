jQuery(document).ready(function(){
    console.log('network-activity-map.js file loaded')

    makeRequest('POST', 'network/activity/map',{'id': 'test'} )
        .done(function(data) {
            "use strict";
            let obj = dt_network_activity_map
            let chartDiv = jQuery('#chart')
            chartDiv.empty().html(`Map`)

            console.log( data )
            console.log(obj)
        })
})