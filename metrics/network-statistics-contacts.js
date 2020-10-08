jQuery(document).ready(function(){
    let obj = network_statistics_contacts
    let chartDiv = jQuery('#chart')
    let spinner = '<span class="loading-spinner active"></span>'

    // add highlight to menu
    jQuery('#network_statistics_contacts').prop('style', 'font-weight:900;')

    // write page layout with spinners
    chartDiv.empty().html(`
            <span class="section-header">Statistics Contacts</span>
                <hr style="max-width:100%;">
                <div id="map_chart" style="width: 100%; margin:0 auto; max-height: 700px;height: 100vh;vertical-align: text-top;">${spinner}</div>
                
                <hr style="max-width:100%;">
            `)

    // call for data
    makeRequest('POST', obj.endpoint,{'id': 'test'} )
        .done(function(data) {
            "use strict";
            console.log(obj)
        })
})