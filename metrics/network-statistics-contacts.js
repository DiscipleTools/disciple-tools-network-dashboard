jQuery(document).ready(function(){
    console.log('network-statistics-contacts.js file loaded')

    makeRequest('POST', 'network/statistics/contacts',{'id': 'test'} )
        .done(function(data) {
            "use strict";
            let obj = dt_network_statistics_contacts
            let chartDiv = jQuery('#chart')
            chartDiv.empty().html(`Contacts`)

            console.log( data )
            console.log(obj)
        })
})