jQuery(document).ready(function(){
    console.log('network-sites.js file loaded')

    makeRequest('POST', 'network/sites',{'id': 'test'} )
        .done(function(data) {
            "use strict";
            let obj = dt_network_sites
            let chartDiv = jQuery('#chart')
            chartDiv.empty().html(`Sites`)

            console.log( data )
            console.log(obj)
        })
})