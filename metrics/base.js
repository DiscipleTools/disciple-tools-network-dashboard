function load_sites_list_data(){
    makeRequest('GET', obj.endpoint, {'type': 'sites_list'} )
        .done(function(data) {
            window.sites_list = data
        })
}
function load_sites_data(){
    makeRequest('GET', obj.endpoint, {'type': 'sites'} )
        .done(function(data) {
            window.sites = data
        })
}
function load_locations_list_data(){
    makeRequest('GET', obj.endpoint, {'type': 'locations_list'} )
        .done(function(data) {
            window.locations_list = data
        })
}


function load_line_chart(div, id, type, numberOfElements) {
    if ( typeof window.sites === 'undefined'){
        load_sites_data()
    }
    if ('group-line-chart-div'===div) {
        switch (type) {
            case 'days':
                line_chart(div, window.sites[id].groups.added.sixty_days, numberOfElements)
                break;
            case 'months':
                line_chart(div, window.sites[id].groups.added.twenty_four_months, numberOfElements, 'month')
                break;
        }
    } else if ('line-chart-div'===div) {
        switch (type) {
            case 'days':
                line_chart(div, window.sites[id].contacts.added.sixty_days, numberOfElements)
                break;
            case 'months':
                line_chart(div, window.sites[id].contacts.added.twenty_four_months, numberOfElements, 'month')
                break;
        }
    } else if ('user-activity-chart-div'===div) {
        switch (type) {
            case 'days':
                line_chart(div, window.sites[id].users.login_activity.sixty_days, numberOfElements)
                break;
            case 'months':
                line_chart(div, window.sites[id].users.login_activity.twenty_four_months, numberOfElements, 'month')
                break;
        }
    } else if ('global-contacts-chart-div'===div) {
        switch (type) {
            case 'days':
                line_chart(div, window.global.contacts.added.sixty_days, numberOfElements)
                break;
            case 'months':
                line_chart(div, window.global.contacts.added.twenty_four_months, numberOfElements, 'month')
                break;
        }
    } else if ('global-groups-chart-div'===div) {
        switch (type) {
            case 'days':
                line_chart(div, window.global.groups.added.sixty_days, numberOfElements)
                break;
            case 'months':
                line_chart(div, window.global.groups.added.twenty_four_months, numberOfElements, 'month')
                break;
        }
    }
}

function set_buttons(buttonClassName, id) {
    jQuery('.' + buttonClassName + '').addClass('hollow')
    jQuery('#' + id + '').removeClass('hollow')
}

function line_chart(div, dates, numberOfElements, type) {

    // Themes begin
    am4core.useTheme(am4themes_animated);
    let chart = am4core.create(div, am4charts.XYChart);

    chart.paddingRight = 30;
    chart.paddingBottom = 50;

    chart.data = dates.slice(0, numberOfElements);

    // Create axes
    let dateAxis = chart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 60;
    if ('month'===type) {
        dateAxis.tooltipDateFormat = "MMM YYYY";
    }


    let valueAxis = chart.yAxes.push(new am4charts.ValueAxis());

    // Create series
    let series = chart.series.push(new am4charts.LineSeries());
    series.dataFields.valueY = "value";
    series.dataFields.dateX = "date";
    series.tooltipText = "{value}";

    series.tooltip.pointerOrientation = "vertical";
    series.strokeWidth = 6;
    series.fillOpacity = 0.5;

    chart.cursor = new am4charts.XYCursor();
    chart.cursor.snapToSeries = series;
    chart.cursor.xAxis = dateAxis;

}

function load_gen_chart(div, id, type) {
    if ('generations-div'===div) {
        switch (type) {
            case 'g-baptisms':
                bar_chart('generations-div', window.sites[id].contacts.baptisms.generations)
                break;
            case 'g-coaching':
                bar_chart('generations-div', window.sites[id].contacts.coaching.generations)
                break;
            case 'g-groups':
                bar_chart('generations-div', window.sites[id].groups.group_generations)
                break;
            case 'g-churches':
                bar_chart('generations-div', window.sites[id].groups.church_generations)
                break;
        }

    }
}

