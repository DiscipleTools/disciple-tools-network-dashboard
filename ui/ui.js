_ = _ || window.lodash

jQuery(document).ready(function () {
    let sidemenu = jQuery('#metrics-sidemenu')
    sidemenu.prop('data-multi-expand', true)

  MAPPINGDATA.data = wpApiNetworkDashboard.locations_list
  if (!window.location.hash || '#network_home'===window.location.hash) {
    show_network_home()
  }
  if ('#sites'===window.location.hash) {
    show_sites_list()
  }
  if ('#mapping_view'===window.location.hash) {
    mapping_view()
  }
  if ('#mapping_list'===window.location.hash) {
    page_mapping_list()
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
    <span class="section-header">` + page.translations.sm_title + `</span>
    
    <hr style="max-width:100%;">
    <!--<div id="home-map-div" style="width: 100%;max-height: 600px;height: 100vh;"></div>-->
<!--    <div class="cell medium-6" id="map_chart" style="display:none;"></div>-->
    <div id="map_chart" style="width: 100%;max-height: 700px;height: 100vh;vertical-align: text-top;"></div>
    
    <hr style="max-width:100%;">
    
    <div class="grid-x grid-padding-x grid-padding-y">
      <div class="cell">
      <span class="section-header">Active Totals for All Sites </span><br>
        <div class="grid-x callout">
          <div class="medium-3 cell center">
            <h4>Contacts<br><span class="total_contacts">${page.global.contacts.total}</span></h4>
          </div>
          <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
            <h4>Groups<br><span class="total_groups">${page.global.groups.total}</span></h4>
          </div>
          <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
            <h4>Users<br><span id="total_users">${page.global.users.total}</span></h4>
          </div>
          <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
            <h4>Countries<br><span id="total_users">${page.global.locations.total_countries}</span></h4>
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
function show_sites_list() {
  "use strict";
  let page = wpApiNetworkDashboard
  let chartDiv = jQuery('#chart')
  chartDiv.empty().html(`
        <span class="section-header">` + page.translations.sm_title + `</span>
        
        <hr style="max-width:100%;">
        
        <div class="grid-x grid-padding-x">
            <div class="cell">
                <div id="list-sites"></div>
            </div>
        </div>
        
        `);

    load_site_lists('list-sites')
}

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

function load_points_map(div, id) {
  console.log(div);
  console.log(id);
  if ('site-map-div'===div) {
    // used on the single site page

    let locations = []
    jQuery.each(wpApiNetworkDashboard.locations_list.list, function (i, v) {
      if (v.level==='0' && v[id]) {
        locations.push(v)
      }
    })

    points_map_chart(div, locations)

  } else if ('home-map-div'===div) {
    //build locations list
    let locations = []
    jQuery.each(wpApiNetworkDashboard.locations_list.list, function (i, v) {
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

/**
 * Single Snapshots Page
 */
function show_network_site(id, name) {
    "use strict";
    if (id===undefined) {
        show_network_home()
        return
    }

    let data = wpApiNetworkDashboard.sites[id]

    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`
      <span class="section-header">${name}</span>
      <span style="font-size:.5em;color:lightslategrey;float:right;" onclick="jQuery('#site-id').show()">show id<span id="site-id" style="display:none;"><br>${id}</span></span>
      <hr style="max-width:100%;">
      
      <br>
      <span class="section-header">Active Snapshot</span>
      <div class="grid-x grid-padding-x grid-padding-y">
          <div class="cell">
              <div class="grid-x callout">
                  <div class="medium-4 cell center">
                  <h4>Contacts<br><span class="total_contacts">${data.contacts.current_state.status.active}</span></h4>
                  </div>
                  <div class="medium-4 cell center" style="border-left: 1px solid #ccc">
                  <h4>Groups<br><span class="total_groups">${data.groups.current_state.all}</span></h4>
                  </div>
                  <div class="medium-4 cell center" style="border-left: 1px solid #ccc">
                  <h4>Users<br><span id="total_users">${data.users.current_state.total_users}</span></h4>
                  </div>
              </div>
          </div>
      </div>
      
      <div class="grid-x grid-padding-x grid-padding-y">
          <div class="cell medium-6">
              Generations<br>
              <button class="button generation-buttons" id="g-baptisms" onclick="load_gen_chart( 'generations-div', '${id}', 'g-baptisms' );set_buttons('generation-buttons', 'g-baptisms' )">Baptisms</button> 
              <button class="button hollow generation-buttons" id="g-groups"  onclick="load_gen_chart( 'generations-div', '${id}', 'g-groups' );set_buttons('generation-buttons', 'g-groups' )">Groups</button> 
              <button class="button hollow generation-buttons" id="g-churches"  onclick="load_gen_chart( 'generations-div', '${id}', 'g-churches' );set_buttons('generation-buttons', 'g-churches' )">Churches</button> 
              <div id="generations-div" style="height:300px;width:100%;"></div>
          </div>
          <div class="cell medium-6">
              Location<br>
              <div id="site-map-div" style="height:300px;width:100%;"></div>
          </div>
      </div>
      
      <div class="grid-x grid-padding-y grid-margin-y">
          <div class="cell">
              <br><br>
              <hr>
          </div>
      </div>
      
      <!-- CONTACTS -->
      <span class="section-header">Contacts</span>
      <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
          <div class="cell">
              
              <div class="grid-x callout">
                  <div class="medium-3 cell center">
                  <h4>Active Contacts<br><span class="total_contacts">${data.contacts.current_state.status.active}</span></h4>
                  </div>
                  <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                  <h4>Paused Contacts<br><span class="total_groups">${data.contacts.current_state.status.paused}</span></h4>
                  </div>
                  <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                  <h4>Closed Contacts<br><span id="total_users">${data.contacts.current_state.status.closed}</span></h4>
                  </div>
                  <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                  <h4>Total Contacts<br><span id="total_users">${data.contacts.current_state.all_contacts}</span></h4>
                  </div>
              </div>
          </div>
      </div>
      
      <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
          <div class="cell">
              New Contacts <br>
              <button class="button hollow new-contact-buttons" id="c-7-days" onclick="load_line_chart( 'line-chart-div', '${id}', 'days', 7 );set_buttons('new-contact-buttons', 'c-7-days' )">Last 7 days</button> 
              <button class="button new-contact-buttons" id="c-30-days"  onclick="load_line_chart( 'line-chart-div', '${id}', 'days', 30 );set_buttons('new-contact-buttons', 'c-30-days' )">Last 30 days</button> 
              <button class="button hollow new-contact-buttons" id="c-60-days"  onclick="load_line_chart( 'line-chart-div', '${id}', 'days', 60 );set_buttons('new-contact-buttons', 'c-60-days' )">Last 60 days</button> 
              <button class="button hollow new-contact-buttons" id="c-12-months"  onclick="load_line_chart( 'line-chart-div', '${id}', 'months', 12 );set_buttons('new-contact-buttons', 'c-12-months' )">Last 12 Months</button>
              <button class="button hollow new-contact-buttons" id="c-24-months"  onclick="load_line_chart( 'line-chart-div', '${id}', 'months', 24 );set_buttons('new-contact-buttons', 'c-24-months' )">Last 24 Months</button>
              <div id="line-chart-div" style="height:500px;width:100%;"></div>
          </div>
      </div>
      
      <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
          <div class="cell medium-10">
              Follow-up Funnel
              <div id="follow-up-funnel-chart-div" style="height:400px;width:100%;"></div>
          </div>
          <div class="cell medium-2 center" style="border-left: 1px solid #ccc">
              <div class="grid-x grid-margin-y">
                  <div class="cell" style="padding-top:1.2em;"><h4>On-Going Meetings<br><span>${data.contacts.follow_up_funnel.ongoing_meetings}</span></h4></div>
                  <div class="cell" style="padding-top:1.2em;"><h4>Coaching<br><span>${data.contacts.follow_up_funnel.coaching}</span></h4></div>
                  <div class="cell" style="padding-top:1.2em;"><h4>Baptized<br><span>${data.contacts.baptisms.current_state.all_baptisms}</span></h4></div>
              </div>
          </div>
      </div>
      
     
      <div class="grid-x grid-padding-y grid-margin-y grid-margin-y">
          <div class="cell">
              <br><br>
              <hr>
          </div>
      </div>
      
      <!-- GROUPS -->
      <span class="section-header">Groups</span>
      <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
          <div class="cell">
              <div class="grid-x callout">
                  <div class="medium-3 cell center">
                  <h4>Active Groups<br><span class="total_contacts">${data.groups.current_state.all}</span></h4>
                  </div>
                  <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                  <h4>Pre-Group<br><span class="total_groups">${data.groups.current_state.active.pre_group}</span></h4>
                  </div>
                  <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                  <h4>Group<br><span id="total_users">${data.groups.current_state.active.group}</span></h4>
                  </div>
                  <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                  <h4>Church<br><span id="total_users">${data.groups.current_state.active.church}</span></h4>
                  </div>
              </div>
          </div>
      </div>
      
      <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
          <div class="cell">
              New Groups <br>
              <button class="button hollow new-group-buttons" id="g-7-days" onclick="load_line_chart( 'group-line-chart-div', '${id}', 'days', 7 );set_buttons('new-group-buttons', 'g-7-days' )">Last 7 days</button> 
              <button class="button new-group-buttons" id="g-30-days"  onclick="load_line_chart( 'group-line-chart-div', '${id}', 'days', 30 );set_buttons('new-group-buttons', 'g-30-days' )">Last 30 days</button> 
              <button class="button hollow new-group-buttons" id="g-60-days"  onclick="load_line_chart( 'group-line-chart-div', '${id}', 'days', 60 );set_buttons('new-group-buttons', 'g-60-days' )">Last 60 days</button> 
              <button class="button hollow new-group-buttons" id="g-12-months"  onclick="load_line_chart( 'group-line-chart-div', '${id}', 'months', 12 );set_buttons('new-group-buttons', 'g-12-months' )">Last 12 Months</button>
              <button class="button hollow new-group-buttons" id="g-24-months"  onclick="load_line_chart( 'group-line-chart-div', '${id}', 'months', 24 );set_buttons('new-group-buttons', 'g-24-months' )">Last 24 Months</button>
              <div id="group-line-chart-div" style="height:500px;width:100%;"></div>
          </div>
      </div>
      
      <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
          
          <div class="cell medium-6">
              Health Metrics
              <div id="health-bar-chart-div" style="height:500px;width:100%;"></div>
          </div>
          <div class="cell medium-6">
              Chart Funnel
              <div id="church-funnel-chart-div" style="height:500px;width:100%;"></div>
          </div>
      </div>
      
      <div class="grid-x grid-padding-y grid-margin-y">
          <div class="cell">
              <br><br>
              <hr>
          </div>
      </div>
      
      <!-- USERS -->
      
      <span class="section-header">Users</span>
      <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
          <div class="cell">
              <div class="grid-x callout">
                  <div class="medium-3 cell center">
                  <h4>Total Users<br><span class="total_contacts">${data.users.current_state.total_users}</span></h4>
                  </div>
                  <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                  <h4>Responders<br><span id="total_users">${data.users.current_state.roles.responders}</span></h4>
                  </div>
                  <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                  <h4>Dispatchers<br><span id="total_users">${data.users.current_state.roles.dispatchers}</span></h4>
                  </div>
                  <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                  <h4>Multipliers<br><span class="total_groups">${data.users.current_state.roles.multipliers}</span></h4>
                  </div>
                  <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                  <h4>Admins<br><span id="total_users">${data.users.current_state.roles.admins}</span></h4>
                  </div>
              </div>
          </div>
      </div>
      
      <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
          <div class="cell">
              User Login Activity <br>
              <button class="button hollow active-user-buttons" id="ua-7-days" onclick="load_line_chart( 'user-activity-chart-div', '${id}', 'days', 7 );set_buttons('active-user-buttons', 'ua-7-days' )">Last 7 days</button> 
              <button class="button active-user-buttons" id="ua-30-days" onclick="load_line_chart( 'user-activity-chart-div', '${id}', 'days', 30 );set_buttons('active-user-buttons', 'ua-30-days' )">Last 30 days</button> 
              <button class="button hollow active-user-buttons" id="ua-60-days" onclick="load_line_chart( 'user-activity-chart-div', '${id}', 'days', 60 );set_buttons('active-user-buttons', 'ua-60-days' )">Last 60 days</button> 
              <button class="button hollow active-user-buttons" id="ua-12-months" onclick="load_line_chart( 'user-activity-chart-div', '${id}', 'months', 12 );set_buttons('active-user-buttons', 'ua-12-months' )">Last 12 Months</button>
              <button class="button hollow active-user-buttons" id="ua-24-months" onclick="load_line_chart( 'user-activity-chart-div', '${id}', 'months', 24 );set_buttons('active-user-buttons', 'ua-24-months' )">Last 24 Months</button>
              <div id="user-activity-chart-div" style="height:500px;width:100%;"></div>
          </div>
      </div>
      
      <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
          <div class="cell medium-6">
              Users Active in the Last 30 Days
              <div id="system-engagement-pie-chart-div" style="height:400px;width:100%;"></div>
          </div>
          <div class="cell medium-6">
              
          </div>
      </div>
              
  `);

    load_line_chart('line-chart-div', id, 'days', 30)
    set_buttons('new-contact-buttons', 'c-30-days')

    load_points_map('site-map-div', id)
    load_gen_chart('generations-div', id, 'g-baptisms')

    load_line_chart('group-line-chart-div', id, 'days', 30)
    set_buttons('new-group-buttons', 'g-30-days')
    load_funnel_chart('follow-up-funnel-chart-div', id)

    load_bar_chart('health-bar-chart-div', id)
    load_funnel_chart('church-funnel-chart-div', id)

    load_pie_chart('system-engagement-pie-chart-div', id)

    load_line_chart('user-activity-chart-div', id, 'days', 30)
    set_buttons('active-user-buttons', 'ua-30-days')

    jQuery('#metrics-sidemenu').foundation('down', jQuery('#sites-list'));
}


/**
 * Site Lists Page
 * @param div
 */
function load_site_lists(div) {
  let list = jQuery('#' + div + '')
  list.empty().html(`
    <table id="site-table" class="display" data-order='[[ 1, "asc" ]]' data-page-length='25'>
      <thead>
        <th>ID</th>
        <th>Name</th>
        <th>Contacts</th>
        <th>Groups</th>
        <th>Users</th>
        <th>Timestamp</th>
        <th>Visit</th>
      </thead>
      </table>
  `)


  let table = jQuery('#site-table').DataTable({
    data: wpApiNetworkDashboard.sites_list,
    columns: [
      {data: 'id'},
      {data: 'name'},
      {data: 'contacts'},
      {data: 'groups'},
      {data: 'users'},
      {data: 'date'},
      {data: null, "defaultContent": "<button class='button small' style='margin:0' >View</button>"}
    ],
    "columnDefs": [
      {
        "targets": [0],
        "visible": false
      }
    ]
  });

  jQuery('#site-table tbody').on('click', "button", function (event) {
    jQuery(this).parent().append(wpApiNetworkDashboard.spinner_large)
    let data = table.row(jQuery(this).parents('tr')).data();
    show_network_site(data['id'], data['name'])
    window.scrollTo(0, 0);
  })
}

function mapping_view(grid_id) {
  "use strict";
  let chartDiv = jQuery('#chart')
  chartDiv.empty().html(`
    <div id="mapping_chart"></div>
  `)

  page_mapping_view()

}


let LISTDATA = MAPPINGDATA
function page_mapping_list() {
  "use strict";
  let chartDiv = jQuery('#chart')
  chartDiv.empty().html(`
    <style>
      .map_wrapper {}
      .map_header_wrapper {
          float:left;
          position:absolute;
      }
      .section_title {
          font-size:1.5em;
      }
      .current_level {}
      .location_list {
      }
      .map_hr {
        max-width:100%;
        margin: 10px 0;
      }
      @media screen and (max-width : 640px){
        #country-list-table {
          margin-left: 5px !important;
        }
        .map_header_wrapper {
          position:relative;
          text-align: center;
          width: 100%;
        }
      }
    </style>

    <!-- List Widget -->
    <div id="map_wrapper" class="map_wrapper">
      <div id="map_drill_wrapper" class="grid-x grid-margin-x map_drill_wrapper">
        <div id="location_list_drilldown" class="cell auto location_list_drilldown"></div>
      </div>
      <hr id="map_hr_1" class="map_hr">
      
      <div id="map_header_wrapper" class="map_header_wrapper">
        <strong id="section_title" class="section_title" ></strong><br>
        <span id="current_level" class="current_level"></span>
      </div>
      
      <div id="location_list" class="location_list"></div>
      <hr id="map_hr_2" class="map_hr">
    </div> <!-- end widget -->
  `);

  if ( LISTDATA.data ){
    window.DRILLDOWN.get_drill_down('location_list_drilldown', LISTDATA.settings.current_map, LISTDATA.settings.cached)
  } else {
    get_data(false).then(response=>{
      LISTDATA.data = response
      // set the depth of the drill down
      LISTDATA.settings.hide_final_drill_down = false
      // load drill down
      window.DRILLDOWN.get_drill_down('location_list_drilldown', LISTDATA.settings.current_map, LISTDATA.settings.cached)
    }).fail(err=>{
      console.log(err)
    })
  }
}

window.DRILLDOWN.location_list_drilldown = function( grid_id ) {
  location_grid_list( 'location_list', grid_id )
}
function get_data( force_refresh = false ) {
  let spinner = jQuery('.loading-spinner')
  spinner.addClass('active')
  return jQuery.ajax({
    type: "GET",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: `${window.wp_js_object.rest_endpoints_base}/data?refresh=${force_refresh}`,
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', window.wp_js_object.nonce );
    },
  })
  .then( function( response ) {
    spinner.removeClass('active')
    return response
  })
  .fail(function (err) {
    spinner.removeClass('active')
    console.log("error")
    console.log(err)
  })
}

function location_grid_list( div, grid_id ) {
  DRILLDOWN.show_spinner()

  // Find data source before build
  if ( grid_id === 'top_map_level' ) {
    let map_data = null
    let default_map_settings = LISTDATA.settings.default_map_settings

    if ( DRILLDOWN.isEmpty( default_map_settings.children ) ) {
      map_data = LISTDATA.data[default_map_settings.parent]
    }
    else {
      if ( default_map_settings.children.length < 2 ) {
        // single child
        map_data = LISTDATA.data[default_map_settings.children[0]]
      } else {
        // multiple child
        jQuery('#section_title').empty()
        jQuery('#current_level').empty()
        jQuery('#location_list').empty().append('Select Location')
        DRILLDOWN.hide_spinner()
        return;
      }
    }

    // Initialize Location Data
    if ( map_data === undefined ) {
      console.log('error getting map_data')
      return;
    }

    build_location_grid_list( div, map_data )
  }
  else if ( LISTDATA.data[grid_id] === undefined ) {
    let rest = LISTDATA.settings.endpoints.get_map_by_grid_id_endpoint

    jQuery.ajax({
      type: rest.method,
      contentType: "application/json; charset=utf-8",
      data: JSON.stringify( { 'grid_id': grid_id, 'cached': LISTDATA.settings.cached, 'cached_length': LISTDATA.settings.cached_length } ),
      dataType: "json",
      url: LISTDATA.settings.root + rest.namespace + rest.route,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', rest.nonce );
      },
    })
    .done( function( response ) {
      LISTDATA.data[grid_id] = response
      build_location_grid_list( div, LISTDATA.data[grid_id] )
    })
    .fail(function (err) {
      console.log("error")
      console.log(err)
      DRILLDOWN.hide_spinner()
    })

  } else {
    build_location_grid_list( div, LISTDATA.data[grid_id] )
  }

  // build list
  function build_location_grid_list( div, map_data ) {
    let translations = window.mappingModule.mapping_module.translations

    // Place Title
    let title = jQuery('#section_title')
    title.empty().html(map_data.self.name)

    // Population Division and Check for Custom Division
    let pd_settings = LISTDATA.settings.population_division
    let population_division = pd_settings.base
    if ( ! DRILLDOWN.isEmpty( pd_settings.custom ) ) {
      jQuery.each( pd_settings.custom, function(i,v) {
        if ( map_data.self.grid_id === i ) {
          population_division = v
        }
      })
    }

    // Self Data
    let self_population = map_data.self.population_formatted
    jQuery('#current_level').empty().html(`${_.escape(translations.population)}: ${_.escape( self_population )}`)

    // Build List
    let locations = jQuery('#location_list')
    locations.empty()

    let html = `<table id="country-list-table" class="display">`

    // Header Section
    html += `<thead><tr><th>${_.escape(translations.name)}</th><th>${_.escape(translations.population)}</th>`

    /* Additional Columns */
    if ( LISTDATA.data.custom_column_labels ) {
      jQuery.each( LISTDATA.data.custom_column_labels, function(i,v) {
        html += `<th>${_.escape( v.label )}</th>`
      })
    }
    /* End Additional Columns */

    html += `</tr></thead>`
    // End Header Section

    // Children List Section
    let sorted_children =  _.sortBy(map_data.children, [function(o) { return o.name; }]);

    html += `<tbody>`

    jQuery.each( sorted_children, function(i, v) {
      let population = v.population_formatted

      html += `<tr>
        <td><strong><a onclick="DRILLDOWN.get_drill_down('location_list_drilldown', ${_.escape( v.grid_id )} )">${_.escape( v.name )}</a></strong></td>
        <td>${_.escape( population )}</td>`

      /* Additional Columns */
      if ( LISTDATA.data.custom_column_data[v.grid_id] ) {
        jQuery.each( LISTDATA.data.custom_column_data[v.grid_id], function(ii,vv) {
          html += `<td><strong>${_.escape( vv )}</strong></td>`
        })
      } else {
        jQuery.each( LISTDATA.data.custom_column_labels, function(ii,vv) {
          html += `<td class="grey">0</td>`
        })
      }
      /* End Additional Columns */

      html += `</tr>`

    })
    html += `</tbody>`
    // end Child section

    html += `</table>`
    locations.append(html)

    let isMobile = window.matchMedia("only screen and (max-width: 760px)").matches;

    if (isMobile) {
      jQuery('#country-list-table').DataTable({
        "paging":   false,
        "scrollX": true
      });
    } else {
      jQuery('#country-list-table').DataTable({
        "paging":   false
      });
    }

    DRILLDOWN.hide_spinner()
  }
}


