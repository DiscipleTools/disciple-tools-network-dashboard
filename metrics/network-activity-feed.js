jQuery(document).ready(function(){
    let chartDiv = jQuery('#chart')
    let spinner = '<span class="loading-spinner active"></span>'

    // add highlight to menu
    jQuery('#network_activity_feed').prop('style', 'font-weight:900;')

    // write page layout with spinners
    chartDiv.empty().html(`
             <style>
                #activity-wrapper {
                    height: ${window.innerHeight - 200}px !important;
                    overflow: scroll;
                }
            </style>
            <span class="section-header">Feed</span>
                <hr style="max-width:100%;">
                <div class="grid-x grid-padding-x">
                    <div class="medium-3 cell">
                        <div class="grid-x">
                            <div class="cell" id="filters"></div>
                            <div class="cell"><hr></div>
                            <div class="cell">
                                Sites
                                <select id="site-list" name="site-filter">
                                    <option>No Filter</option>
                                </select>
                            </div>
                            <div class="cell">
                                Types
                                <select id="action-filter" name="action-filter">
                                    <option>No Filter</option>
                                </select>
                            </div>
                        </div>
                        <div class="cell"><hr><button class="button clear" onclick="reset()">reset data</button> <span class="reset-spinner"></span></div>
                    </div>
                    <div class="medium-9 cell" style="border-left: 1px solid lightgrey;">
                        <div id="activity-wrapper">
                            <div id="activity-list">${spinner}</div>
                        </div>
                    </div>
                </div>
            `)

    let container = jQuery('#activity-list');
    let index = 0

    // call for data
    makeRequest('POST', 'network/base', {'type': 'activity', 'filters': { 'end': '-7 days' } } )
        .done( data => {
            "use strict";
            window.feed = data
            write_activity_list()
        })

    makeRequest('POST', 'network/base', {'type': 'activity_stats'} )
        .done(function(data) {
            console.log(data)
            window.activity_stats = data
            write_filters()
        })

    function write_activity_list(){
        index = 0
        jQuery.each( window.feed, function(i,v){
            container.append(`<h2>${v.label} (${v.list.length} events)</h2>`)
            container.append(`<ul>`)
            jQuery.each(v.list, function(ii,vv){
                container.append(`<li class="${vv.action} ${vv.site_id}"><strong>(${vv.time})</strong> ${vv.message} </li>`)
                index++
            })
            container.append(`</ul>`)

            if ( index > 500 ){
                return false
            }
        })

        jQuery('.loading-spinner').removeClass('active')
    }

    function write_filters(){

        let site_list = jQuery('#site-list')
        site_list.empty().html(`<option>No Filter</option><option disabled>-----</option>`)
        jQuery.each( window.activity_stats.sites_labels, function(sli,slv){
            site_list.append(`<option value="${sli}">${slv}</option>`)
        })

        let action_list = jQuery('#action-filter')
        action_list.empty().html(`<option>No Filter</option><option disabled>-----</option>`)
        jQuery.each( window.activity_stats.actions_labels, function(ali,alv){
            action_list.append(`<option value="${ali}">${alv}</option>`)
        })

        jQuery('#filters').html(`Results: ${window.activity_stats.total_records} (Last 7 Days )`)
    }

})