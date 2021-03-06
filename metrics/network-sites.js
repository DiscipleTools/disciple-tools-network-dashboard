jQuery(document).ready(function(){
    let chartDiv = jQuery('#chart')
    let spinner = '<span class="loading-spinner"></span>'

    // add highlight to menu
    jQuery('#network_sites').prop('style', 'font-weight:900;')

    // write page layout with spinners
    chartDiv.empty().html(`
        <span class="section-header">${window.lodash.escape( network_base_script.trans.sites ) /*Sites*/}</span>

        <hr style="max-width:100%;">

        <div class="grid-x grid-padding-x">
            <div class="cell">
                <div id="list-sites">${spinner}</div>
            </div>
        </div>
        <div class="large reveal" id="site-modal" data-v-offset="0px" data-reveal>
            <div id="site-modal-content" >${spinner}</div>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <h2><span aria-hidden="true">&times;</span></h2>
             </button>
        </div>

         <hr style="max-width:100%;">
        <div><button class="button clear" onclick="reset()">${window.lodash.escape( network_base_script.trans.reset_data ) /*reset data*/}</button> <span class="reset-spinner"></span></div>
       `)

    // call for data
    makeRequest('POST', 'network/base', {'type': 'sites_list'} )
        .done(function(data) {
            window.sites_list = data

            new Foundation.Reveal(jQuery('#site-modal'))

            write_sites_list()
        })
    makeRequest('POST', 'network/base', {'type': 'sites'} )
        .done(function(data) {
            window.sites = data
        })
    makeRequest('POST', 'network/base', {'type': 'locations_list'} )
        .done(function(data) {
            window.locations_list = data
        })
})