function load_bar_chart(div, id) {
    if ( 'health-bar-chart-div' === div ) {
        health_stacked_bar_chart('health-bar-chart-div', window.sites[id].groups.health)
    }
}

function health_stacked_bar_chart(div, values) {
    am4core.useTheme(am4themes_animated);
    let chart = am4core.create(div, am4charts.XYChart);

    chart.data = values;

    // Create axes
    let categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
    categoryAxis.dataFields.category = "category";
    categoryAxis.renderer.grid.template.location = 0.5;
    categoryAxis.renderer.labels.template.rotation = -90;
    categoryAxis.renderer.minGridDistance = 30;

    let valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
    valueAxis.renderer.inside = true;
    valueAxis.renderer.labels.template.disabled = true;
    valueAxis.min = 0;

    // Create series
    function createSeries(field, name) {

        // Set up series
        let series = chart.series.push(new am4charts.ColumnSeries());
        series.name = name;
        series.dataFields.valueY = field;
        series.dataFields.categoryX = "category";
        series.sequencedInterpolation = true;

        // Make it stacked
        series.stacked = true;

        // Configure columns
        series.columns.template.width = am4core.percent(60);
        series.columns.template.tooltipText = "[bold]{categoryX}[/]\n[font-size:14px]{name}: {valueY}";

        // Add label
        let labelBullet = series.bullets.push(new am4charts.LabelBullet());
        labelBullet.label.text = "{valueY}";
        labelBullet.locationY = 0.5;

        return series;
    }

    createSeries('practicing', 'Practicing')
    createSeries('not_practicing', 'Not Practicing')


    // Legend
    chart.legend = new am4charts.Legend();
}

function bar_chart(div, values) {
    // Themes begin
    am4core.useTheme(am4themes_animated);
    // Themes end

    // Create chart instance
    let chart = am4core.create(div, am4charts.XYChart);

    // Add data
    chart.data = values;

    // Create axes

    let categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
    categoryAxis.dataFields.category = "label";
    categoryAxis.renderer.grid.template.location = 0;
    categoryAxis.renderer.minGridDistance = 30;

    categoryAxis.renderer.labels.template.adapter.add("dy", function (dy, target) {
        if (target.dataItem && target.dataItem.index & 2==2) {
            return dy + 25;
        }
        return dy;
    });

    let valueAxis = chart.yAxes.push(new am4charts.ValueAxis());

    // Create series
    let series = chart.series.push(new am4charts.ColumnSeries());
    series.dataFields.valueY = "value";
    series.dataFields.categoryX = "label";
    series.name = "Visits";
    series.columns.template.tooltipText = "{categoryX}: [bold]{valueY}[/]";
    series.columns.template.fillOpacity = .8;

    let columnTemplate = series.columns.template;
    columnTemplate.strokeWidth = 2;
    columnTemplate.strokeOpacity = 1;
}

function load_pie_chart(div, id) {
    if ('types-pie-chart-div'===div) {
        pie_chart('types-pie-chart-div', window.sites[id].groups.by_types)
    } else if ('system-engagement-pie-chart-div'===div) {
        pie_chart('system-engagement-pie-chart-div', window.sites[id].users.last_thirty_day_engagement)
    }
}

function pie_chart(div, values) {
    am4core.useTheme(am4themes_animated);

    // Create chart instance
    let chart = am4core.create(div, am4charts.PieChart);

    chart.data = values

    let pieSeries = chart.series.push(new am4charts.PieSeries());
    pieSeries.dataFields.value = "value";
    pieSeries.dataFields.category = "label";
    pieSeries.labels.template.disabled = true;
    pieSeries.ticks.template.disabled = true;
    pieSeries.slices.template.stroke = am4core.color("#ffffff");
    pieSeries.slices.template.strokeWidth = 1;

    chart.innerRadius = am4core.percent(25);

    chart.legend = new am4charts.Legend();
}

