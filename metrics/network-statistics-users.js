jQuery(document).ready(function(){
    console.log('network-statistics-users.js file loaded')

    makeRequest('POST', 'network/statistics/users',{'id': 'test'} )
        .done(function(data) {
            "use strict";
            let obj = dt_network_statistics_users
            let chartDiv = jQuery('#chart')
            chartDiv.empty().html(`Users`)

            console.log( data )
            console.log(obj)
        })
})