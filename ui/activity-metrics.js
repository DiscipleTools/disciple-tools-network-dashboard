jQuery(document).ready(function($) {

    if('/network/activity/livefeed' === window.location.pathname || '/network/activity/livefeed/' === window.location.pathname) {
        console.log(dtDashboardActivity)
        write_livefeed()
    }
    if('/network/activity/map' === window.location.pathname || '/network/activity/map/' === window.location.pathname) {
        write_map()
    }
    if('/network/activity/stats' === window.location.pathname || '/network/activity/stats/' === window.location.pathname) {
        write_stats()
    }

})

function write_livefeed(){
    "use strict";
    let obj = dtDashboardActivity
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`
    livefeed
    `)
}

function write_map(){
    "use strict";
    let obj = dtDashboardActivity
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`
    map
    `)
}

function write_stats(){
    "use strict";
    let obj = dtDashboardActivity
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`
    stats
    `)
}