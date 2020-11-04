jQuery(document).ready(function(){
    let chartDiv = jQuery('#chart')
    let spinner = '<span class="loading-spinner active"></span>'

    // add highlight to menu
    jQuery('#network_activity_chart').prop('style', 'font-weight:900;')

    // write page layout with spinners
    chartDiv.empty().html(`
            
            <span class="section-header">Stats</span>
            <hr style="max-width:100%;">
            <div class="grid-x grid-padding-x">
                <div class="medium-3 cell">
                    <div id="activity-filter-wrapper"></div>
                </div>
                <div class="medium-9 cell" style="border-left: 1px solid lightgrey;">
                    
                    <!-- Stats Body -->
                    <div class="grid-x callout">
                      <div class="medium-3 cell center">
                        <h4>Activities<br><span id="activities_count">${spinner}</span></h4>
                      </div>
                      <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                        <h4>Actions<br><span id="actions_count">${spinner}</span></h4>
                      </div>
                      <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                        <h4>Sites<br><span id="sites_count">${spinner}</span></h4>
                      </div>
                      
                    </div>
                    <hr>
                    
                    <span class="section-header">By Action Type</span>
                    <div class="grid-x grid-padding-x">
                        <div class="cell">
                            <div id="action-type-table-wrapper" style="padding:0 1em;">${spinner}</div>
                        </div>
                    </div>
                   
                </div>
            </div>
            `)

    window.load_activity_filter()

    jQuery(document).ajaxComplete((event, xhr, settings) => {
        if ( typeof xhr.responseJSON.records_count !== 'undefined' ) {

            jQuery('#activities_count').html(window.activity_stats.records_count)
            jQuery('#actions_count').html( countProperties(window.activity_stats.actions) )
            jQuery('#sites_count').html( countProperties(window.activity_stats.sites) )

            write_action_table()
        }
    }).ajaxError((event, xhr) => {
        handleAjaxError(xhr)
    })

    function write_action_table(){

        let list = jQuery('#action-type-table-wrapper')
        list.empty().html(`
            <table id="action-type-table" class="display" style="cursor:pointer;" data-order='[[ 1, "asc" ]]' data-page-length='20'>
              <thead>
                <th>Key</th>
                <th>Name</th>
                <th>Total Activities</th>
              </thead>
              </table>
          `)

        window.list = []
        jQuery.each(window.activity_stats.actions, function(i,v){
            let total = 0
            if ( typeof window.activity_stats.actions_totals[v.key] !== 'undefined' ){
                total = window.activity_stats.actions_totals[v.key]
            }
            window.list.push( {'key': v.key, 'label': v.label, 'total': total })
        })

        let table = jQuery('#action-type-table').DataTable({
            data: window.list,
            columns: [
                {data: 'key'},
                {data: 'label'},
                {data: 'total'}
            ],
            "columnDefs": [
                {
                    "targets": [0],
                    "visible": false
                }
            ]
        });
    }

    function countProperties (obj) {
        var count = 0;

        for (var property in obj) {
            if (Object.prototype.hasOwnProperty.call(obj, property)) {
                count++;
            }
        }

        return count;
    }


})