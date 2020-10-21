jQuery(document).ready(function(){
    let chartDiv = jQuery('#chart')
    let spinner = '<span class="loading-spinner active"></span>'

    // add highlight to menu
    jQuery('#network_activity_chart').prop('style', 'font-weight:900;')

    // write page layout with spinners
    chartDiv.empty().html(`
                <span class="section-header">Activity Chart</span>
                <hr style="max-width:100%;">
                
                <div class="grid-x grid-padding-x grid-padding-y">
                  <div class="cell">
                    <div class="grid-x callout">
                    <div class="medium-2 cell center">
                        <h4>Events<br><span class="my_total_contacts">${spinner}</span></h4>
                      </div>
                      <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                        <h4>Sites<br><span class="our_total_contacts">${spinner}</span></h4>
                      </div>
                      <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                        <h4>Countries<br><span id="my_total_baptisms">${spinner}</span></h4>
                      </div>
                      <div class="medium-2 cell center" style="border-left: 1px solid #ccc">
                        <h4>No Locations<br><span class="our_total_contacts">${spinner}</span></h4>
                      </div>
                    </div>
                  </div>
                </div>
                <br>
                <div class="grid-x grid-padding-x">
                    <div class="medium-6 cell"><!--column 1-->
                        <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
                          <div class="cell">
                            <span class="section-header">Activity </span><br>
                            <button class="button hollow new-contact-buttons" id="c-7-days" onclick="load_line_chart( 'global-contacts-chart-div', null, 'days', 7 );set_buttons('new-contact-buttons', 'c-7-days' )">Last 7 days</button> 
                            <button class="button new-contact-buttons" id="c-30-days"  onclick="load_line_chart( 'global-contacts-chart-div', null, 'days', 30 );set_buttons('new-contact-buttons', 'c-30-days' )">Last 30 days</button> 
                            <button class="button hollow new-contact-buttons" id="c-60-days"  onclick="load_line_chart( 'global-contacts-chart-div', null, 'days', 60 );set_buttons('new-contact-buttons', 'c-60-days' )">Last 60 days</button> 
                            <button class="button hollow new-contact-buttons" id="c-12-months"  onclick="load_line_chart( 'global-contacts-chart-div', null, 'months', 12 );set_buttons('new-contact-buttons', 'c-12-months' )">Last 12 Months</button>
                            <button class="button hollow new-contact-buttons" id="c-24-months"  onclick="load_line_chart( 'global-contacts-chart-div', null, 'months', 24 );set_buttons('new-contact-buttons', 'c-24-months' )">Last 24 Months</button>
                            <div id="global-contacts-chart-div" style="height:500px;width:100%;">${spinner}</div>
                          </div>
                        </div>
                    </div>
                <div class="medium-6 cell"><!--column 2-->
                    <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
                  <div class="cell">
                    <span class="section-header">Sites Submitting Activity</span><br>
                    <div id="list-sites">${spinner}</div>
                  </div>
                </div>
                </div>
                </div>
                
                <div id="action-list" style="height:300px;width:100%;"></div>
                
                <br>
                
                <div><button class="button clear" onclick="reset()">reset data</button> <span class="reset-spinner"></span></div>
            `)

    // call for data
    makeRequest('GET', 'network/base', {'type': 'global'} )
        .done(function(data) {
            window.sites = data.sites
            window.global = data.global

            let actions = [
                {
                    'label': 'Test',
                    'value': 30
                },
                {
                    'label': 'Test2',
                    'value': 60
                }
            ]
            bar_chart('action-list', actions)


            load_line_chart('global-contacts-chart-div', null, 'days', 30)
            set_buttons('new-contact-buttons', 'c-30-days')

            load_line_chart('global-baptisms-chart-div', null, 'days', 30)
            set_buttons('new-baptism-buttons', 'b-30-days')

            load_global_gen_chart('generations-div', 'g-baptisms')
        })

    makeRequest('POST', 'network/base', {'type': 'sites_list'} )
        .done(function(data) {
            window.sites_list = data

            new Foundation.Reveal(jQuery('#site-modal'))

            write_sites_list()
        })
})