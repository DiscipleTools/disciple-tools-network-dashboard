jQuery(document).ready(function() {
    if( ! window.location.hash || '#network_hero_stats' === window.location.hash) {
        show_network_hero_stats()
    }
    if( '#network_basics' === window.location.hash) {
        show_network_basics()
    }
    if('#network_critical_path' === window.location.hash) {
        show_network_critical_path()
    }
    if('#report_sync' === window.location.hash) {
        show_report_sync()
    }
    if('#network_tree' === window.location.hash) {
        show_network_tree()
    }
    if('#network_map' === window.location.hash) {
        show_network_map()
    }
    if('#network_locations' === window.location.hash) {
        show_network_locations()
    }

})

function show_network_hero_stats(){
    "use strict";
    let page = wpApiNetworkDashboard
    console.log(page)
    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 1.3
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`
        <span class="section-header">`+ page.translations.sm_title +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="zume-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" id="zume-project-legend" data-reveal> 
        <button class="close-button" data-close aria-label="Close modal" type="button">
            
            <span aria-hidden="true">&times;</span>
        </button>
        
        </div>
        <hr style="max-width:100%;">
        <div class="table-scroll">
            
        </div>
        
        `)

}

function show_network_basics(){
"use strict";
let page = wpApiNetworkDashboard
    console.log(page)
let screenHeight = jQuery(window).height()
let chartHeight = screenHeight / 1.3
let chartDiv = jQuery('#chart')
chartDiv.empty().html(`
        <span class="section-header">`+ page.translations.sm_title +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="zume-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" id="zume-project-legend" data-reveal> 
        <button class="close-button" data-close aria-label="Close modal" type="button">
            
            <span aria-hidden="true">&times;</span>
        </button>
        
        </div>
        <hr style="max-width:100%;">
        <div class="table-scroll">
            <table id="stat_table" class="stack hover scroll" style="text-align:left;">
                <tr><th>Name</th><th>Contacts</th><th>Groups</th><th>Workers</th><th>Locations</th></tr>
            </table>
        </div>
        
        `)

    let list = wpApiNetworkDashboard.site_link_list;
    jQuery.each(list, function(i, v) {
        /* Project Totals */
        let stat_table = jQuery('#stat_table')

        stat_table.append( `<tr id="stat-${v.id}"><th>${v.name}</th><td id="spinner-${v.id}">${page.spinner_large}</td></tr>`)

        let data = { "id": v.id, "type": 'project_totals' }
        jQuery.ajax({
            type: "POST",
            data: JSON.stringify(data),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            url: wpApiNetworkDashboard.root+'dt/v1/network/ui/live_stats',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpApiNetworkDashboard.nonce );
            },
        })
            .done(function (data) {
                jQuery('#spinner-'+v.id).remove()

                let response = jQuery.parseJSON( data )
                console.log(response)
                jQuery.each(response, function(ii, vv) {
                    jQuery('#stat-'+v.id).append( '<td>'+vv+'</td>' )
                })

            })
            .fail(function (err) {
                jQuery('#stat-'+vv.id).empty().append( "<td>error</td>" )
                console.log("error for " +  vv.name );
                console.log(err);
            })
    })
    jQuery('#table-scroll').foundation()
    // new Foundation.reInit( jQuery('#stat_table'));

}

