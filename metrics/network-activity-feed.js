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
            <span class="section-header">Feed<span style="float:right;"><button class="button clear" onclick="reset()">reset data</button> <span class="reset-spinner"></span></span></span>
                <hr style="max-width:100%;">
                <div class="grid-x grid-padding-x">
                    <div class="medium-3 cell">
                        <div class="grid-x">
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
                    </div>
                    <div class="medium-9 cell" style="border-left: 1px solid lightgrey;">
                        <div id="activity-wrapper">
                            <div id="activity-list">${spinner}</div>
                        </div>
                    </div>
                </div>
            `)


    // call for data
    makeRequest('POST', 'network/activity/feed' )
        .done( data => {
            "use strict";
            window.feed = data

            let container = jQuery('#activity-list');
            let index = 0
            jQuery.each( window.feed.list, function(i,v){
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

            let site_list = jQuery('#site-list')
            jQuery.each( window.feed.sites, function(i,sl){
                site_list.append(`<option value="${sl.key}">${sl.label}</option>`)
            })

            let action_list = jQuery('#action-filter')
            jQuery.each( window.feed.actions, function(i,a){
                action_list.append(`<option value="${a.key}">${a.label}</option>`)
            })

            jQuery('.loading-spinner').removeClass('active')
        })

})