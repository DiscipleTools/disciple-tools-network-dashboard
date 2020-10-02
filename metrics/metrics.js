_ = _ || window.lodash

jQuery(document).ready(function () {
    let sidemenu = jQuery('#metrics-sidemenu')
    sidemenu.prop('data-multi-expand', true)

    MAPPINGDATA.data = wpApiNetworkDashboard.locations_list
    if (!window.location.hash || '#network_home'===window.location.hash) {
        show_network_home()
    }
})


/**
 * Home Page
 */
function show_network_home() {
    "use strict";
    let page = wpApiNetworkDashboard
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`
    <span class="section-header">Network Dashboard</span>
    
    <hr style="max-width:100%;">
    <div id="map_chart" style="width: 100%; max-width:1200px; margin:0 auto; max-height: 700px;height: 100vh;vertical-align: text-top;"></div>
    
    <hr style="max-width:100%;">
    
    <div class="grid-x grid-padding-x grid-padding-y">
      <div class="cell">
      <span class="section-header">Active Totals for All Sites </span><br>
        <div class="grid-x callout">
          
          <div class="medium-2 cell center">
            <h4>Contacts<br><span class="total_contacts"><a href="/network/">${page.global.contacts.total}</a></span></h4>
          </div>
          <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
            <h4>Groups<br><span class="total_groups"><a href="/network/">${page.global.groups.total}</a></span></h4>
          </div>
          <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
            <h4>Users<br><span id="total_users"><a href="/network/">${page.global.users.total}</a></span></h4>
          </div>
          <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
            <h4>Sites<br><span class="total_sites"><a href="/network/sites/">${page.global.sites.total}</a></span></h4>
          </div>
          <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
            <h4>Countries<br><span id="total_countries"><a href="/network/">${page.global.locations.total_countries}</a></span></h4>
          </div>
          <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
            <h4>Prayer Events<br><span id="total_prayer_events"><a href="/network/">${page.global.prayer_events.total}</a></span></h4>
          </div>
        </div>
      </div>
    </div>
    
    <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
      <div class="cell">
        <span class="section-header">New Contacts </span><br>
        <button class="button hollow new-contact-buttons" id="c-7-days" onclick="load_line_chart( 'global-contacts-chart-div', null, 'days', 7 );set_buttons('new-contact-buttons', 'c-7-days' )">Last 7 days</button> 
        <button class="button new-contact-buttons" id="c-30-days"  onclick="load_line_chart( 'global-contacts-chart-div', null, 'days', 30 );set_buttons('new-contact-buttons', 'c-30-days' )">Last 30 days</button> 
        <button class="button hollow new-contact-buttons" id="c-60-days"  onclick="load_line_chart( 'global-contacts-chart-div', null, 'days', 60 );set_buttons('new-contact-buttons', 'c-60-days' )">Last 60 days</button> 
        <button class="button hollow new-contact-buttons" id="c-12-months"  onclick="load_line_chart( 'global-contacts-chart-div', null, 'months', 12 );set_buttons('new-contact-buttons', 'c-12-months' )">Last 12 Months</button>
        <button class="button hollow new-contact-buttons" id="c-24-months"  onclick="load_line_chart( 'global-contacts-chart-div', null, 'months', 24 );set_buttons('new-contact-buttons', 'c-24-months' )">Last 24 Months</button>
        <div id="global-contacts-chart-div" style="height:500px;width:100%;"></div>
      </div>
    </div>
    
    <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
      <div class="cell">
        <span class="section-header">New Groups </span><br>
        <button class="button hollow new-group-buttons" id="g-7-days" onclick="load_line_chart( 'global-groups-chart-div', null, 'days', 7 );set_buttons('new-group-buttons', 'g-7-days' )">Last 7 days</button> 
        <button class="button new-group-buttons" id="g-30-days"  onclick="load_line_chart( 'global-groups-chart-div', null, 'days', 30 );set_buttons('new-group-buttons', 'g-30-days' )">Last 30 days</button> 
        <button class="button hollow new-group-buttons" id="g-60-days"  onclick="load_line_chart( 'global-groups-chart-div', null, 'days', 60 );set_buttons('new-group-buttons', 'g-60-days' )">Last 60 days</button> 
        <button class="button hollow new-group-buttons" id="g-12-months"  onclick="load_line_chart( 'global-groups-chart-div', null, 'months', 12 );set_buttons('new-group-buttons', 'g-12-months' )">Last 12 Months</button>
        <button class="button hollow new-group-buttons" id="g-24-months"  onclick="load_line_chart( 'global-groups-chart-div', null, 'months', 24 );set_buttons('new-group-buttons', 'g-24-months' )">Last 24 Months</button>
        <div id="global-groups-chart-div" style="height:500px;width:100%;"></div>
      </div>
    </div>
    
  `);

    DRILLDOWN.get_drill_down('map_chart_drilldown', MAPPINGDATA.settings.current_map)

    load_line_chart('global-contacts-chart-div', null, 'days', 30)
    set_buttons('new-contact-buttons', 'c-30-days')

    load_line_chart('global-groups-chart-div', null, 'days', 30)
    set_buttons('new-group-buttons', 'g-30-days')

}

