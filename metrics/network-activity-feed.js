jQuery(document).ready(function(){
    let chartDiv = jQuery('#chart')
    let spinner = '<span class="loading-spinner active"></span>'

    // add highlight to menu
    jQuery('#network_activity_feed').prop('style', 'font-weight:900;')

    // write page layout with spinners
    chartDiv.empty().html(`
            <span class="section-header">Feed<span style="float:right;"><button class="button clear" onclick="reset()">reset data</button> <span class="reset-spinner"></span></span></span>
                <hr style="max-width:100%;">
                <div id="activity-feed">${spinner}</div>
            `)


    // call for data
    makeRequest('POST', 'network/activity/feed' )
        .done( data => {
            "use strict";
            console.log(data)
            let container = jQuery('#activity-feed');
            let index = 0

            jQuery.each( data, function(i,v){
                container.append(`<h2>${i} (${v.length} events)</h2>`)
                jQuery.each(v, function(ii,vv){
                    container.append(` ${vv} <br>`)
                    index++
                })
                if ( index > 1000 ){
                    return false
                }
            })

            jQuery('.loading-spinner').removeClass('active')
        })
})