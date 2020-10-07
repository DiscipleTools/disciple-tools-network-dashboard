jQuery(document).ready(function(){
    console.log('network-maps-cluster.js file loaded')

    makeRequest('POST', 'network/maps/cluster',{'id': 'test'} )
        .done(function(data) {
            "use strict";
            let obj = dt_network_maps_cluster
            let chartDiv = jQuery('#chart')
            chartDiv.empty().html(`Cluster`)

            console.log( data )
            console.log(obj)
        })
})