/**
 * List of Sites Page
 */

function load_line_chart(div, id, type, numberOfElements) {
    if ('group-line-chart-div'===div) {
        switch (type) {
            case 'days':
                line_chart(div, wpApiNetworkDashboard.sites[id].groups.added.sixty_days, numberOfElements)
                break;
            case 'months':
                line_chart(div, wpApiNetworkDashboard.sites[id].groups.added.twenty_four_months, numberOfElements, 'month')
                break;
        }
    } else if ('line-chart-div'===div) {
        switch (type) {
            case 'days':
                line_chart(div, wpApiNetworkDashboard.sites[id].contacts.added.sixty_days, numberOfElements)
                break;
            case 'months':
                line_chart(div, wpApiNetworkDashboard.sites[id].contacts.added.twenty_four_months, numberOfElements, 'month')
                break;
        }
    } else if ('user-activity-chart-div'===div) {
        switch (type) {
            case 'days':
                line_chart(div, wpApiNetworkDashboard.sites[id].users.login_activity.sixty_days, numberOfElements)
                break;
            case 'months':
                line_chart(div, wpApiNetworkDashboard.sites[id].users.login_activity.twenty_four_months, numberOfElements, 'month')
                break;
        }
    } else if ('global-contacts-chart-div'===div) {
        switch (type) {
            case 'days':
                line_chart(div, wpApiNetworkDashboard.global.contacts.added.sixty_days, numberOfElements)
                break;
            case 'months':
                line_chart(div, wpApiNetworkDashboard.global.contacts.added.twenty_four_months, numberOfElements, 'month')
                break;
        }
    } else if ('global-groups-chart-div'===div) {
        switch (type) {
            case 'days':
                line_chart(div, wpApiNetworkDashboard.global.groups.added.sixty_days, numberOfElements)
                break;
            case 'months':
                line_chart(div, wpApiNetworkDashboard.global.groups.added.twenty_four_months, numberOfElements, 'month')
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
                bar_chart('generations-div', wpApiNetworkDashboard.sites[id].contacts.baptisms.generations)
                break;
            case 'g-coaching':
                bar_chart('generations-div', wpApiNetworkDashboard.sites[id].contacts.coaching.generations)
                break;
            case 'g-groups':
                bar_chart('generations-div', wpApiNetworkDashboard.sites[id].groups.group_generations)
                break;
            case 'g-churches':
                bar_chart('generations-div', wpApiNetworkDashboard.sites[id].groups.church_generations)
                break;
        }

    }
}

function load_bar_chart(div, id) {
    if ( 'health-bar-chart-div' === div ) {
        health_stacked_bar_chart('health-bar-chart-div', wpApiNetworkDashboard.sites[id].groups.health)
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
        pie_chart('types-pie-chart-div', wpApiNetworkDashboard.sites[id].groups.by_types)
    } else if ('system-engagement-pie-chart-div'===div) {
        pie_chart('system-engagement-pie-chart-div', wpApiNetworkDashboard.sites[id].users.last_thirty_day_engagement)
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
        funnel_chart(div, wpApiNetworkDashboard.sites[id].groups.by_types, "vertical", true)
    } else if ('follow-up-funnel-chart-div'===div) {
        funnel_chart(div, wpApiNetworkDashboard.sites[id].contacts.follow_up_funnel.funnel, "horizontal")
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
