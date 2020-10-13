jQuery(document).ready(function(){
    // let obj = network_maps_hovermap
    let chartDiv = jQuery('#chart')
    let spinner = '<span class="loading-spinner active"></span>'

    // add highlight to menu
    jQuery('#network_maps_hovermap').prop('style', 'font-weight:900;')

    // write page layout with spinners
    chartDiv.empty().html(`
        <span class="section-header">Maps Hover Map</span>
            <hr style="max-width:100%;">
            <div id="mapping_chart">${spinner}</div>
            
            <div><button class="button clear" onclick="reset()">reset data</button> <span class="reset-spinner"></span></div>
        `)

    makeRequest('POST', 'network/base', {'type': 'locations_list'} )
        .done(function(data) {
            MAPPINGDATA.data = data
            page_mapping_view()
        })
})