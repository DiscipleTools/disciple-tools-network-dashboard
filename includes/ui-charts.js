jQuery(document).ready(function() {
    if( ! window.location.hash || '#network_dashboard_overview' === window.location.hash) {
        show_network_dashboard_overview()
    }
    if('#network_tree' === window.location.hash) {
        show_network_tree()
    }
    if('#network_map' === window.location.hash) {
        show_network_map()
    }
    if('#network_side_tree' === window.location.hash) {
        show_network_side_tree()
    }
    if('#report_sync' === window.location.hash) {
        show_report_sync()
    }
})

function show_network_dashboard_overview(){
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
        
        `)

    google.charts.load('current', {'packages':['table']});
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
        <div id="chart_div" style="width:100%; height:`+chartHeight+`"></div>
        
        `)

    google.charts.load('current', {packages:["map"]});
    google.charts.setOnLoadCallback(drawChart);

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

function show_network_side_tree(){
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

function show_report_sync(){
    "use strict";
    let page = wpApiNetworkDashboard
    console.log(page)

    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 1.3
    let chartDiv = jQuery('#chart')
    let list = wpApiNetworkDashboard.stats.report_sync

    chartDiv.empty().html(`
        <span class="section-header">`+ page.translations.sm_title +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="zume-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" id="zume-project-legend" data-reveal> 
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
        </div>
        
        <hr style="max-width:100%;">
        `)

    jQuery.each(list, function(i, v) {
        console.log( v )
        chartDiv.append(`
                    <div class="grid-x grid-padding-x grid-margin-x" >
                        <div class="cell">
                            <h4>`+v.post_title+` <span id="status-`+v.id+`">`+page.spinner_large+`</span></h4>
                        </div>
                        <div class="cell" id="site-`+v.id+`"></div>
                    </div><hr style="max-width:100%;">`)
        let data = { "id": v.id }
        jQuery.ajax({
            type: "POST",
            data: JSON.stringify(data),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            url: wpApiNetworkDashboard.root+'dt/v1/network/get_report',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpApiNetworkDashboard.nonce );
            },
        })
            .done(function (data) {
                jQuery('#status-'+v.id).empty().append('&#10003;')
                jQuery('#site-'+v.id).empty().append('Content')

                console.log( v.post_title )
                console.log( data )
            })
            .fail(function (err) {
                jQuery('#status-'+v.id).empty().append( "error" )
                console.log("error for " +  v.post_title );
                console.log(err);
            })
    })


    chartDiv.append(`<div><span class="small grey">( stats as of  )</span> 
            <a onclick="refresh_stats_data( 'show_network_dashboard_overview' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+wpApiNetworkDashboard.plugin_uri+`includes/spinner.svg" /></span> 
            </div>`)
}