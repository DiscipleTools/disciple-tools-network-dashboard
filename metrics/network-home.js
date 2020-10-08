jQuery(document).ready(function(){
    let obj = network_home
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
                        <h4>Contacts<br><span class="total_contacts"><a href="/network/">${spinner}</a></span></h4>
                      </div>
                      <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                        <h4>Groups<br><span class="total_groups"><a href="/network/">${spinner}</a></span></h4>
                      </div>
                      <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                        <h4>Users<br><span id="total_users"><a href="/network/">${spinner}</a></span></h4>
                      </div>
                      <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                        <h4>Sites<br><span class="total_sites"><a href="/network/sites/">${spinner}</a></span></h4>
                      </div>
                      <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                        <h4>Countries<br><span id="total_countries"><a href="/network/">${spinner}</a></span></h4>
                      </div>
                      <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                        <h4>Activity<br><span id="total_prayer_events"><a href="/network/">${spinner}</a></span></h4>
                      </div>
                      
                    </div>
                  </div>
                </div>
                
                <div class="grid-x grid-padding-x">
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
                    <div class="medium-6 cell">
                        <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
                          <div class="cell">
                            <span class="section-header">Sites</span><br>
                            <table class="hover">
                                <thead><tr><td>Name</td></tr></thead>
                                <tbody>
                                    <tr><td>Site Name</td></tr>
                                    <tr><td>Site Name</td></tr>
                                    <tr><td>Site Name</td></tr>
                                    <tr><td>Site Name</td></tr>
                                    <tr><td>Site Name</td></tr>
                                    <tr><td>Site Name</td></tr>
                                    <tr><td>Site Name</td></tr>
                                    <tr><td>Site Name</td></tr>
                                    <tr><td>Site Name</td></tr>
                                    <tr><td>Site Name</td></tr>
                                    <tr><td>Site Name</td></tr>
                                    <tr><td>Site Name</td></tr>
                                    <tr><td>Site Name</td></tr>
                                    <tr><td>Site Name</td></tr>
                                    <tr><td>Site Name</td></tr>
                                </tbody>
                            </table>
                          </div>
                        </div>
                    
                    
                    </div>
                </div>
                
               
                
                
            `)

    // call for data
    makeRequest('POST', obj.endpoint,{'id': 'test'} )
        .done(function(data) {
            "use strict";
            console.log(obj)
        })
})