jQuery(document).ready(function(){
    let chartDiv = jQuery('#chart')
    let spinner = '<span class="loading-spinner active"></span>'

    // add highlight to menu
    jQuery('#network_activity_feed').prop('style', 'font-weight:900;')

    // write page layout with spinners
    chartDiv.empty().html(`
             <style>
                #activity-wrapper {
                    height: ${window.innerHeight - 200}px !important;
                    overflow: scroll;
                }
            </style>
            <span class="section-header">Feed<span style="float:right;"><button class="button clear" onclick="reset()">reset data</button> <span class="reset-spinner"></span></span></span>
                <hr style="max-width:100%;">
                <div class="grid-x grid-padding-x">
                    <div class="medium-3 cell">
                        <div class="grid-x">
                            <div class="cell">
                                Sites
                                <select>
                                    <option>Sites</option>
                                    <option>Sites</option>
                                    <option>Sites</option>
                                    <option>Sites</option>
                                    <option>Sites</option>
                                </select>
                            </div>
                            <div class="cell">
                                Sites
                                <select>
                                    <option>Sites</option>
                                    <option>Sites</option>
                                    <option>Sites</option>
                                    <option>Sites</option>
                                    <option>Sites</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="medium-9 cell" style="border-left: 1px solid lightgrey;">
                        <div id="activity-wrapper">
                            <div id="activity-list">${spinner}</div>
                            
                        </div>
                    </div>
                </div>
            `)

    // call for data
    makeRequest('POST', 'network/activity/feed' )
        .done( data => {
            "use strict";
            console.log(data)
            let container = jQuery('#activity-list');
            let index = 0

            jQuery.each( data, function(i,v){
                container.append(`<h2>${i} (${v.length} events)</h2>`)
                container.append(`<ul>`)
                jQuery.each(v, function(ii,vv){
                    container.append(`<li> ${vv} </li>`)
                    index++
                })
                container.append(`</ul>`)

                if ( index > 1000 ){
                    return false
                }
            })

            jQuery('.loading-spinner').removeClass('active')
        })
})