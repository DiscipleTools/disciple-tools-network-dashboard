jQuery(document).ready(function() {
    if( ! window.location.hash || '#saturation_mapping_overview' === window.location.hash) {
        show_saturation_mapping_overview()
    }
    if('#saturation_tree' === window.location.hash) {
        show_saturation_tree()
    }
    if('#saturation_map' === window.location.hash) {
        show_saturation_map()
    }
    if('#saturation_side_tree' === window.location.hash) {
        show_saturation_side_tree()
    }
})

function show_saturation_mapping_overview(){
"use strict";
let page = wpApiSatMapMetrics
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
            <a onclick="refresh_stats_data( 'show_saturation_mapping_overview' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+wpApiSatMapMetrics.plugin_uri+`includes/ajax-loader.gif" /></span> 
            </div>`)
}

function show_saturation_tree(){
    "use strict";
    let page = wpApiSatMapMetrics
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
            <a onclick="refresh_stats_data( 'show_saturation_mapping_overview' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+wpApiSatMapMetrics.plugin_uri+`includes/ajax-loader.gif" /></span> 
            </div>`)
}

function show_saturation_map(){
    "use strict";
    let page = wpApiSatMapMetrics
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
            <a onclick="refresh_stats_data( 'show_saturation_mapping_overview' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+wpApiSatMapMetrics.plugin_uri+`includes/ajax-loader.gif" /></span> 
            </div>`)
}

function show_saturation_side_tree(){
    "use strict";
    let page = wpApiSatMapMetrics
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
            <a onclick="refresh_stats_data( 'show_saturation_mapping_overview' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+wpApiSatMapMetrics.plugin_uri+`includes/ajax-loader.gif" /></span> 
            </div>`)
}