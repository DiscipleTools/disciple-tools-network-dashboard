
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
    } else if ('global-baptisms-chart-div'===div) {
        switch (type) {
            case 'days':
                line_chart(div, window.global.contacts.baptisms.added.sixty_days, numberOfElements)
                break;
            case 'months':
                line_chart(div, window.global.contacts.baptisms.added.twenty_four_months, numberOfElements, 'month')
                break;
        }
    } else if ('global-users-chart-div'===div) {
        switch (type) {
            case 'days':
                line_chart(div, window.global.users.login_activity.sixty_days, numberOfElements)
                break;
            case 'months':
                line_chart(div, window.global.users.login_activity.twenty_four_months, numberOfElements, 'month')
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

function load_global_line_chart(div, id, type, numberOfElements) {
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
    } else if ('global-baptisms-chart-div'===div) {
        switch (type) {
            case 'days':
                line_chart(div, window.global.contacts.baptisms.added.sixty_days, numberOfElements)
                break;
            case 'months':
                line_chart(div, window.global.contacts.baptisms.added.twenty_four_months, numberOfElements, 'month')
                break;
        }
    } else if ('global-users-chart-div'===div) {
        switch (type) {
            case 'days':
                line_chart(div, window.global.users.login_activity.sixty_days, numberOfElements)
                break;
            case 'months':
                line_chart(div, window.global.users.login_activity.twenty_four_months, numberOfElements, 'month')
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

function write_sites_list( load_reveal = false ) {
    if ( typeof window.sites_list === 'undefined' ){
        console.log( 'window.sites_list not found')
        return
    }
    // let spinner = '<span class="loading-spinner"></span>'
    let list = jQuery('#list-sites')
    list.empty().html(`
            <table id="site-table" class="display" style="cursor:pointer;" data-order='[[ 1, "asc" ]]' data-page-length='25'>
              <thead>
                <th>${network_base_script.trans.base_1 /*ID*/}</th>
                <th>${network_base_script.trans.base_2 /*Site*/}</th>
                <th>${network_base_script.trans.base_3/*Contacts*/}</th>
                <th>${network_base_script.trans.base_4 /*Groups*/}</th>
                <th>${network_base_script.trans.base_5 /*Users*/}</th>
                <th>${network_base_script.trans.base_6 /*Timestamp*/}</th>
                <th>${network_base_script.trans.base_7 /*Visit*/}</th>
              </thead>
              </table>
          `)


    let table = jQuery('#site-table').DataTable({
        data: window.sites_list,
        columns: [
            {data: 'id'},
            {data: 'name'},
            {data: 'contacts'},
            {data: 'groups'},
            {data: 'users'},
            {data: 'date'},
            {data: null, "defaultContent": "<button class='button small' style='margin:0' >"+network_base_script.trans.base_8+"</button>"}
        ],
        "columnDefs": [
            {
                "targets": [0],
                "visible": false
            }
        ]
    });

    if ( load_reveal ) {
        new Foundation.Reveal(jQuery('#site-modal'))
    }

    jQuery('#site-table tbody').on('click', "tr", function (event) {
        jQuery('#site-modal-content').empty().html('<span class="loading-spinner active"></span>')
        jQuery('#site-modal').foundation('open')
        let data = table.row(jQuery(this)).data();
        show_single_site(data['id'], data['name'])
        window.scrollTo(0, 0);
    })
}

/**
 * Single Snapshots Page
 */
function show_single_site(id, name) {

    "use strict";
    if (id===undefined) {
        show_network_home()
        return
    }

    if ( typeof  window.sites === 'undefined' ){
        return;
    }

    let data = window.sites[id]

    let chartDiv = jQuery('#site-modal-content')
    chartDiv.empty().html(`
              <span class="section-header">${name}</span>
              <hr style="max-width:100%;">
              
              <br>
              <span class="section-header">${network_base_script.trans.site_1 /*Active Snapshot*/}</span>
              <div class="grid-x grid-padding-x grid-padding-y">
                  <div class="cell">
                      <div class="grid-x callout">
                          <div class="medium-4 cell center">
                          <h4>${network_base_script.trans.contacts /*Contacts*/}<br><span class="total_contacts">${data.contacts.current_state.status.active}</span></h4>
                          </div>
                          <div class="medium-4 cell center" style="border-left: 1px solid #ccc">
                          <h4>${network_base_script.trans.groups /*Groups*/}<br><span class="total_groups">${data.groups.current_state.total_active}</span></h4>
                          </div>
                          <div class="medium-4 cell center" style="border-left: 1px solid #ccc">
                          <h4>${network_base_script.trans.users /*Users*/}<br><span id="total_users">${data.users.current_state.total_users}</span></h4>
                          </div>
                      </div>
                  </div>
              </div>
              
              <div class="grid-x grid-padding-x grid-padding-y">
                  <div class="cell">
                      ${network_base_script.trans.site_2 /*Generations*/}<br>
                      <button class="button generation-buttons" id="g-baptisms" onclick="load_gen_chart( 'generations-div', '${id}', 'g-baptisms' );set_buttons('generation-buttons', 'g-baptisms' )">${network_base_script.trans.baptisms /*Baptisms*/}</button> 
                      <button class="button hollow generation-buttons" id="g-groups" onclick="load_gen_chart( 'generations-div', '${id}', 'g-groups' );set_buttons('generation-buttons', 'g-groups' )">${network_base_script.trans.groups /*Groups*/}</button> 
                      <button class="button hollow generation-buttons" id="g-churches" onclick="load_gen_chart( 'generations-div', '${id}', 'g-churches' );set_buttons('generation-buttons', 'g-churches' )">${network_base_script.trans.churches /*Churches*/}</button> 
                      <div id="generations-div" style="height:300px;width:100%;"></div>
                  </div>
                  
              </div>
              
              <div class="grid-x grid-padding-y grid-margin-y">
                  <div class="cell">
                      <br><br>
                      <hr>
                  </div>
              </div>
              
              <!-- CONTACTS -->
              <span class="section-header">${network_base_script.trans.contacts /*Contacts*/}</span>
              <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
                  <div class="cell">
                      
                      <div class="grid-x callout">
                          <div class="medium-3 cell center">
                          <h4>${network_base_script.trans.active_contacts /*Active Contacts*/}<br><span class="total_contacts">${data.contacts.current_state.status.active}</span></h4>
                          </div>
                          <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                          <h4>${network_base_script.trans.paused_contacts /*Paused Contacts*/}<br><span class="total_groups">${data.contacts.current_state.status.paused}</span></h4>
                          </div>
                          <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                          <h4>${network_base_script.trans.closed_contacts /*Closed Contacts*/}<br><span id="total_users">${data.contacts.current_state.status.closed}</span></h4>
                          </div>
                          <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                          <h4>${network_base_script.trans.total_contacts /*Total Contacts*/}<br><span id="total_users">${data.contacts.current_state.all_contacts}</span></h4>
                          </div>
                      </div>
                  </div>
              </div>
              
              <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
                  <div class="cell">
                      ${network_base_script.trans.new_contacts /*New Contacts*/}<br>
                      <button class="button hollow new-contact-buttons" id="c-7-days" onclick="load_line_chart( 'line-chart-div', '${id}', 'days', 7 );set_buttons('new-contact-buttons', 'c-7-days' )">${network_base_script.trans.last_7_days /*Last 7 days*/}</button> 
                      <button class="button new-contact-buttons" id="c-30-days"  onclick="load_line_chart( 'line-chart-div', '${id}', 'days', 30 );set_buttons('new-contact-buttons', 'c-30-days' )">${network_base_script.trans.last_30_days /*Last 30 days*/}</button> 
                      <button class="button hollow new-contact-buttons" id="c-60-days"  onclick="load_line_chart( 'line-chart-div', '${id}', 'days', 60 );set_buttons('new-contact-buttons', 'c-60-days' )">${network_base_script.trans.last_60_days /*Last 60 days*/}</button> 
                      <button class="button hollow new-contact-buttons" id="c-12-months"  onclick="load_line_chart( 'line-chart-div', '${id}', 'months', 12 );set_buttons('new-contact-buttons', 'c-12-months' )">${network_base_script.trans.last_12_months /*Last 12 Months*/}</button>
                      <button class="button hollow new-contact-buttons" id="c-24-months"  onclick="load_line_chart( 'line-chart-div', '${id}', 'months', 24 );set_buttons('new-contact-buttons', 'c-24-months' )">${network_base_script.trans.last_24_months /*Last 24 Months*/}</button>
                      <div id="line-chart-div" style="height:500px;width:100%;"></div>
                  </div>
              </div>
              
              <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
                  <div class="cell medium-10">
                      ${network_base_script.trans.site_3 /*Follow-up Funnel*/}
                      <div id="follow-up-funnel-chart-div" style="height:400px;width:100%;"></div>
                  </div>
                  <div class="cell medium-2 center" style="border-left: 1px solid #ccc">
                      <div class="grid-x grid-margin-y">
                          <div class="cell" style="padding-top:1.2em;"><h4>${network_base_script.trans.site_4 /*On-Going Meetings*/}<br><span>${data.contacts.follow_up_funnel.ongoing_meetings}</span></h4></div>
                          <div class="cell" style="padding-top:1.2em;"><h4>${network_base_script.trans.site_5 /*Coaching*/}<br><span>${data.contacts.follow_up_funnel.coaching}</span></h4></div>
                          <div class="cell" style="padding-top:1.2em;"><h4>${network_base_script.trans.site_6 /*Baptized*/}<br><span>${data.contacts.baptisms.current_state.all_baptisms}</span></h4></div>
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
              <span class="section-header">${network_base_script.trans.groups /*Groups*/}</span>
              <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
                  <div class="cell">
                      <div class="grid-x callout">
                          <div class="medium-3 cell center">
                          <h4>${network_base_script.trans.site_7 /*Active Groups*/}<br><span class="total_contacts">${data.groups.current_state.all}</span></h4>
                          </div>
                          <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                          <h4>${network_base_script.trans.site_8 /*Pre-Group*/}<br><span class="total_groups">${data.groups.current_state.active.pre_group}</span></h4>
                          </div>
                          <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                          <h4>${network_base_script.trans.group /*Group*/}<br><span id="total_users">${data.groups.current_state.active.group}</span></h4>
                          </div>
                          <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                          <h4>${network_base_script.trans.site_8 /*Church*/}<br><span id="total_users">${data.groups.current_state.active.church}</span></h4>
                          </div>
                      </div>
                  </div>
              </div>
              
              <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
                  <div class="cell">
                      ${network_base_script.trans.new_groups /*New Groups */}<br>
                      <button class="button hollow new-group-buttons" id="g-7-days" onclick="load_line_chart( 'group-line-chart-div', '${id}', 'days', 7 );set_buttons('new-group-buttons', 'g-7-days' )">${network_base_script.trans.last_7_days /*Last 7 days*/}</button> 
                      <button class="button new-group-buttons" id="g-30-days"  onclick="load_line_chart( 'group-line-chart-div', '${id}', 'days', 30 );set_buttons('new-group-buttons', 'g-30-days' )">${network_base_script.trans.last_30_days /*Last 30 days*/}</button> 
                      <button class="button hollow new-group-buttons" id="g-60-days"  onclick="load_line_chart( 'group-line-chart-div', '${id}', 'days', 60 );set_buttons('new-group-buttons', 'g-60-days' )">${network_base_script.trans.last_60_days /*Last 60 days*/}</button> 
                      <button class="button hollow new-group-buttons" id="g-12-months"  onclick="load_line_chart( 'group-line-chart-div', '${id}', 'months', 12 );set_buttons('new-group-buttons', 'g-12-months' )">${network_base_script.trans.last_12_months /*Last 12 Months*/}</button>
                      <button class="button hollow new-group-buttons" id="g-24-months"  onclick="load_line_chart( 'group-line-chart-div', '${id}', 'months', 24 );set_buttons('new-group-buttons', 'g-24-months' )">${network_base_script.trans.last_24_months /*Last 24 Months*/}</button>
                      <div id="group-line-chart-div" style="height:500px;width:100%;"></div>
                  </div>
              </div>
              
              <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
                  
                  <div class="cell medium-6">
                      ${network_base_script.trans.site_8 /*Health Metrics*/}
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
              
              <span class="section-header">${network_base_script.trans.users /*Users*/}</span>
              <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
                  <div class="cell">
                      <div class="grid-x callout">
                          <div class="medium-3 cell center">
                          <h4>${network_base_script.trans.site_10 /*Total Users*/}<br><span class="total_contacts">${data.users.current_state.total_users}</span></h4>
                          </div>
                          <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                          <h4>${network_base_script.trans.site_11 /*Responders*/}<br><span id="total_users">${data.users.current_state.roles.responders}</span></h4>
                          </div>
                          <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                          <h4>${network_base_script.trans.site_12 /*Dispatchers*/}<br><span id="total_users">${data.users.current_state.roles.dispatchers}</span></h4>
                          </div>
                          <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                          <h4>${network_base_script.trans.site_13 /*Multipliers*/}<br><span class="total_groups">${data.users.current_state.roles.multipliers}</span></h4>
                          </div>
                          <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                          <h4>${network_base_script.trans.site_14 /*Admins*/}<br><span id="total_users">${data.users.current_state.roles.admins}</span></h4>
                          </div>
                      </div>
                  </div>
              </div>
              
              <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
                  <div class="cell">
                      ${network_base_script.trans.site_15 /*User Login Activity*/}<br>
                      <button class="button hollow active-user-buttons" id="ua-7-days" onclick="load_line_chart( 'user-activity-chart-div', '${id}', 'days', 7 );set_buttons('active-user-buttons', 'ua-7-days' )">${network_base_script.trans.last_7_days /*Last 7 days*/}</button> 
                      <button class="button active-user-buttons" id="ua-30-days" onclick="load_line_chart( 'user-activity-chart-div', '${id}', 'days', 30 );set_buttons('active-user-buttons', 'ua-30-days' )">${network_base_script.trans.last_30_days /*Last 30 days*/}</button> 
                      <button class="button hollow active-user-buttons" id="ua-60-days" onclick="load_line_chart( 'user-activity-chart-div', '${id}', 'days', 60 );set_buttons('active-user-buttons', 'ua-60-days' )">${network_base_script.trans.last_60_days /*Last 60 days*/}</button> 
                      <button class="button hollow active-user-buttons" id="ua-12-months" onclick="load_line_chart( 'user-activity-chart-div', '${id}', 'months', 12 );set_buttons('active-user-buttons', 'ua-12-months' )">${network_base_script.trans.last_12_months /*Last 12 Months*/}</button>
                      <button class="button hollow active-user-buttons" id="ua-24-months" onclick="load_line_chart( 'user-activity-chart-div', '${id}', 'months', 24 );set_buttons('active-user-buttons', 'ua-24-months' )">${network_base_script.trans.last_24_months /*Last 24 Months*/}</button>
                      <div id="user-activity-chart-div" style="height:500px;width:100%;"></div>
                  </div>
              </div>
              
              <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
                  <div class="cell medium-6">
                      ${network_base_script.trans.site_16 /*Users Active in the Last 30 Days*/}
                      <div id="system-engagement-pie-chart-div" style="height:400px;width:100%;"></div>
                  </div>
                  <div class="cell medium-6">
                      
                  </div>
              </div>
                      
          `);

    load_line_chart('line-chart-div', id, 'days', 30)
    set_buttons('new-contact-buttons', 'c-30-days')

    // load_points_map('site-map-div', id)
    load_gen_chart('generations-div', id, 'g-baptisms')

    load_line_chart('group-line-chart-div', id, 'days', 30)
    set_buttons('new-group-buttons', 'g-30-days')
    load_funnel_chart('follow-up-funnel-chart-div', id)

    load_bar_chart('health-bar-chart-div', id)
    load_funnel_chart('church-funnel-chart-div', id)

    load_pie_chart('system-engagement-pie-chart-div', id)

    load_line_chart('user-activity-chart-div', id, 'days', 30)
    set_buttons('active-user-buttons', 'ua-30-days')

    jQuery('.loading-spinner').removeClass('active')

    jQuery('#metrics-sidemenu').foundation('down', jQuery('#sites-list'));
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

function load_global_gen_chart(div, type) {
    if ('generations-div'===div) {
        switch (type) {
            case 'g-baptisms':
                bar_chart('generations-div', window.global.contacts.baptisms.generations)
                break;
            case 'g-coaching':
                bar_chart('generations-div', window.global.contacts.coaching.generations)
                break;
            case 'g-groups':
                bar_chart('generations-div', window.global.groups.group_generations)
                break;
            case 'g-churches':
                bar_chart('generations-div', window.global.groups.church_generations)
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

    createSeries('practicing', network_base_script.trans.site_17 ) /*Practicing*/
    createSeries('not_practicing', network_base_script.trans.site_18 ) /*Not Practicing*/


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
    } else if ('global-system-engagement-pie-chart-div'===div) {
        pie_chart('global-system-engagement-pie-chart-div', window.global.users.last_thirty_day_engagement)
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

function reset() {
    jQuery('.reset-spinner').html('<span class="loading-spinner active"></span>')
    makeRequest('POST', 'network/base', {'type': 'reset'} )
        .done(function(data) {
            location.reload()
        })
}
