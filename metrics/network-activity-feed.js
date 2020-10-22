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
                        <div class="grid-x" id="filters_section">
                            <div class="cell"><h3>Filters</h3></div>
                            <div class="cell" id="filters"></div>
                            <div class="cell"><hr></div>
                            <div class="cell">
                                <strong>Sites</strong>
                                <div id="site-list"></div>
                            </div>
                            <div class="cell">
                                <strong>Types</strong>
                                <div id="action-filter"></div>
                            </div>
                            <div class="cell">
                             <hr>
                             <button type="button" onclick="build_new_list()" class="button small secondary-button">Update List</button>
                             <button class="button clear" onclick="reset()">reset data</button> <span class="reset-spinner"></span>
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

    // initialize vars
    let container = jQuery('#activity-list');
    let index = 0
    window.activity_filter = { 'end': '-7 days' }

    // run on load
    load_data()

    // query and reload data
    function load_data() {
        // call for data
        makeRequest('POST', 'network/base', {'type': 'activity', 'filters': window.activity_filter } )
            .done( data => {
                "use strict";
                window.feed = data
                write_activity_list()
            })

        makeRequest('POST', 'network/base', {'type': 'activity_stats'} )
            .done(function(data) {
                window.activity_stats = data
                write_filters()
            })
    }
    function write_activity_list(){
        // index = 0
        jQuery.each( window.feed, function(i,v){
            container.append(`<h2>${v.label} (${v.list.length} events)</h2>`)
            container.append(`<ul>`)
            jQuery.each(v.list, function(ii,vv){
                container.append(`<li class="${vv.action} ${vv.site_id}"><strong>(${vv.time})</strong> ${vv.message} </li>`)
                index++
            })
            container.append(`</ul>`)
        })

        jQuery('.loading-spinner').removeClass('active')
    }

    function write_filters(){

        let site_list = jQuery('#site-list')
        let hollow = ''
        jQuery.each( window.activity_stats.sites_labels, function(sli,slv){
            hollow = ''
            if ( jQuery.inArray( sli, window.activity_filter.sites ) >= 0 ){
                hollow = 'hollow'
            }
            site_list.append(`<button class="button small sites ${hollow}" value="${sli}">${slv}</button> `)
        })

        let action_list = jQuery('#action-filter')
        jQuery.each( window.activity_stats.actions_labels, function(ali,alv){
            hollow = ''
            if ( jQuery.inArray( ali, window.activity_filter.actions ) >= 0 ){
                hollow = 'hollow'
            }
            action_list.append(`<button class="button small actions ${hollow}" value="${ali}">${alv}</button> `)
        })

        jQuery('#filters').html(`Results: ${window.activity_stats.total_records}`)
    }

    function build_new_list(){
        let sites = jQuery('button.sites .hollow')
        let actions = jQuery('button.actions .hollow')


    }


})