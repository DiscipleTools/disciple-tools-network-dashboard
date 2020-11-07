jQuery(document).ready(function(){
    let chartDiv = jQuery('#chart')
    let spinner = '<span class="loading-spinner active"></span>'

    // add highlight to menu
    jQuery('#network_activity_stream').prop('style', 'font-weight:900;')

    // write page layout with spinners
    chartDiv.empty().html(`
            <style>
                #activity-list-wrapper {
                    height: ${window.innerHeight - 175}px !important;
                    overflow: scroll;
                }
            </style>
            <span class="section-header">${_.escape( network_base_script.trans.activity_10 ) /*Feed*/}</span>
                <hr style="max-width:100%;">
                <div class="grid-x grid-padding-x">
                    <div class="medium-3 cell">
                        <div id="activity-filter-wrapper"></div>
                    </div>
                    <div class="medium-9 cell" style="border-left: 1px solid lightgrey;">
                        <div id="activity-list-wrapper"></div>
                    </div>
                </div>
            `)

    window.load_activity_filter()

})