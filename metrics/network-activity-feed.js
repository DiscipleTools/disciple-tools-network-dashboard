jQuery(document).ready(function(){
    console.log('network-activity-feed.js file loaded')

    makeRequest('POST', 'network/activity/feed',{'id': 'test'} )
        .done(function(data) {
            "use strict";
            let obj = dt_network_activity_feed
            let chartDiv = jQuery('#chart')
            chartDiv.empty().html(`Feed`)

            console.log( data )
            console.log(obj)
        })
})