function show_network_critical_path(){
    "use strict";
    let page = wpApiNetworkDashboard
    console.log(page)
    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 1.3
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`
        <span class="section-header">`+ page.translations.sm_title +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="zume-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" id="zume-project-legend" data-reveal> 
        <button class="close-button" data-close aria-label="Close modal" type="button">
            
            <span aria-hidden="true">&times;</span>
        </button>
        
        </div>
        <hr style="max-width:100%;">
        <div class="table-scroll">
            <table id="stat_table" class="stack hover scroll" style="text-align:left;">
                <tr class="header">
                    <th>Name</th>
                    <th>New Inquirers</th>
                    <th>First Meetings</th>
                    <th>Ongoing Meetings</th>
                    <th>Total Baptisms</th>
                    <th>Baptizers</th>
                    <th>Total Churches and Groups</th>
                    <th>Active Groups</th>
                    <th>Active Churches</th>
                    <th>People Groups</th>
                </tr>
            </table>
        </div>
        
        `)

    let list = wpApiNetworkDashboard.site_link_list;
    jQuery.each(list, function(i, v) {
        /* Project Totals */
        let stat_table = jQuery('#stat_table')

        stat_table.append( `<tr id="stat-${v.id}"><th>${v.name}</th><td id="spinner-${v.id}">${page.spinner_large}</td></tr>`)

        let data = { "id": v.id, "type": 'critical_path' }
        jQuery.ajax({
            type: "POST",
            data: JSON.stringify(data),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            url: wpApiNetworkDashboard.root+'dt/v1/network/ui/live_stats',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpApiNetworkDashboard.nonce );
            },
        })
            .done(function (data) {
                jQuery('#spinner-'+v.id).remove()

                let response = jQuery.parseJSON( data )
                console.log(response)
                jQuery.each(response, function(ii, vv) {
                    jQuery('#stat-'+v.id).append( '<td>'+vv+'</td>' )
                })

            })
            .fail(function (err) {
                jQuery('#stat-'+vv.id).empty().append( "<td>error</td>" )
                console.log("error for " +  vv.name );
                console.log(err);
            })
    })
    jQuery('#table-scroll').foundation()

}

function show_report_sync(){
    "use strict";
    let page = wpApiNetworkDashboard
    console.log(page)

    let chartDiv = jQuery('#chart')

    chartDiv.empty().html(
        `<span class="section-header">`+ page.translations.sm_title +`</span>`+
        `<span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="zume-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" id="zume-project-legend" data-reveal> 
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
        </div>
        
        <hr style="max-width:100%;">
        `)

    let list = wpApiNetworkDashboard.stats.report_sync;
    jQuery.each(list, function(i, v) {
        chartDiv.append(`
                    <div class="grid-x grid-padding-x grid-margin-x" >
                        <div class="cell"><h4>` + v.name + `</h4></div>
                        <div class="cell" id="site-`+v.id+`"></div>
                    </div><hr style="max-width:100%;">`);

        let spinner_section = jQuery('#site-'+v.id)


        /* Project Totals */
        spinner_section.append(`<p id="project-total-`+v.id+`">`+page.spinner_large+`</p>`)
        let data = { "id": v.id, "type": 'project_totals' }
        jQuery.ajax({
            type: "POST",
            data: JSON.stringify(data),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            url: wpApiNetworkDashboard.root+'dt/v1/network/ui/trigger_transfer',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpApiNetworkDashboard.nonce );
            },
        })
            .done(function (data) {
                jQuery('#project-total-'+v.id).empty().append( "Project Total Record Id: " + data )

            })
            .fail(function (err) {
                jQuery('#project-total-'+v.id).empty().append( "error" )
                console.log("error for " +  v.name );
                console.log(err);
            })



        /* Partner Profile Report */
        spinner_section.append( `<p id="partner-profile-`+v.id+`">`+page.spinner_large+`</p>`)
        let data2 = { "id": v.id, "type": 'site_profile' }
        jQuery.ajax({
            type: "POST",
            data: JSON.stringify(data2),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            url: wpApiNetworkDashboard.root+'dt/v1/network/ui/trigger_transfer',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpApiNetworkDashboard.nonce );
            },
        })
            .done(function (data) {
                jQuery('#partner-profile-'+v.id).empty().append( "Site Profile: " + data)
                console.log(data)
            })
            .fail(function (err) {
                jQuery('#status-'+v.id).empty().append( "error" )
                console.log("error for " +  v.name );
                console.log(err);
            })



        /* Partner Location Report */
        spinner_section.append( `<p id="site-locations-`+v.id+`">`+page.spinner_large+`</p>`)

        let data3 = { "id": v.id, "type": "outstanding_site_locations" }
        jQuery.ajax({
            type: "POST",
            data: JSON.stringify(data3),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            url: wpApiNetworkDashboard.root+'dt/v1/network/ui/trigger_transfer',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpApiNetworkDashboard.nonce );
            },
        })
            .done(function (data) {
                jQuery('#site-locations-'+v.id).empty().append( "Site Locations: " + data)
                console.log(data)
            })
            .fail(function (err) {
                jQuery('#site-locations-'+v.id).empty().append( "error" )
                console.log("error for " +  v.name );
                console.log(err);
            })


    })

}

function show_network_locations(){
    "use strict";
    let page = wpApiNetworkDashboard
    console.log(page)
    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 1.3
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`
        <span class="section-header">`+ page.translations.sm_title +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="zume-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" id="zume-project-legend" data-reveal> 
        <button class="close-button" data-close aria-label="Close modal" type="button">
            
            <span aria-hidden="true">&times;</span>
        </button>
        </div>
        <hr style="max-width:100%;">
        <div id="chart_div" style="width:100%; height:`+chartHeight+`"></div>
        
        `)
    chartDiv.append(page.stats.level_tree);


    chartDiv.append(`<hr style="max-width:100%;"><div><span class="small grey">( stats as of  )</span> 
            <a onclick="refresh_stats_data( 'show_network_dashboard_overview' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+wpApiNetworkDashboard.plugin_uri+`includes/spinner.svg" /></span> 
            </div>`)
}

function show_network_tree(){
    "use strict";
    let page = wpApiNetworkDashboard
    console.log(page)
    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 1.3
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`
        <span class="section-header">`+ page.translations.sm_title +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="zume-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" id="zume-project-legend" data-reveal> 
        <button class="close-button" data-close aria-label="Close modal" type="button">
            
            <span aria-hidden="true">&times;</span>
        </button>
        </div>
        <hr style="max-width:100%;">
        <div id="stat_table" style="width:100%"></div>
        <div class="grid-x">
            <div class="cell">
                
            </div>
        </div>
        `)

    google.charts.load('current', {packages:["orgchart"]});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Name');
        data.addColumn('string', 'Manager');
        data.addColumn('string', 'ToolTip');

        // For each orgchart box, provide the name, manager, and tooltip to show.
        data.addRows(page.stats.tree);

        // Create the chart.
        var chart = new google.visualization.OrgChart(document.getElementById('stat_table'));
        chart.draw(data, {
            allowHtml:true,
            size: 'small',
            allowCollapse: true,
        });
    }

    chartDiv.append(`<hr style="max-width:100%;"><div><span class="small grey">( stats as of  )</span> 
            <a onclick="refresh_stats_data( 'show_network_dashboard_overview' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+wpApiNetworkDashboard.plugin_uri+`includes/spinner.svg" /></span> 
            </div>`)
}

function show_network_map(){
    "use strict";
    let page = wpApiNetworkDashboard
    console.log(page)
    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 1.3
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`
        <span class="section-header">`+ page.translations.sm_title +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="zume-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" id="zume-project-legend" data-reveal> 
        <button class="close-button" data-close aria-label="Close modal" type="button">
            
            <span aria-hidden="true">&times;</span>
        </button>
        </div>
        <hr style="max-width:100%;">
        <div id="stat_table"></div>
        <div id="chart_div" style="width:100%; height:`+chartHeight+`"></div>
        
        `)

    google.charts.load('current', {packages:["map", 'table']});
    google.charts.setOnLoadCallback(drawChart);
    google.charts.setOnLoadCallback(drawTable);

    function drawTable() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Location');
        data.addColumn('number', 'Population');
        data.addColumn('number', 'Groups Needed');
        data.addColumn('number', 'Active Groups');
        data.addRows(page.stats.table);

        var table = new google.visualization.Table(document.getElementById('stat_table'));

        table.draw(data, {showRowNumber: true, width: '100%', height: '100%'});
    }

    function drawChart() {
        var data = google.visualization.arrayToDataTable(page.stats.map);

        var options = {
            showTooltip: true,
            showInfoWindow: true,
            height: '700',
            mapType: 'terrain'
        };

        var map = new google.visualization.Map(document.getElementById('chart_div'));

        map.draw(data, options);
    }

    chartDiv.append(`<hr style="max-width:100%;"><div><span class="small grey">( stats as of  )</span> 
            <a onclick="refresh_stats_data( 'show_network_dashboard_overview' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+wpApiNetworkDashboard.plugin_uri+`includes/spinner.svg" /></span> 
            </div>`)
}