function load_funnel_chart(div, id) {
    if ('church-funnel-chart-div'===div) {
        funnel_chart(div, window.sites[id].groups.by_types, "vertical", true)
    } else if ('follow-up-funnel-chart-div'===div) {
        funnel_chart(div, window.sites[id].contacts.follow_up_funnel.funnel, "horizontal")
    }
}

function funnel_chart(div, chartData, direction, labels) {
    am4core.useTheme(am4themes_animated);

    let chart = am4core.create(div, am4charts.SlicedChart);
    chart.hiddenState.properties.opacity = 0; // this makes initial fade in effect

    chart.data = chartData

    let series = chart.series.push(new am4charts.FunnelSeries());
    series.colors.step = 2;
    series.dataFields.value = "value";
    series.dataFields.category = "name";

    if (true===labels) {
        series.alignLabels = true;
        series.labelsContainer.paddingLeft = 15;
        series.labelsContainer.width = 200;
    } else {
        series.labels.template.disabled = true;
        series.ticks.template.disabled = true;
    }

    series.orientation = direction;
    //series.bottomRatio = 1;

    chart.legend = new am4charts.Legend();
    chart.legend.position = "center";
    chart.legend.valign = "bottom";
    chart.legend.margin(5, 5, 20, 5);
}

function load_points_map(div, id) {
    console.log(div);
    console.log(id);
    if ('site-map-div'===div) {
        // used on the single site page

        let locations = []
        jQuery.each(window.locations_list.list, function (i, v) {
            if (v.level==='0' && v[id]) {
                locations.push(v)
            }
        })

        points_map_chart(div, locations)

    } else if ('home-map-div'===div) {
        //build locations list
        let locations = []
        jQuery.each(window.locations_list.list, function (i, v) {
            if (v.level==='-3') {
                locations.push(v)
            }
        })

        points_map_chart(div, locations)
    }
}

function points_map_chart(div, values) {
    // Themes begin
    am4core.useTheme(am4themes_animated);
    // Create map instance
    let chart = am4core.create(div, am4maps.MapChart);

    let mapUrl = MAPPINGDATA.settings.mapping_source_url + 'collection/world.geojson'
    jQuery.getJSON( mapUrl, function( data ) {
        window.am4geodata_worldLow = data

        // Create map polygon series
        chart.geodata = am4geodata_worldLow;

        chart.projection = new am4maps.projections.Miller();
        let polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());
        polygonSeries.exclude = ["AQ"];
        polygonSeries.useGeodata = true;


        let imageSeries = chart.series.push(new am4maps.MapImageSeries());
        imageSeries.data = values;

        let imageSeriesTemplate = imageSeries.mapImages.template;
        let circle = imageSeriesTemplate.createChild(am4core.Circle);
        circle.radius = 13;
        circle.fill = am4core.color("#8bc34a");
        circle.stroke = am4core.color("#8bc34a");
        circle.strokeWidth = 2;
        circle.nonScaling = true;

        // Click navigation
        circle.events.on("hit", function (ev) {
            console.log(ev.target.dataItem.dataContext.name)
            console.log(ev.target.dataItem.dataContext.grid_id)

            location_grid_map( div, ev.target.dataItem.dataContext.grid_id)

        }, this);

        if ('site-map-div'===div) {
            circle.tooltipText = "[bold]{name}[/] ";
        } else if ('home-map-div'===div) {
            circle.tooltipText = "[bold]{name}[/] \n {sites}";
        }

        imageSeriesTemplate.propertyFields.latitude = "latitude";
        imageSeriesTemplate.propertyFields.longitude = "longitude";
        imageSeriesTemplate.nonScaling = true;

    })
}
