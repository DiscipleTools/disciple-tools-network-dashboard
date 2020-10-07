jQuery(document).ready(function(){
    console.log('network-statistics-groups.js file loaded')

    makeRequest('POST', 'network/statistics/groups',{'id': 'test'} )
        .done(function(data) {
            "use strict";
            let obj = dt_network_statistics_groups
            let chartDiv = jQuery('#chart')
            chartDiv.empty().html(`Groups`)

            console.log( data )
            console.log(obj)
        })
})