jQuery(document).ready(function(){
    let obj = network_sites
    let chartDiv = jQuery('#chart')
    let spinner = '<span class="loading-spinner active"></span>'

    // add highlight to menu
    jQuery('#network_sites').prop('style', 'font-weight:900;')

    // write page layout with spinners
    chartDiv.empty().html(`
            <span class="section-header">Sites</span>
        
        <hr style="max-width:100%;">
        
        <div class="grid-x grid-padding-x">
            <div class="cell">
                <div id="list-sites">${spinner}</div>
            </div>
        </div>
        
        
       `)

    // call for data
    load_sites_list_data()
    load_sites_data()
    function load_sites_list_data(){
        makeRequest('POST', obj.endpoint, {'type': 'sites_list'} )
            .done(function(data) {
                window.sites_list = data
                write_sites_list(data)
            })
    }
    function load_sites_data(){
        makeRequest('POST', obj.endpoint, {'type': 'sites'} )
            .done(function(data) {
                window.sites = data
            })
    }

    // load sites
    function write_sites_list(data) {
        if ( typeof data === 'undefined' && typeof window.sites === 'undefined' ){
            data = window.sites_list
        }
        let list = jQuery('#list-sites')
        list.empty().html(`
            <table id="site-table" class="display" data-order='[[ 1, "asc" ]]' data-page-length='25'>
              <thead>
                <th>ID</th>
                <th>Site</th>
                <th>Contacts</th>
                <th>Groups</th>
                <th>Users</th>
                <th>Timestamp</th>
                <th>Visit</th>
              </thead>
              </table>
          `)


        let table = jQuery('#site-table').DataTable({
            data: data,
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
            jQuery(this).parent().append(spinner)
            let data = table.row(jQuery(this).parents('tr')).data();
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

})