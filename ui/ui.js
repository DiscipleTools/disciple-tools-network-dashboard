jQuery(document).ready(function() {
    if( ! window.location.hash || '#network_home' === window.location.hash) {
        show_network_home()
    }
    if('#sites' === window.location.hash) {
        show_sites_list()
    }
    if( '#mapping_view' === window.location.hash) {
        page_mapping_view()
    }
    if('#mapping_list' === window.location.hash) {
        page_mapping_list()
    }
})

/**
 * Home Page
 */
function show_network_home(){
    console.log(wpApiNetworkDashboard)
    "use strict";
    let page = wpApiNetworkDashboard
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`
        <span class="section-header">`+ page.translations.sm_title +`</span>
        
        <hr style="max-width:100%;">
        <div id="home-map-div" style="width: 100%;max-height: 600px;height: 100vh;"></div>
        
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

    load_points_map( 'home-map-div' )

    load_line_chart( 'global-contacts-chart-div', null, 'days', 30 )
    set_buttons('new-contact-buttons', 'c-30-days' )

    load_line_chart( 'global-groups-chart-div', null, 'days', 30 )
    set_buttons('new-group-buttons', 'g-30-days' )

}


/**
 * Single Snapshots Page
 */
function show_network_site( id, name ) {
    console.log(wpApiNetworkDashboard)
    "use strict";
    if ( id == undefined ) {
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

    load_line_chart( 'line-chart-div', id, 'days', 30 )
    set_buttons('new-contact-buttons', 'c-30-days' )

    load_points_map( 'site-map-div', id )
    load_gen_chart( 'generations-div', id, 'g-baptisms' )

    load_line_chart( 'group-line-chart-div', id, 'days', 30 )
    set_buttons('new-group-buttons', 'g-30-days' )
    load_funnel_chart( 'follow-up-funnel-chart-div', id )

    load_bar_chart( 'health-bar-chart-div', id )
    load_funnel_chart( 'church-funnel-chart-div', id )

    load_pie_chart( 'system-engagement-pie-chart-div', id )

    load_line_chart( 'user-activity-chart-div', id, 'days', 30 )
    set_buttons('active-user-buttons', 'ua-30-days' )

    jQuery('#metrics-sidemenu').foundation('down', jQuery('#sites-list'));
}

/**
 * List of Sites Page
 */
function show_sites_list() {
    "use strict";
    let page = wpApiNetworkDashboard
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`
        <span class="section-header">`+ page.translations.sm_title +`</span>
        
        <hr style="max-width:100%;">
        
        <div class="grid-x grid-padding-x">
            <div class="cell">
                <div id="list-sites"></div>
            </div>
        </div>
        
        `);

    jQuery(document).ready(function() {
        load_site_lists( 'list-sites' )
    })
}

function load_line_chart( div, id, type, numberOfElements ) {
    if ( 'group-line-chart-div' === div ) {
        switch ( type ) {
            case 'days':
                line_chart( div, wpApiNetworkDashboard.sites[id].groups.added.sixty_days, numberOfElements )
                break;
            case 'months':
                line_chart( div, wpApiNetworkDashboard.sites[id].groups.added.twenty_four_months, numberOfElements, 'month' )
                break;
        }
    } else if ( 'line-chart-div' === div ) {
        switch ( type ) {
            case 'days':
                line_chart( div, wpApiNetworkDashboard.sites[id].contacts.added.sixty_days, numberOfElements )
                break;
            case 'months':
                line_chart( div, wpApiNetworkDashboard.sites[id].contacts.added.twenty_four_months, numberOfElements, 'month' )
                break;
        }
    } else if ( 'user-activity-chart-div' === div ) {
        switch ( type ) {
            case 'days':
                line_chart( div, wpApiNetworkDashboard.sites[id].users.login_activity.sixty_days, numberOfElements )
                break;
            case 'months':
                line_chart( div, wpApiNetworkDashboard.sites[id].users.login_activity.twenty_four_months, numberOfElements, 'month')
                break;
        }
    } else if ( 'global-contacts-chart-div' === div ) {
        switch ( type ) {
            case 'days':
                line_chart( div, wpApiNetworkDashboard.global.contacts.added.sixty_days, numberOfElements )
                break;
            case 'months':
                line_chart( div, wpApiNetworkDashboard.global.contacts.added.twenty_four_months, numberOfElements, 'month' )
                break;
        }
    } else if ( 'global-groups-chart-div' === div ) {
        switch ( type ) {
            case 'days':
                line_chart( div, wpApiNetworkDashboard.global.groups.added.sixty_days, numberOfElements )
                break;
            case 'months':
                line_chart( div, wpApiNetworkDashboard.global.groups.added.twenty_four_months, numberOfElements, 'month' )
                break;
        }
    }
}

function set_buttons( buttonClassName, id ) {
    jQuery('.'+buttonClassName+'').addClass('hollow')
    jQuery('#'+id+'').removeClass('hollow')
}

function line_chart( div, dates, numberOfElements, type  ) {

    // Themes begin
    am4core.useTheme(am4themes_animated);
    let chart = am4core.create( div , am4charts.XYChart);

    chart.paddingRight = 30;
    chart.paddingBottom = 50;

    chart.data = dates.slice(0, numberOfElements);

    // Create axes
    var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
    dateAxis.renderer.minGridDistance = 60;
    if ( 'month' === type ) {
        dateAxis.tooltipDateFormat = "MMM YYYY";
    }


    var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());

    // Create series
    var series = chart.series.push(new am4charts.LineSeries());
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

function load_gen_chart( div, id, type ) {
    if ( 'generations-div' === div ) {
        switch ( type ) {
            case 'g-baptisms':
                bar_chart( 'generations-div', wpApiNetworkDashboard.sites[id].contacts.baptisms.generations )
                break;
            case 'g-coaching':
                bar_chart( 'generations-div', wpApiNetworkDashboard.sites[id].contacts.coaching.generations )
                break;
            case 'g-groups':
                bar_chart( 'generations-div', wpApiNetworkDashboard.sites[id].groups.group_generations )
                break;
            case 'g-churches':
                bar_chart( 'generations-div', wpApiNetworkDashboard.sites[id].groups.church_generations )
                break;
        }

    }
}

function load_bar_chart( div, id ) {
    if ( 'health-bar-chart-div' ) {
        health_stacked_bar_chart( 'health-bar-chart-div', wpApiNetworkDashboard.sites[id].groups.health )
    }
}

function health_stacked_bar_chart( div, values ) {
    am4core.useTheme(am4themes_animated);
    var chart = am4core.create( div, am4charts.XYChart);

    chart.data = values;

    // Create axes
    var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
    categoryAxis.dataFields.category = "category";
    categoryAxis.renderer.grid.template.location = 0.5;
    categoryAxis.renderer.labels.template.rotation = -90;
    categoryAxis.renderer.minGridDistance = 30;

    var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
    valueAxis.renderer.inside = true;
    valueAxis.renderer.labels.template.disabled = true;
    valueAxis.min = 0;

    // Create series
    function createSeries(field, name) {

        // Set up series
        var series = chart.series.push(new am4charts.ColumnSeries());
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
        var labelBullet = series.bullets.push(new am4charts.LabelBullet());
        labelBullet.label.text = "{valueY}";
        labelBullet.locationY = 0.5;

        return series;
    }
    createSeries('practicing', 'Practicing')
    createSeries('not_practicing', 'Not Practicing')


    // Legend
    chart.legend = new am4charts.Legend();
}

function bar_chart( div, values ) {
    // Themes begin
    am4core.useTheme(am4themes_animated);
    // Themes end

    // Create chart instance
    var chart = am4core.create( div, am4charts.XYChart);

    // Add data
    chart.data = values;

    // Create axes

    var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
    categoryAxis.dataFields.category = "label";
    categoryAxis.renderer.grid.template.location = 0;
    categoryAxis.renderer.minGridDistance = 30;

    categoryAxis.renderer.labels.template.adapter.add("dy", function(dy, target) {
        if (target.dataItem && target.dataItem.index & 2 == 2) {
            return dy + 25;
        }
        return dy;
    });

    var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());

    // Create series
    var series = chart.series.push(new am4charts.ColumnSeries());
    series.dataFields.valueY = "value";
    series.dataFields.categoryX = "label";
    series.name = "Visits";
    series.columns.template.tooltipText = "{categoryX}: [bold]{valueY}[/]";
    series.columns.template.fillOpacity = .8;

    var columnTemplate = series.columns.template;
    columnTemplate.strokeWidth = 2;
    columnTemplate.strokeOpacity = 1;
}

function load_pie_chart( div, id ) {
    if ( 'types-pie-chart-div' === div ) {
        pie_chart( 'types-pie-chart-div', wpApiNetworkDashboard.sites[id].groups.by_types )
    } else if ( 'system-engagement-pie-chart-div' === div ) {
        pie_chart( 'system-engagement-pie-chart-div', wpApiNetworkDashboard.sites[id].users.last_thirty_day_engagement )
    }
}

function pie_chart( div, values ) {
    am4core.useTheme(am4themes_animated);

    // Create chart instance
    var chart = am4core.create( div, am4charts.PieChart);

    chart.data = values

    var pieSeries = chart.series.push(new am4charts.PieSeries());
    pieSeries.dataFields.value = "value";
    pieSeries.dataFields.category = "label";
    pieSeries.labels.template.disabled = true;
    pieSeries.ticks.template.disabled = true;
    pieSeries.slices.template.stroke = am4core.color("#ffffff");
    pieSeries.slices.template.strokeWidth = 1;

    chart.innerRadius = am4core.percent(25);

    chart.legend = new am4charts.Legend();
}

function load_funnel_chart( div, id ) {
    if ( 'church-funnel-chart-div' === div ) {
        funnel_chart( div, wpApiNetworkDashboard.sites[id].groups.by_types, "vertical", true )
    } else if ( 'follow-up-funnel-chart-div' === div ) {
        funnel_chart( div, wpApiNetworkDashboard.sites[id].contacts.follow_up_funnel.funnel, "horizontal" )
    }
}

function funnel_chart( div, chartData, direction, labels ) {
    am4core.useTheme(am4themes_animated);

    var chart = am4core.create( div, am4charts.SlicedChart);
    chart.hiddenState.properties.opacity = 0; // this makes initial fade in effect

    chart.data = chartData

    var series = chart.series.push(new am4charts.FunnelSeries());
    series.colors.step = 2;
    series.dataFields.value = "value";
    series.dataFields.category = "name";

    if ( true === labels ) {
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
    chart.legend.margin(5,5,20,5);
}

function load_points_map( div, id ) {
    if ( 'site-map-div' === div ) {
        // used on the single site page
        let locations = []
        jQuery.each( wpApiNetworkDashboard.sites, function(i, v) {
            jQuery.each( v.locations.countries, function(ii, vv ) {
                locations.push( vv )
            })
        } )

        points_map_chart( div, locations )
    } else if ( 'home-map-div' === div ) {
        //build locations list
        let locations = []
        jQuery.each( wpApiNetworkDashboard.sites, function(i, v) {
            jQuery.each( v.locations.countries, function(ii, vv ) {
                locations.push( vv )
            })
        } )

        points_map_chart( div, locations )
    }
}

function points_map_chart( div, values ) {
    // Themes begin
    am4core.useTheme(am4themes_animated);
    // Create map instance
    var chart = am4core.create( div, am4maps.MapChart);

    var latlong = get_countries_geolocations();

    var mapData = values;
    // console.log(values)

    // Add lat/long information to data
    for(var i = 0; i < mapData.length; i++) {
        mapData[i].latitude = latlong[mapData[i].id].latitude;
        mapData[i].longitude = latlong[mapData[i].id].longitude;
    }

    // Set map definition
    chart.geodata = am4geodata_worldLow;
    // Set projection
    chart.projection = new am4maps.projections.Miller();

    // Create map polygon series
    var polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());
    polygonSeries.exclude = ["AQ"];
    polygonSeries.useGeodata = true;

    var imageSeries = chart.series.push(new am4maps.MapImageSeries());
    imageSeries.data = mapData;
    imageSeries.dataFields.value = "value";

    var imageTemplate = imageSeries.mapImages.template;
    imageTemplate.propertyFields.latitude = "latitude";
    imageTemplate.propertyFields.longitude = "longitude";
    imageTemplate.nonScaling = true

    var circle = imageTemplate.createChild(am4core.Circle);
    circle.fillOpacity = 0.7;
    circle.propertyFields.fill = "color";
    circle.tooltipText = "{name}: \n [bold]{site_name} \n [bold]contacts:{contacts} \n [bold]groups:{groups}";

    imageSeries.heatRules.push({
        "target": circle,
        "property": "radius",
        "min": 15,
        "max": 20,
        "dataField": "value"
    })


}

function get_countries_geolocations() {
    var latlong = {
        "AD": {"latitude":42.5, "longitude":1.5},
        "AE": {"latitude":24, "longitude":54},
        "AF": {"latitude":33, "longitude":65},
        "AG": {"latitude":17.05, "longitude":-61.8},
        "AI": {"latitude":18.25, "longitude":-63.1667},
        "AL": {"latitude":41, "longitude":20},
        "AM": {"latitude":40, "longitude":45},
        "AN": {"latitude":12.25, "longitude":-68.75},
        "AO": {"latitude":-12.5, "longitude":18.5},
        "AP": {"latitude":35, "longitude":105},
        "AQ": {"latitude":-90, "longitude":0},
        "AR": {"latitude":-34, "longitude":-64},
        "AS": {"latitude":-14.3333, "longitude":-170},
        "AT": {"latitude":47.3333, "longitude":13.3333},
        "AU": {"latitude":-27, "longitude":133},
        "AW": {"latitude":12.5, "longitude":-69.9667},
        "AZ": {"latitude":40.5, "longitude":47.5},
        "BA": {"latitude":44, "longitude":18},
        "BB": {"latitude":13.1667, "longitude":-59.5333},
        "BD": {"latitude":24, "longitude":90},
        "BE": {"latitude":50.8333, "longitude":4},
        "BF": {"latitude":13, "longitude":-2},
        "BG": {"latitude":43, "longitude":25},
        "BH": {"latitude":26, "longitude":50.55},
        "BI": {"latitude":-3.5, "longitude":30},
        "BJ": {"latitude":9.5, "longitude":2.25},
        "BM": {"latitude":32.3333, "longitude":-64.75},
        "BN": {"latitude":4.5, "longitude":114.6667},
        "BO": {"latitude":-17, "longitude":-65},
        "BR": {"latitude":-10, "longitude":-55},
        "BS": {"latitude":24.25, "longitude":-76},
        "BT": {"latitude":27.5, "longitude":90.5},
        "BV": {"latitude":-54.4333, "longitude":3.4},
        "BW": {"latitude":-22, "longitude":24},
        "BY": {"latitude":53, "longitude":28},
        "BZ": {"latitude":17.25, "longitude":-88.75},
        "CA": {"latitude":54, "longitude":-100},
        "CC": {"latitude":-12.5, "longitude":96.8333},
        "CD": {"latitude":0, "longitude":25},
        "CF": {"latitude":7, "longitude":21},
        "CG": {"latitude":-1, "longitude":15},
        "CH": {"latitude":47, "longitude":8},
        "CI": {"latitude":8, "longitude":-5},
        "CK": {"latitude":-21.2333, "longitude":-159.7667},
        "CL": {"latitude":-30, "longitude":-71},
        "CM": {"latitude":6, "longitude":12},
        "CN": {"latitude":35, "longitude":105},
        "CO": {"latitude":4, "longitude":-72},
        "CR": {"latitude":10, "longitude":-84},
        "CU": {"latitude":21.5, "longitude":-80},
        "CV": {"latitude":16, "longitude":-24},
        "CX": {"latitude":-10.5, "longitude":105.6667},
        "CY": {"latitude":35, "longitude":33},
        "CZ": {"latitude":49.75, "longitude":15.5},
        "DE": {"latitude":51, "longitude":9},
        "DJ": {"latitude":11.5, "longitude":43},
        "DK": {"latitude":56, "longitude":10},
        "DM": {"latitude":15.4167, "longitude":-61.3333},
        "DO": {"latitude":19, "longitude":-70.6667},
        "DZ": {"latitude":28, "longitude":3},
        "EC": {"latitude":-2, "longitude":-77.5},
        "EE": {"latitude":59, "longitude":26},
        "EG": {"latitude":27, "longitude":30},
        "EH": {"latitude":24.5, "longitude":-13},
        "ER": {"latitude":15, "longitude":39},
        "ES": {"latitude":40, "longitude":-4},
        "ET": {"latitude":8, "longitude":38},
        "EU": {"latitude":47, "longitude":8},
        "FI": {"latitude":62, "longitude":26},
        "FJ": {"latitude":-18, "longitude":175},
        "FK": {"latitude":-51.75, "longitude":-59},
        "FM": {"latitude":6.9167, "longitude":158.25},
        "FO": {"latitude":62, "longitude":-7},
        "FR": {"latitude":46, "longitude":2},
        "GA": {"latitude":-1, "longitude":11.75},
        "GB": {"latitude":54, "longitude":-2},
        "GD": {"latitude":12.1167, "longitude":-61.6667},
        "GE": {"latitude":42, "longitude":43.5},
        "GF": {"latitude":4, "longitude":-53},
        "GH": {"latitude":8, "longitude":-2},
        "GI": {"latitude":36.1833, "longitude":-5.3667},
        "GL": {"latitude":72, "longitude":-40},
        "GM": {"latitude":13.4667, "longitude":-16.5667},
        "GN": {"latitude":11, "longitude":-10},
        "GP": {"latitude":16.25, "longitude":-61.5833},
        "GQ": {"latitude":2, "longitude":10},
        "GR": {"latitude":39, "longitude":22},
        "GS": {"latitude":-54.5, "longitude":-37},
        "GT": {"latitude":15.5, "longitude":-90.25},
        "GU": {"latitude":13.4667, "longitude":144.7833},
        "GW": {"latitude":12, "longitude":-15},
        "GY": {"latitude":5, "longitude":-59},
        "HK": {"latitude":22.25, "longitude":114.1667},
        "HM": {"latitude":-53.1, "longitude":72.5167},
        "HN": {"latitude":15, "longitude":-86.5},
        "HR": {"latitude":45.1667, "longitude":15.5},
        "HT": {"latitude":19, "longitude":-72.4167},
        "HU": {"latitude":47, "longitude":20},
        "ID": {"latitude":-5, "longitude":120},
        "IE": {"latitude":53, "longitude":-8},
        "IL": {"latitude":31.5, "longitude":34.75},
        "IN": {"latitude":20, "longitude":77},
        "IO": {"latitude":-6, "longitude":71.5},
        "IQ": {"latitude":33, "longitude":44},
        "IR": {"latitude":32, "longitude":53},
        "IS": {"latitude":65, "longitude":-18},
        "IT": {"latitude":42.8333, "longitude":12.8333},
        "JM": {"latitude":18.25, "longitude":-77.5},
        "JO": {"latitude":31, "longitude":36},
        "JP": {"latitude":36, "longitude":138},
        "KE": {"latitude":1, "longitude":38},
        "KG": {"latitude":41, "longitude":75},
        "KH": {"latitude":13, "longitude":105},
        "KI": {"latitude":1.4167, "longitude":173},
        "KM": {"latitude":-12.1667, "longitude":44.25},
        "KN": {"latitude":17.3333, "longitude":-62.75},
        "KP": {"latitude":40, "longitude":127},
        "KR": {"latitude":37, "longitude":127.5},
        "KW": {"latitude":29.3375, "longitude":47.6581},
        "KY": {"latitude":19.5, "longitude":-80.5},
        "KZ": {"latitude":48, "longitude":68},
        "LA": {"latitude":18, "longitude":105},
        "LB": {"latitude":33.8333, "longitude":35.8333},
        "LC": {"latitude":13.8833, "longitude":-61.1333},
        "LI": {"latitude":47.1667, "longitude":9.5333},
        "LK": {"latitude":7, "longitude":81},
        "LR": {"latitude":6.5, "longitude":-9.5},
        "LS": {"latitude":-29.5, "longitude":28.5},
        "LT": {"latitude":55, "longitude":24},
        "LU": {"latitude":49.75, "longitude":6},
        "LV": {"latitude":57, "longitude":25},
        "LY": {"latitude":25, "longitude":17},
        "MA": {"latitude":32, "longitude":-5},
        "MC": {"latitude":43.7333, "longitude":7.4},
        "MD": {"latitude":47, "longitude":29},
        "ME": {"latitude":42.5, "longitude":19.4},
        "MG": {"latitude":-20, "longitude":47},
        "MH": {"latitude":9, "longitude":168},
        "MK": {"latitude":41.8333, "longitude":22},
        "ML": {"latitude":17, "longitude":-4},
        "MM": {"latitude":22, "longitude":98},
        "MN": {"latitude":46, "longitude":105},
        "MO": {"latitude":22.1667, "longitude":113.55},
        "MP": {"latitude":15.2, "longitude":145.75},
        "MQ": {"latitude":14.6667, "longitude":-61},
        "MR": {"latitude":20, "longitude":-12},
        "MS": {"latitude":16.75, "longitude":-62.2},
        "MT": {"latitude":35.8333, "longitude":14.5833},
        "MU": {"latitude":-20.2833, "longitude":57.55},
        "MV": {"latitude":3.25, "longitude":73},
        "MW": {"latitude":-13.5, "longitude":34},
        "MX": {"latitude":23, "longitude":-102},
        "MY": {"latitude":2.5, "longitude":112.5},
        "MZ": {"latitude":-18.25, "longitude":35},
        "NA": {"latitude":-22, "longitude":17},
        "NC": {"latitude":-21.5, "longitude":165.5},
        "NE": {"latitude":16, "longitude":8},
        "NF": {"latitude":-29.0333, "longitude":167.95},
        "NG": {"latitude":10, "longitude":8},
        "NI": {"latitude":13, "longitude":-85},
        "NL": {"latitude":52.5, "longitude":5.75},
        "NO": {"latitude":62, "longitude":10},
        "NP": {"latitude":28, "longitude":84},
        "NR": {"latitude":-0.5333, "longitude":166.9167},
        "NU": {"latitude":-19.0333, "longitude":-169.8667},
        "NZ": {"latitude":-41, "longitude":174},
        "OM": {"latitude":21, "longitude":57},
        "PA": {"latitude":9, "longitude":-80},
        "PE": {"latitude":-10, "longitude":-76},
        "PF": {"latitude":-15, "longitude":-140},
        "PG": {"latitude":-6, "longitude":147},
        "PH": {"latitude":13, "longitude":122},
        "PK": {"latitude":30, "longitude":70},
        "PL": {"latitude":52, "longitude":20},
        "PM": {"latitude":46.8333, "longitude":-56.3333},
        "PR": {"latitude":18.25, "longitude":-66.5},
        "PS": {"latitude":32, "longitude":35.25},
        "PT": {"latitude":39.5, "longitude":-8},
        "PW": {"latitude":7.5, "longitude":134.5},
        "PY": {"latitude":-23, "longitude":-58},
        "QA": {"latitude":25.5, "longitude":51.25},
        "RE": {"latitude":-21.1, "longitude":55.6},
        "RO": {"latitude":46, "longitude":25},
        "RS": {"latitude":44, "longitude":21},
        "RU": {"latitude":60, "longitude":100},
        "RW": {"latitude":-2, "longitude":30},
        "SA": {"latitude":25, "longitude":45},
        "SB": {"latitude":-8, "longitude":159},
        "SC": {"latitude":-4.5833, "longitude":55.6667},
        "SD": {"latitude":15, "longitude":30},
        "SE": {"latitude":62, "longitude":15},
        "SG": {"latitude":1.3667, "longitude":103.8},
        "SH": {"latitude":-15.9333, "longitude":-5.7},
        "SI": {"latitude":46, "longitude":15},
        "SJ": {"latitude":78, "longitude":20},
        "SK": {"latitude":48.6667, "longitude":19.5},
        "SL": {"latitude":8.5, "longitude":-11.5},
        "SM": {"latitude":43.7667, "longitude":12.4167},
        "SN": {"latitude":14, "longitude":-14},
        "SO": {"latitude":10, "longitude":49},
        "SR": {"latitude":4, "longitude":-56},
        "ST": {"latitude":1, "longitude":7},
        "SV": {"latitude":13.8333, "longitude":-88.9167},
        "SY": {"latitude":35, "longitude":38},
        "SZ": {"latitude":-26.5, "longitude":31.5},
        "TC": {"latitude":21.75, "longitude":-71.5833},
        "TD": {"latitude":15, "longitude":19},
        "TF": {"latitude":-43, "longitude":67},
        "TG": {"latitude":8, "longitude":1.1667},
        "TH": {"latitude":15, "longitude":100},
        "TJ": {"latitude":39, "longitude":71},
        "TK": {"latitude":-9, "longitude":-172},
        "TM": {"latitude":40, "longitude":60},
        "TN": {"latitude":34, "longitude":9},
        "TO": {"latitude":-20, "longitude":-175},
        "TR": {"latitude":39, "longitude":35},
        "TT": {"latitude":11, "longitude":-61},
        "TV": {"latitude":-8, "longitude":178},
        "TW": {"latitude":23.5, "longitude":121},
        "TZ": {"latitude":-6, "longitude":35},
        "UA": {"latitude":49, "longitude":32},
        "UG": {"latitude":1, "longitude":32},
        "UM": {"latitude":19.2833, "longitude":166.6},
        "US": {"latitude":38, "longitude":-97},
        "UY": {"latitude":-33, "longitude":-56},
        "UZ": {"latitude":41, "longitude":64},
        "VA": {"latitude":41.9, "longitude":12.45},
        "VC": {"latitude":13.25, "longitude":-61.2},
        "VE": {"latitude":8, "longitude":-66},
        "VG": {"latitude":18.5, "longitude":-64.5},
        "VI": {"latitude":18.3333, "longitude":-64.8333},
        "VN": {"latitude":16, "longitude":106},
        "VU": {"latitude":-16, "longitude":167},
        "WF": {"latitude":-13.3, "longitude":-176.2},
        "WS": {"latitude":-13.5833, "longitude":-172.3333},
        "YE": {"latitude":15, "longitude":48},
        "YT": {"latitude":-12.8333, "longitude":45.1667},
        "ZA": {"latitude":-29, "longitude":24},
        "ZM": {"latitude":-15, "longitude":30},
        "ZW": {"latitude":-20, "longitude":30}
    };
    return latlong;
}


/**
 * Site Lists Page
 * @param div
 */
function load_site_lists( div ) {
    let list = jQuery('#'+div+'')
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


    let table = jQuery('#site-table').DataTable( {
        data: wpApiNetworkDashboard.sites_list,
        columns: [
            { data: 'id' },
            { data: 'name' },
            { data: 'contacts' },
            { data: 'groups' },
            { data: 'users' },
            { data: 'date' },
            { data: null,"defaultContent":"<button class='button small' style='margin:0' >View</button>"}
        ],
        "columnDefs": [
            {
                "targets": [0],
                "visible": false
            }
        ]
    });

    jQuery('#site-table tbody').on( 'click', "button", function ( event ) {
        jQuery(this).parent().append( wpApiNetworkDashboard.spinner_large )
        var data = table.row( jQuery(this).parents('tr') ).data();
        show_network_site( data['id'], data['name'] )
        window.scrollTo(0, 0);
    })
}

jQuery(document).ready(function() {
    if (typeof mappingModule != "undefined") {
        /* these two wrappers test that the mappingModule object is loaded */
        // console.log(mappingModule)

    }
})


function page_mapping_view() {
    console.log(DRILLDOWNDATA)
    "use strict";
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`
        
        <div class="grid-x grid-margin-y">
            <div class="cell medium-6" id="network_chart_drilldown"></div>
            <div class="cell medium-6" style="text-align:right;">
               <strong id="section-title" style="font-size:2em;"></strong><br>
                <span id="current_level"></span>
            </div>
        </div>
        
        <hr style="max-width:100%;">
        
       <!-- Map -->
       <div class="grid-x grid-margin-x">
            <div class="cell medium-10">
                <div id="map_chart" style="width: 100%;max-height: 700px;height: 100vh;vertical-align: text-top;"></div>
            </div>
            <div class="cell medium-2 left-border-grey">
                <div class="grid-y">
                    <div class="cell" style="overflow-y: scroll; height:700px; padding:0 .4em;" id="child-list-container">
                        <div id="minimap"></div><br><br>
                        <div class="button-group expanded stacked" id="data-type-list">
                         </div>
                    </div>
                </div>
            </div>
        </div>
        
        <hr style="max-width:100%;">
        
        <span style="float:right;font-size:.8em;"><a onclick="DRILLDOWN.get_drill_down('map_chart_drilldown')" >return to top level</a></span>
        <br>
        `);
// set the depth of the drill down
    DRILLDOWNDATA.settings.hide_final_drill_down = true
    DRILLDOWN.get_drill_down('network_chart_drilldown')

}

window.DRILLDOWN.network_chart_drilldown = function( geonameid ) {
    console.log('network_chart_drilldown')
}


function page_mapping_list() {
    console.log(DRILLDOWNDATA)
    "use strict";
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`
        <div class="grid-x grid-margin-x">
            <div class="cell auto" id="network_list_drilldown"></div>
            <div class="cell small-1">
                <span id="spinner" style="display:none;" class="float-right">${DRILLDOWNDATA.settings.spinner_large}</span>
            </div>
        </div>
        
        <hr style="max-width:100%;">
        
        <div id="page-header" style="float:left;">
            <strong id="section-title" style="font-size:1.5em;"></strong><br>
            <span id="current_level"></span>
        </div>
        
        <div id="location_list"></div>
        
        <hr style="max-width:100%;">
        
        <br>
        <style> /* @todo move these definitions to site style sheet. */
            #page-header {
                position:absolute;
            }
            @media screen and (max-width : 640px){
                #page-header {
                    position:relative;
                    text-align: center;
                    width: 100%;
                }
            }
           
        </style>
        `);

    // set the depth of the drill down
    DRILLDOWNDATA.settings.hide_final_drill_down = false
    DRILLDOWN.get_drill_down('network_list_drilldown')
}

window.DRILLDOWN.network_list_drilldown = function( geonameid ) {
    console.log('network_list_drilldown')
    let map_data = wpApiNetworkDashboard.locations_list


    // Place Title
    let title = jQuery('#section-title')
    title.empty().html(map_data.self.name)

    // Population Division and Check for Custom Division
    let pd_settings = DRILLDOWNDATA.settings.population_division
    let population_division = pd_settings.base
    if ( ! DRILLDOWN.isEmpty( pd_settings.custom ) ) {
        jQuery.each( pd_settings.custom, function(i,v) {
            if ( map_data.self.geonameid === i ) {
                population_division = v
            }
        })
    }

    // Self Data
    let self_population = map_data.self.population_formatted
    jQuery('#current_level').empty().html(`Population: ${self_population}`)

    // Build List
    let locations = jQuery('#location_list')
    locations.empty()

    let html = `<table id="country-list-table" class="display">`

    // Header Section
    html += `<thead><tr><th>Name</th><th>Population</th>`

    /* Additional Columns */
    if ( DRILLDOWNDATA.data.custom_column_labels ) {
        jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(i,v) {
            html += `<th>${v.label}</th>`
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
                    <td><strong><a onclick="DRILLDOWN.get_drill_down('network_list_drilldown', ${v.geonameid} )">${v.name}</a></strong></td>
                    <td>${population}</td>`

        /* Additional Columns */
        if ( DRILLDOWNDATA.data.custom_column_data[v.geonameid] ) {
            jQuery.each( DRILLDOWNDATA.data.custom_column_data[v.geonameid], function(ii,vv) {
                html += `<td><strong>${vv}</strong></td>`
            })
        } else {
            jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii,vv) {
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

    jQuery('#country-list-table').DataTable({
        "paging":   false
    });

    DRILLDOWN.hide_spinner()

}

