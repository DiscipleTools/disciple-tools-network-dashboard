jQuery(document).ready(function(){
    let chartDiv = jQuery('#chart')
    let spinner = '<span class="loading-spinner active"></span>'

    // add highlight to menu
    jQuery('#network_statistics_users').prop('style', 'font-weight:900;')

    // write page layout with spinners
    chartDiv.empty().html(`
            <span class="section-header">${_.escape( network_base_script.trans.users ) /*Users*/}</span>
                <hr style="max-width:100%;">
                
                  <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
                      <div class="cell">
                          <div class="grid-x callout">
                              <div class="medium-3 cell center">
                              <h4>${_.escape( network_base_script.trans.site_10 ) /*Total Users*/}<br><span id="total_users">${spinner}</span></h4>
                              </div>
                              <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                              <h4>${_.escape( network_base_script.trans.site_11 ) /*Responders*/}<br><span id="total_responders">${spinner}</span></h4>
                              </div>
                              <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                              <h4>${_.escape( network_base_script.trans.site_12 ) /*Dispatchers*/}<br><span id="total_dispatchers">${spinner}</span></h4>
                              </div>
                              <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                              <h4>${_.escape( network_base_script.trans.site_13 ) /*Multipliers*/}<br><span id="total_multipliers">${spinner}</span></h4>
                              </div>
                              <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                              <h4>${_.escape( network_base_script.trans.site_14 ) /*Admins*/}<br><span id="total_admins">${spinner}</span></h4>
                              </div>
                          </div>
                      </div>
                  </div>
                <br>
                <div class="grid-x grid-padding-x">
                    <div class="medium-6 cell"><!--column 1-->
                        <span class="section-header">${_.escape( network_base_script.trans.site_15 ) /*User Login Activity*/}</span><br>
                        <button class="button hollow active-user-buttons" id="ua-7-days" onclick="load_line_chart( 'global-users-chart-div', null, 'days', 7 );set_buttons('active-user-buttons', 'ua-7-days' )">${_.escape( network_base_script.trans.last_7_days ) /*Last 7 days*/}</button> 
                        <button class="button active-user-buttons" id="ua-30-days" onclick="load_line_chart( 'global-users-chart-div', null, 'days', 30 );set_buttons('active-user-buttons', 'ua-30-days' )">${_.escape( network_base_script.trans.last_30_days ) /*Last 30 days*/}</button> 
                        <button class="button hollow active-user-buttons" id="ua-60-days" onclick="load_line_chart( 'global-users-chart-div', null, 'days', 60 );set_buttons('active-user-buttons', 'ua-60-days' )">${_.escape( network_base_script.trans.last_60_days ) /*Last 60 days*/}</button> 
                        <button class="button hollow active-user-buttons" id="ua-12-months" onclick="load_line_chart( 'global-users-chart-div', null, 'months', 12 );set_buttons('active-user-buttons', 'ua-12-months' )">${_.escape( network_base_script.trans.last_12_months ) /*Last 12 Months*/}</button>
                        <button class="button hollow active-user-buttons" id="ua-24-months" onclick="load_line_chart( 'global-users-chart-div', null, 'months', 24 );set_buttons('active-user-buttons', 'ua-24-months' )">${_.escape( network_base_script.trans.last_24_months ) /*Last 24 Months*/}</button>
                        <div id="global-users-chart-div" style="height:500px;width:100%;">${spinner}</div>
                    </div>
                    <div class="medium-6 cell"><!--column 2-->
                        <span class="section-header">${_.escape( network_base_script.trans.site_16 ) /*Users Active in the Last 30 Days*/}</span><br>
                        <div id="global-system-engagement-pie-chart-div" style="height:500px;width:100%;">${spinner}</div>
                    </div>
                </div>
                
                <hr style="max-width:100%;">
                <div><button class="button clear" onclick="reset()">${_.escape( network_base_script.trans.reset_data ) /*reset data*/}</button> <span class="reset-spinner"></span></div>
            `)

    // call for data
    makeRequest('POST', 'network/base', {'type': 'global'} )
        .done(function(data) {
            window.sites = data.sites
            window.global = data.global

            load_line_chart('global-users-chart-div', null, 'days', 30)
            set_buttons('active-user-buttons', 'ua-30-days')

            load_pie_chart('global-system-engagement-pie-chart-div', null)

            jQuery('#total_users').html(window.global.users.total )
            jQuery.each( window.global.users.current_state, function(i,v) {
                if ( 'responders' === i ){
                    jQuery('#total_responders').html(v)
                }
                if ( 'dispatchers' === i ){
                    jQuery('#total_dispatchers').html(v)
                }
                if ( 'multipliers' === i ){
                    jQuery('#total_multipliers').html(v)
                }
                if ( 'admins' === i ){
                    jQuery('#total_admins').html(v)
                }
            })

        })
})