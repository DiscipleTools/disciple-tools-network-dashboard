jQuery(document).ready(function(){
    let obj = network_statistics_groups
    let chartDiv = jQuery('#chart')
    let spinner = '<span class="loading-spinner active"></span>'

    // add highlight to menu
    jQuery('#network_statistics_groups').prop('style', 'font-weight:900;')

    // write page layout with spinners
    chartDiv.empty().html(`
            <span class="section-header">Groups</span>
                <hr style="max-width:100%;">
                <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
                  <div class="cell">
                      <div class="grid-x callout">
                          <div class="medium-3 cell center">
                          <h4>Pre-Group<br><span id="active_pre_group">${spinner}</span></h4>
                          </div>
                          <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                          <h4>Group<br><span id="active_group">${spinner}</span></h4>
                          </div>
                          <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                          <h4>Church<br><span id="active_church">${spinner}</span></h4>
                          </div>
                          <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                          <h4>All Active/Inactive<br><span id="all_groups">${spinner}</span></h4>
                          </div>
                      </div>
                  </div>
              </div>
                
                <!-- charts row -->
                <div class="grid-x grid-padding-x">
                    
                    <!--column 1-->
                    <div class="medium-6 cell">
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
                    
                    <!--column 2-->
                    <div class="medium-6 cell">
                        <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
                          <div class="cell">
                            <span class="section-header">Generations</span><br>
                              <button class="button hollow generation-buttons" id="g-groups"  onclick="load_global_gen_chart( 'generations-div', 'g-groups' );set_buttons('generation-buttons', 'g-groups' )">Groups</button> 
                              <button class="button hollow generation-buttons" id="g-churches"  onclick="load_global_gen_chart( 'generations-div', 'g-churches' );set_buttons('generation-buttons', 'g-churches' )">Churches</button> 
                              <div id="generations-div" style="height:500px;width:100%;">${spinner}</div>
                          </div>
                        </div>
                      </div>
                   </div> <!-- end charts row -->
                   
                   <hr style="max-width:100%;">
                <div><button class="button clear" onclick="reset()">reset data</button> <span class="reset-spinner"></span></div>
            `)

    // call for data
    makeRequest('POST', 'network/base', {'type': 'global'} )
        .done(function(data) {
            window.sites = data.sites
            window.global = data.global

            jQuery('#active_pre_group').html(window.global.groups.status.pre_group)
            jQuery('#active_group').html(window.global.groups.status.group)
            jQuery('#active_church').html(window.global.groups.status.church)
            jQuery('#all_groups').html(window.global.groups.status.total)

            load_line_chart('global-groups-chart-div', null, 'days', 30)
            set_buttons('new-group-buttons', 'g-30-days')

            load_global_gen_chart('generations-div', 'g-groups')
            set_buttons('generation-buttons', 'g-groups')
        })
})