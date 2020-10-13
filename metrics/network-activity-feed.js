jQuery(document).ready(function(){
    let obj = network_activity_feed
    let chartDiv = jQuery('#chart')
    let spinner = '<span class="loading-spinner active"></span>'

    // add highlight to menu
    jQuery('#network_activity_feed').prop('style', 'font-weight:900;')

    // write page layout with spinners
    chartDiv.empty().html(`
            <span class="section-header">Feed</span>
                <hr style="max-width:100%;">
                
                
                
                
                
                <div><button class="button clear" onclick="reset()">reset data</button> <span class="reset-spinner"></span></div>
            `)

    // call for data
    makeRequest('POST', 'network/base', {'type': 'activity'} )
        .done( data => {
            "use strict";
            console.log(data)
        })
})