jQuery(document).ready(function(){
    // let obj = network_home
    let chartDiv = jQuery('#chart')
    let spinner = '<span class="loading-spinner active"></span>'

    // add highlight to menu
    jQuery('#network_home').prop('style', 'font-weight:900;')

    // write page layout with spinners
    chartDiv.empty().html(`
        <span class="section-header">Home</span>
            <hr style="max-width:100%;">
            <div id="map_chart" style="width: 100%; max-width:1200px; margin:0 auto; max-height: 700px;height: 100vh;vertical-align: text-top;">${spinner}</div>
            
            <hr style="max-width:100%;">
            
            <div class="grid-x grid-padding-x grid-padding-y">
              <div class="cell">
              <span class="section-header">Active Totals for All Sites </span><br>
                <div class="grid-x callout">
                <div class="medium-2 cell center">
                    <h4>Contacts<br><a href="/network/statistics/contacts"><span class="total_contacts">${spinner}</span></a></h4>
                  </div>
                  <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                    <h4>Groups<br><a href="/network/statistics/groups"><span class="total_groups">${spinner}</span></a></h4>
                  </div>
                  <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                    <h4>Users<br><a href="/network/statistics/users"><span id="total_users">${spinner}</span></a></h4>
                  </div>
                  <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                    <h4>Sites<br><a href="/network/sites/"><span class="total_sites">${spinner}</span></a></h4>
                  </div>
                  <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                    <h4>Countries<br><a href="/network/maps/locationlist"><span id="total_countries">${spinner}</span></a></h4>
                  </div>
                  <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                    <h4>Events (30 Days)<br><a href="/network/activity/stream"><span class="total_activity">${spinner}</span></a></h4>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="grid-x grid-padding-x grid-margin-x">
                <div class="medium-6 cell">
                
                    <!-- new contacts -->
                     <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
                      <div class="cell">
                        <span class="section-header">New Contacts </span><br>
                        <button class="button hollow new-contact-buttons" id="c-7-days" onclick="load_line_chart( 'global-contacts-chart-div', null, 'days', 7 );set_buttons('new-contact-buttons', 'c-7-days' )">Last 7 days</button> 
                        <button class="button new-contact-buttons" id="c-30-days"  onclick="load_line_chart( 'global-contacts-chart-div', null, 'days', 30 );set_buttons('new-contact-buttons', 'c-30-days' )">Last 30 days</button> 
                        <button class="button hollow new-contact-buttons" id="c-60-days"  onclick="load_line_chart( 'global-contacts-chart-div', null, 'days', 60 );set_buttons('new-contact-buttons', 'c-60-days' )">Last 60 days</button> 
                        <button class="button hollow new-contact-buttons" id="c-12-months"  onclick="load_line_chart( 'global-contacts-chart-div', null, 'months', 12 );set_buttons('new-contact-buttons', 'c-12-months' )">Last 12 Months</button>
                        <button class="button hollow new-contact-buttons" id="c-24-months"  onclick="load_line_chart( 'global-contacts-chart-div', null, 'months', 24 );set_buttons('new-contact-buttons', 'c-24-months' )">Last 24 Months</button>
                        <div id="global-contacts-chart-div" style="height:500px;width:100%;">${spinner}</div>
                      </div>
                    </div>
                
                    <!-- new groups -->
                    <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
                      <div class="cell">
                        <span class="section-header">New Groups </span><br>
                        <button class="button hollow new-group-buttons" id="g-7-days" onclick="load_line_chart( 'global-groups-chart-div', null, 'days', 7 );set_buttons('new-group-buttons', 'g-7-days' )">Last 7 days</button> 
                        <button class="button new-group-buttons" id="g-30-days"  onclick="load_line_chart( 'global-groups-chart-div', null, 'days', 30 );set_buttons('new-group-buttons', 'g-30-days' )">Last 30 days</button> 
                        <button class="button hollow new-group-buttons" id="g-60-days"  onclick="load_line_chart( 'global-groups-chart-div', null, 'days', 60 );set_buttons('new-group-buttons', 'g-60-days' )">Last 60 days</button> 
                        <button class="button hollow new-group-buttons" id="g-12-months"  onclick="load_line_chart( 'global-groups-chart-div', null, 'months', 12 );set_buttons('new-group-buttons', 'g-12-months' )">Last 12 Months</button>
                        <button class="button hollow new-group-buttons" id="g-24-months"  onclick="load_line_chart( 'global-groups-chart-div', null, 'months', 24 );set_buttons('new-group-buttons', 'g-24-months' )">Last 24 Months</button>
                        <div id="global-groups-chart-div" style="height:500px;width:100%;">${spinner}</div>
                      </div>
                    </div>
                
                </div>
                <div class="medium-6 cell" >
                    <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
                      <div class="cell">
                        <span class="section-header">Sites</span><br>
                        <div class="grid-x grid-padding-x">
                            <div class="cell">
                                <div id="list-sites">${spinner}</div>
                            </div>
                            <div class="large reveal" id="site-modal" data-v-offset="0px" data-reveal>
                                <div id="site-modal-content">${spinner}</div>
                                <button class="close-button" data-close aria-label="Close modal" type="button">
                                    <h2><span aria-hidden="true">&times;</span></h2>
                                 </button>
                            </div>
                        </div>
                      </div>
                    </div>
                </div>
            </div>
            
            <hr style="max-width:100%;">
            <div><button class="button clear" onclick="reset()">reset data</button> <span class="reset-spinner"></span></div>
        `)

    // call for data
    makeRequest('POST', 'network/base', {'type': 'sites_list'} )
        .done(function(data) {
            window.sites_list = data
            write_sites_list( true )
        })
    makeRequest('POST', 'network/base', {'type': 'global'} )
        .done(function(data) {
            window.sites = data.sites
            window.global = data.global

            jQuery('#total_countries').html(window.global.locations.total_countries)
            jQuery('#total_users').html(window.global.users.total)
            jQuery('.total_groups').html(window.global.groups.total)
            jQuery('.total_contacts').html(window.global.contacts.status.active)
            jQuery('.total_sites').html(window.global.sites.total)
            jQuery('.total_activity').html(window.global.activity.total)

            load_line_chart('global-contacts-chart-div', null, 'days', 30)
            set_buttons('new-contact-buttons', 'c-30-days')

            load_line_chart('global-groups-chart-div', null, 'days', 30)
            set_buttons('new-group-buttons', 'g-30-days')
        })

    makeRequest('POST', 'network/base', {'type': 'locations_list'} )
        .done(function(data) {
            window.locations_list = data

            MAPPINGDATA.data = data

            DRILLDOWN.get_drill_down('map_chart_drilldown', MAPPINGDATA.settings.current_map)
        })
})