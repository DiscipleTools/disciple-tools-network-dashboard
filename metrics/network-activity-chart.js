jQuery(document).ready(function(){
    console.log('network-activity-chart.js file loaded')

    makeRequest('POST', 'network/activity/chart',{'id': 'test'} )
        .done(function(data) {
            "use strict";
            let obj = dt_network_activity_chart
            let chartDiv = jQuery('#chart')
            chartDiv.empty().html(`Chart`)

            console.log( data )
            console.log(obj)
        })
})