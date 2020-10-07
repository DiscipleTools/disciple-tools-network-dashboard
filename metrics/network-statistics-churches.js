jQuery(document).ready(function(){
    console.log('network-statistics-churches.js file loaded')

    makeRequest('POST', 'network/statistics/churches',{'id': 'test'} )
        .done(function(data) {
            "use strict";
            let obj = dt_network_statistics_churches
            let chartDiv = jQuery('#chart')
            chartDiv.empty().html(`Churches`)

            console.log( data )
            console.log(obj)
        })
})