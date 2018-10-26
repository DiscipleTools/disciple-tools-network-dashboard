jQuery(document).ready(function() {
    if( ! window.location.hash || '#network_home' === window.location.hash) {
        show_network_home()
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

function show_network_home(){
    "use strict";
    let page = wpApiNetworkDashboard
    console.log(page)
    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 1.3
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`
        <span class="section-header">`+ page.translations.sm_title +`</span>
        
        <hr style="max-width:100%;">
        <div id="mapchartdiv" style="width: 100%;max-height: 600px;height: 100vh;"></div>
        
        <hr style="max-width:100%;">
        
        <div class="grid-x">
            <div class="medium-3 cell">
                <button class="button" id="line_chart_button" data-open="line_chart_full">Line Chart</button>
            </div>    
            <div class="medium-3 cell">
                <button class="button" id="pie_chart_button" data-open="pie_chart_full">Pie Chart</button>
            </div>    
            <div class="medium-3 cell">
                <button class="button" id="pie_chart2_button" data-open="pie_chart2_full">Pie2 Chart</button>
                
            </div>    
            <div class="medium-3 cell">
                
            </div>    
        </div>
        
        
        <div id="line_chart_full" class="full reveal" data-reveal>
            <div id="line_chart" style="width: 100%;max-height: 800px;height: 100vh;"></div>
            
            <button class="close-button" data-close aria-label="Close modal" type="button">
              <span aria-hidden="true">&times;</span>
            </button>
        </div>
        
        <div id="pie_chart_full" class="full reveal" data-reveal>
            <div id="pie_chart" style="width: 100%;max-height: 800px;height: 100vh;"></div>
            
            <button class="close-button" data-close aria-label="Close modal" type="button">
              <span aria-hidden="true">&times;</span>
            </button>
        </div>
        
        <div id="pie_chart2_full" class="full reveal" data-reveal>
            <div id="pie_chart2" style="width: 100%;max-height: 800px;height: 100vh;"></div>
            
            <button class="close-button" data-close aria-label="Close modal" type="button">
              <span aria-hidden="true">&times;</span>
            </button>
        </div>
        
        `);

    function show_map() {
        am4core.useTheme(am4themes_animated);

        var chart = am4core.create("mapchartdiv", am4maps.MapChart);

        try {
            chart.geodata = am4geodata_worldLow;
        }
        catch (e) {
            chart.raiseCriticalError({
                "message": "Map geodata could not be loaded. Please download the latest <a href=\"https://www.amcharts.com/download/download-v4/\">amcharts geodata</a> and extract its contents into the same directory as your amCharts files."
            });
        }


        chart.projection = new am4maps.projections.Miller();

        var title = chart.chartContainer.createChild(am4core.Label);
        title.fontSize = 20;
        title.paddingTop = 30;
        title.align = "center";

        var polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());
        var polygonTemplate = polygonSeries.mapPolygons.template;
        polygonTemplate.tooltipText = "{name}: {value.value}";
        polygonSeries.useGeodata = true;
        polygonSeries.heatRules.push({ property: "fill", target: polygonSeries.mapPolygons.template, min: am4core.color("#ffffff"), max: am4core.color("#AAAA00") });


// add heat legend
        var heatLegend = chart.chartContainer.createChild(am4maps.HeatLegend);
        heatLegend.valign = "bottom";
        heatLegend.series = polygonSeries;
        heatLegend.width = am4core.percent(100);
        heatLegend.orientation = "horizontal";
        heatLegend.padding(20, 20, 20, 20);
        heatLegend.valueAxis.renderer.labels.template.fontSize = 10;
        heatLegend.valueAxis.renderer.minGridDistance = 40;

        polygonSeries.mapPolygons.template.events.on("over", function (event) {
            handleHover(event.target);
        })

        polygonSeries.mapPolygons.template.events.on("hit", function (event) {
            handleHover(event.target);
        })

        function handleHover(mapPolygon) {
            if (!isNaN(mapPolygon.dataItem.value)) {
                heatLegend.valueAxis.showTooltipAt(mapPolygon.dataItem.value)
            }
            else {
                heatLegend.valueAxis.hideTooltip();
            }
        }

        polygonSeries.mapPolygons.template.events.on("out", function (event) {
            heatLegend.valueAxis.hideTooltip();
        })


// life expectancy data

        polygonSeries.data = [{ id: "AF", value: 60.524 },
            { id: "AL", value: 77.185 },
            { id: "DZ", value: 70.874 },
            { id: "AO", value: 51.498 },
            { id: "AR", value: 76.128 },
            { id: "AM", value: 74.469 },
            { id: "AU", value: 82.364 },
            { id: "AT", value: 80.965 },
            { id: "AZ", value: 70.686 },
            { id: "BH", value: 76.474 },
            { id: "BD", value: 70.258 },
            { id: "BY", value: 69.829 },
            { id: "BE", value: 80.373 },
            { id: "BJ", value: 59.165 },
            { id: "BT", value: 67.888 },
            { id: "BO", value: 66.969 },
            { id: "BA", value: 76.211 },
            { id: "BW", value: 47.152 },
            { id: "BR", value: 73.667 },
            { id: "BN", value: 78.35 },
            { id: "BG", value: 73.448 },
            { id: "BF", value: 55.932 },
            { id: "BI", value: 53.637 },
            { id: "KH", value: 71.577 },
            { id: "CM", value: 54.61 },
            { id: "CA", value: 81.323 },
            { id: "CV", value: 74.771 },
            { id: "CF", value: 49.517 },
            { id: "TD", value: 50.724 },
            { id: "CL", value: 79.691 },
            { id: "CN", value: 75.178 },
            { id: "CO", value: 73.835 },
            { id: "KM", value: 60.661 },
            { id: "CD", value: 49.643 },
            { id: "CG", value: 58.32 },
            { id: "CR", value: 79.712 },
            { id: "CI", value: 50.367 },
            { id: "HR", value: 76.881 },
            { id: "CU", value: 79.088 },
            { id: "CY", value: 79.674 },
            { id: "CZ", value: 77.552 },
            { id: "DK", value: 79.251 },
            { id: "GL", value: 79.251 },
            { id: "DJ", value: 61.319 },
            { id: "DO", value: 73.181 },
            { id: "EC", value: 76.195 },
            { id: "EG", value: 70.933 },
            { id: "SV", value: 72.361 },
            { id: "GQ", value: 52.562 },
            { id: "ER", value: 62.329 },
            { id: "EE", value: 74.335 },
            { id: "ET", value: 62.983 },
            { id: "FJ", value: 69.626 },
            { id: "FI", value: 80.362 },
            { id: "FR", value: 81.663 },
            { id: "GA", value: 63.115 },
            { id: "GM", value: 58.59 },
            { id: "GE", value: 74.162 },
            { id: "DE", value: 80.578 },
            { id: "GH", value: 60.979 },
            { id: "GR", value: 80.593 },
            { id: "GT", value: 71.77 },
            { id: "GN", value: 55.865 },
            { id: "GW", value: 54.054 },
            { id: "GY", value: 66.134 },
            { id: "HT", value: 62.746 },
            { id: "HN", value: 73.503 },
            { id: "HK", value: 83.199 },
            { id: "HU", value: 74.491 },
            { id: "IS", value: 81.96 },
            { id: "IN", value: 66.168 },
            { id: "ID", value: 70.624 },
            { id: "IR", value: 73.736 },
            { id: "IQ", value: 69.181 },
            { id: "IE", value: 80.531 },
            { id: "IL", value: 81.641 },
            { id: "IT", value: 82.235 },
            { id: "JM", value: 73.338 },
            { id: "JP", value: 83.418 },
            { id: "JO", value: 73.7 },
            { id: "KZ", value: 66.394 },
            { id: "KE", value: 61.115 },
            { id: "KP", value: 69.701 },
            { id: "KR", value: 81.294 },
            { id: "KW", value: 74.186 },
            { id: "KG", value: 67.37 },
            { id: "LA", value: 67.865 },
            { id: "LV", value: 72.045 },
            { id: "LB", value: 79.716 },
            { id: "LS", value: 48.947 },
            { id: "LR", value: 60.23 },
            { id: "LY", value: 75.13 },
            { id: "LT", value: 71.942 },
            { id: "LU", value: 80.371 },
            { id: "MK", value: 75.041 },
            { id: "MG", value: 64.28 },
            { id: "MW", value: 54.798 },
            { id: "MY", value: 74.836 },
            { id: "ML", value: 54.622 },
            { id: "MR", value: 61.39 },
            { id: "MU", value: 73.453 },
            { id: "MX", value: 77.281 },
            { id: "MD", value: 68.779 },
            { id: "MN", value: 67.286 },
            { id: "ME", value: 74.715 },
            { id: "MA", value: 70.714 },
            { id: "EH", value: 70.714 },
            { id: "MZ", value: 49.91 },
            { id: "MM", value: 65.009 },
            { id: "NA", value: 64.014 },
            { id: "NP", value: 67.989 },
            { id: "NL", value: 80.906 },
            { id: "NZ", value: 80.982 },
            { id: "NI", value: 74.515 },
            { id: "NE", value: 57.934 },
            { id: "NG", value: 52.116 },
            { id: "NO", value: 81.367 },
            { id: "SJ", value: 81.367 },
            { id: "OM", value: 76.287 },
            { id: "PK", value: 66.42 },
            { id: "PA", value: 77.342 },
            { id: "PG", value: 62.288 },
            { id: "PY", value: 72.181 },
            { id: "PE", value: 74.525 },
            { id: "PH", value: 68.538 },
            { id: "PL", value: 76.239 },
            { id: "PT", value: 79.732 },
            { id: "QA", value: 78.231 },
            { id: "RO", value: 73.718 },
            { id: "RU", value: 67.874 },
            { id: "RW", value: 63.563 },
            { id: "SA", value: 75.264 },
            { id: "SN", value: 63.3 },
            { id: "RS", value: 73.934 },
            { id: "SL", value: 45.338 },
            { id: "SG", value: 82.155 },
            { id: "SK", value: 75.272 },
            { id: "SI", value: 79.444 },
            { id: "SB", value: 67.465 },
            { id: "SO", value: 54 },
            { id: "ZA", value: 56.271 },
            { id: "SS", value: 54.666 },
            { id: "ES", value: 81.958 },
            { id: "LK", value: 74.116 },
            { id: "SD", value: 61.875 },
            { id: "SR", value: 70.794 },
            { id: "SZ", value: 48.91 },
            { id: "SE", value: 81.69 },
            { id: "CH", value: 82.471 },
            { id: "SY", value: 71 },
            { id: "TW", value: 79.45 },
            { id: "TJ", value: 67.118 },
            { id: "TZ", value: 60.885 },
            { id: "TH", value: 74.225 },
            { id: "TL", value: 67.033 },
            { id: "TG", value: 56.198 },
            { id: "TT", value: 69.761 },
            { id: "TN", value: 75.632 },
            { id: "TR", value: 74.938 },
            { id: "TM", value: 65.299 },
            { id: "UG", value: 58.668 },
            { id: "UA", value: 68.414 },
            { id: "AE", value: 76.671 },
            { id: "GB", value: 80.396 },
            { id: "US", value: 78.797 },
            { id: "UY", value: 77.084 },
            { id: "UZ", value: 68.117 },
            { id: "VE", value: 74.477 },
            { id: "PS", value: 73.018 },
            { id: "VN", value: 75.793 },
            { id: "YE", value: 62.923 },
            { id: "ZM", value: 57.037 },
            { id: "ZW", value: 58.142 }];

// excludes Antarctica
        polygonSeries.exclude = ["AQ"];

    }
    show_map()

    jQuery('#pie_chart_button').on('click', function() {
        pie_chart()
    })

    jQuery('#line_chart_button').on('click', function() {
        line_chart()
    })

    jQuery('#pie_chart2_button').on('click', function() {
        pie_chart2()
    })

    function pie_chart() {
        /**
         * This is a demo for PieChart.
         *
         * Demo uses JSON-based config.
         *
         * Refer to the following link(s) for reference:
         * @see {@link https://www.amcharts.com/docs/v4/chart-types/pie-chart/}
         * @see {@link https://www.amcharts.com/docs/v4/concepts/json-config/}
         */

// Set theme
        am4core.useTheme(am4themes_animated);

// Create chart
        var chart = am4core.createFromConfig({
            // Set data
            data: [{
                "country": "Lithuania",
                "litres": 501.9
            }, {
                "country": "Czech Republic",
                "litres": 301.9
            }, {
                "country": "Ireland",
                "litres": 201.1
            }, {
                "country": "Germany",
                "litres": 165.8
            }, {
                "country": "Australia",
                "litres": 139.9
            }, {
                "country": "Austria",
                "litres": 128.3
            }, {
                "country": "UK",
                "litres": 99
            }, {
                "country": "Belgium",
                "litres": 60
            }, {
                "country": "The Netherlands",
                "litres": 50
            }],

            // Create series
            "series": [{
                "type": "PieSeries",
                "dataFields": {
                    "value": "litres",
                    "category": "country"
                },
                "hiddenState": {
                    "properties": {
                        // this creates initial animation
                        "opacity": 1,
                        "endAngle": -90,
                        "startAngle": -90
                    }
                }
            }],

            // Add legend
            "legend": {}
        }, "pie_chart", "PieChart");


    }

    function line_chart() {
        // Set theme
        am4core.useTheme(am4themes_animated);

// Generate random data
        var data = [];
        var visits = 10;

        for (var i = 1; i < 366; i++) {
            visits += Math.round((Math.random() < 0.5 ? 1 : -1) * Math.random() * 10);
            data.push({
                date: new Date(2018, 0, i),
                name: "name" + i,
                value: visits
            });
        }

// Create chart
        var chart = am4core.createFromConfig({
            // Set settings and data
            "paddingRight": 30,
            "data": data,

            // Create X axes
            "xAxes": [{
                "type": "DateAxis",
                "renderer": {
                    "grid": {
                        "location": 0
                    }
                }
            }],

            // Create Y axes
            "yAxes": [{
                "type": "ValueAxis",
                "tooltip": {
                    "disabled": true
                },
                "renderer": {
                    "minWidth": 35
                }
            }],

            // Create series
            "series": [{
                "id": "s1",
                "type": "LineSeries",
                "dataFields": {
                    "dateX": "date",
                    "valueY": "value"
                },
                "tooltipText": "{valueY.value}"
            }],

            // Add cursor
            "cursor": {
                "type": "XYCursor"
            },

            // Add horizontal scrollbar
            "scrollbarX": {
                "type": "XYChartScrollbar",
                "series": ["s1"]
            }
        }, "line_chart", "XYChart");
    }

    function pie_chart2() {
        /**
         * This is a demo for PieChart.
         *
         * Demo uses JSON-based config.
         *
         * Refer to the following link(s) for reference:
         * @see {@link https://www.amcharts.com/docs/v4/chart-types/pie-chart/}
         * @see {@link https://www.amcharts.com/docs/v4/concepts/json-config/}
         */

// Set theme
        am4core.useTheme(am4themes_animated);

// Create chart
        var chart = am4core.createFromConfig({
            // Set data
            data: [{
                "country": "Lithuania",
                "litres": 501.9
            }, {
                "country": "Czech Republic",
                "litres": 301.9
            }, {
                "country": "Ireland",
                "litres": 201.1
            }, {
                "country": "Germany",
                "litres": 165.8
            }, {
                "country": "Australia",
                "litres": 139.9
            }, {
                "country": "Austria",
                "litres": 128.3
            }, {
                "country": "UK",
                "litres": 99
            }, {
                "country": "Belgium",
                "litres": 60
            }, {
                "country": "The Netherlands",
                "litres": 50
            }],

            // Create series
            "series": [{
                "type": "PieSeries",
                "dataFields": {
                    "value": "litres",
                    "category": "country"
                },
                "hiddenState": {
                    "properties": {
                        // this creates initial animation
                        "opacity": 1,
                        "endAngle": -90,
                        "startAngle": -90
                    }
                }
            }],

            // Add legend
            "legend": {}
        }, "pie_chart2", "PieChart");


    }

    new Foundation.Reveal(jQuery('#line_chart_full'));
    new Foundation.Reveal(jQuery('#pie_chart_full'));
    new Foundation.Reveal(jQuery('#pie_chart2_full'));

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

