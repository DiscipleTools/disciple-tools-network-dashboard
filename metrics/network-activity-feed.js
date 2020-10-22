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
                            <div class="cell" id="filters">${spinner}</div>
                            <div class="cell"><hr></div>
                             
                            <div class="cell">
                                <strong>Sites</strong>
                                <div id="site-list">${spinner}</div>
                            </div>
                            <div class="cell">
                                <strong>Types</strong>
                                <div id="action-filter">${spinner}</div>
                            </div>
                            <div class="cell">
                                <strong>Time</strong><br>
                                <select name="time_range" id="time_range">
                                    <option value="7">Last 7 Days</option>
                                    <option value="30">Last 30 Days</option>
                                    <option value="this_year">This Year</option>
                                </select>
                            </div>
                            <div class="cell">
                                <strong>Result Limit</strong><br>
                                <select name="record_limit" id="record_limit">
                                    <option value="2000">2000</option>
                                    <option value="5000">5000</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid-x">
                            <div class="cell">
                                 <hr>
                                 <button type="button" id="filter_list_button" class="button small hollow">Filter List</button> <span class="loading-spinner"></span>
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
    window.activity_filter = { 'end': '-7 days' }

    // query and reload data
    function load_data() {
        makeRequest('POST', 'network/base', {'type': 'activity', 'filters': window.activity_filter } )
            .done( data => {
                "use strict";
                window.feed = data
                write_activity_list()
            })
    }
    function load_filters(){
        makeRequest('POST', 'network/base', {'type': 'activity_stats'} )
            .done(function(data) {
                window.activity_stats = data
                write_filters()
            })
    }
    load_data()
    load_filters()

    function write_activity_list(){
        container.empty()
        jQuery.each( window.feed, function(i,v){
            if ( 'records_count' === i ){
                jQuery('#filters').html(`Results: ${v}`)
            } else {
                container.append(`<h2>${v.label} (${v.list.length} events)</h2>`)
                container.append(`<ul>`)
                jQuery.each(v.list, function(ii,vv){
                    container.append(`<li><strong>(${vv.time})</strong> ${vv.message} </li>`)
                })
                container.append(`</ul>`)
            }
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

        // list to button changes
        jQuery('#filters_section button').on('click', function(){
            let item = jQuery(this)
            if ( item.hasClass('hollow') ){
                item.removeClass('hollow')
            } else {
                item.addClass('hollow')
            }
            jQuery('#filter_list_button').removeClass('hollow').addClass('warning')
        })

        // list to refresh filter button
        jQuery('#filter_list_button').on('click', function(){
            jQuery('.loading-spinner').addClass('active')

            let time_range = jQuery('#time_range').val()
            if ( 'this_year' === time_range) {
                var now = new Date();
                var start = new Date(now.getFullYear(), 0, 0);
                var diff = now - start;
                var oneDay = 1000 * 60 * 60 * 24;
                var day = Math.floor(diff / oneDay);
                window.activity_filter.end = '-'+day+' days'
            } else if ( '30' === time_range) {
                window.activity_filter.end = '-30 days'
            } else {
                window.activity_filter.end = '-7 days'
            }

            window.activity_filter.limit = jQuery('#record_limit').val()

            // add site filters
            let sites = []
            jQuery.each( jQuery('#filters_section button.sites.hollow'), function(i,v){
                sites.push(v.value)
            })
            if ( sites ){
                window.activity_filter.sites = sites
            }

            // add action filters
            let actions = []
            jQuery.each( jQuery('#filters_section button.actions.hollow'), function(i,v){
                actions.push(v.value)
            })
            if ( actions ){
                window.activity_filter.actions = actions
            }

            load_data()
            jQuery('#filter_list_button').removeClass('warning').addClass('hollow')
        })


    }

})