function load_list_by_country() {
    let button = jQuery('#import_button')
    let spinner = dtSMOptionAPI.spinner
    button.append(spinner)

    let country_code = jQuery('#selected_country').val()
    let data = { "country_code": country_code }
    jQuery.ajax({
        type: "POST",
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dtSMOptionAPI.root + 'dt/v1/network/load_by_country',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtSMOptionAPI.nonce );
        },
    })
        .done(function (data) {
            button.empty().append('Load')
            let result_div = jQuery('#results')
            result_div.empty()
            result_div.append('<span style="float:right;"><a href="javascript:void(0);" ' +
                'onclick="jQuery(\'.subdivision\').toggle();">collapse/expand all subdivisions</a>' +
                '</span><span id="toggle-all"></span><br clear="all" />')

            jQuery.each(data, function(i,v) {
                result_div.append( '<hr><dt><strong style="font-size:1.4em">' + v.name + '</strong> ' +
                    '<a id="admin1-link-'+v.geonameid+'" class="page-title-action" onclick="install_admin1_geoname(\''+v.geonameid+'\'); jQuery(this).off(\'click\');">Install</a> ' +
                    '<span id="install-'+v.geonameid+'"></span>  <span style="float:right;">' +
                    '<a href="javascript:void(0);" onclick="jQuery(\'.adm2-'+v.geonameid+'\').toggle()">collapse/expand</a>' +
                    '</span></dt>')

                jQuery.each(v.adm2, function(ii, vv) {
                    result_div.append('<dd id="dd-'+vv.geonameid+'" class="adm2-'+v.geonameid+' subdivision"><strong>' + vv.name + '</strong> ' +
                        '<button type="button" id="button-'+vv.geonameid+'" class="page-title-action" onclick="install_admin2_geoname(\''+vv.geonameid+'\');" >Install</button> ' +
                        '<span id="install-'+vv.geonameid+'"></span> ' +
                        '<a class="show-city-link" id="cities-button-'+vv.geonameid+'" ' +
                        'onclick="load_cities(\''+vv.geonameid+'\')">Show Cities/Places</a> ' +
                        '<span id="cities-'+vv.geonameid+'"></span></dd>')
                })

            })

            console.log( 'success ')
            console.log( data )
        })
        .fail(function (err) {
            console.log("error");
            console.log(err);
        })
}
function install_admin2_geoname( geonameid ) {
    console.log('install_geoname')

    jQuery('#button-'+ geonameid ).prop("disabled",true)

    let spinner = dtSMOptionAPI.spinner

    let report_span = jQuery( '#install-' + geonameid )
    report_span.append(spinner)

    let data = { "geonameid": geonameid }
    jQuery.ajax({
        type: "POST",
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dtSMOptionAPI.root+'dt/v1/network/install_admin2_geoname',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtSMOptionAPI.nonce);
        },
    })
        .done(function (data) {
            report_span.empty().append('&#9989;')
            load_current_locations()

            console.log( 'success for ' + geonameid)
            console.log( data )
        })
        .fail(function (err) {
            report_span.empty().append('( oops. something failed. )')
            console.log("error for " + geonameid );
            console.log(err);
        })
}
function install_admin1_geoname( geonameid ) {
    console.log('install_geoname')

    jQuery('#button-'+ geonameid ).prop("disabled",true)

    let spinner = dtSMOptionAPI.spinner

    let report_span = jQuery( '#install-' + geonameid )
    report_span.append(spinner)

    let data = { "geonameid": geonameid }
    jQuery.ajax({
        type: "POST",
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dtSMOptionAPI.root + 'dt/v1/network/install_admin1_geoname',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtSMOptionAPI.nonce);
        },
    })
        .done(function (data) {
            report_span.empty().append('&#9989;')
            load_current_locations()

            console.log( 'success for ' + geonameid)
            console.log( data )
        })
        .fail(function (err) {
            report_span.empty().append('( oops. something failed. )')
            console.log("error for " + geonameid );
            console.log(err);
        })
}

function install_admin1_geoname_metabox( geonameid ) {
    console.log('install_geoname')

    jQuery('#button-'+ geonameid ).prop("disabled",true)

    let spinner = dtSMOptionAPI.spinner

    let report_span = jQuery( '#install-' + geonameid )
    report_span.append(spinner)

    let data = { "geonameid": geonameid }
    jQuery.ajax({
        type: "POST",
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dtSMOptionAPI.root + 'dt/v1/network/install_admin1_geoname',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtSMOptionAPI.nonce);
        },
    })
        .done(function (data) {
            report_span.empty().append('&#9989;')
            load_current_locations()

            console.log( 'success for ' + geonameid)
            console.log( data )
        })
        .fail(function (err) {
            report_span.empty().append('( oops. something failed. )')
            console.log("error for " + geonameid );
            console.log(err);
        })
}

function load_cities( geonameid ) {
    console.log('install_all_cities')

    jQuery('#cities-button-'+ geonameid ).prop("disabled",true)

    let spinner = dtSMOptionAPI.spinner

    let report_span = jQuery( '#cities-' + geonameid )
    report_span.append(spinner)
    let city_result_div = jQuery('#dd-'+geonameid)

    let data = { "geonameid": geonameid }
    jQuery.ajax({
        type: "POST",
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dtSMOptionAPI.root+'dt/v1/network/load_cities',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtSMOptionAPI.nonce );
        },
    })
        .done(function (data) {
            city_result_div.append('<br clear="all" /><hr>')

            jQuery.each(data.cities, function(i, v) {
                city_result_div.append( '<dd><strong>' + v.name + '</strong> <a class="show-city-link" id="city-button-'+v.geonameid+'" ' +
                    'class="page-title-action" onclick="install_single_city('+v.geonameid+','+data.admin2+');" >add</a>'+
                    ' <span id="city-install-'+v.geonameid+'"></span> <dd>')
            })

            report_span.empty()
            load_current_locations()

            console.log( 'success for ' + geonameid)
            console.log( data )
        })
        .fail(function (err) {
            report_span.empty().append('( oops. something failed. )')
            console.log("error for " + geonameid );
            console.log(err);
        })
}
function install_single_city( geonameid, admin2 ) {
    console.log('install_geoname')

    jQuery('#city-button-'+ geonameid ).prop("onclick",'')

    let spinner = dtSMOptionAPI.spinner

    let report_span = jQuery( '#city-install-' + geonameid )
    report_span.append(spinner)

    let data = { "geonameid": geonameid, "admin2": admin2 }
    jQuery.ajax({
        type: "POST",
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dtSMOptionAPI.root + 'dt/v1/network/install_single_city',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtSMOptionAPI.nonce);
        },
    })
        .done(function (data) {
            report_span.empty().append('&#9989;')
            load_current_locations()

            console.log( 'success for ' + geonameid)
            console.log( data )
        })
        .fail(function (err) {
            report_span.empty().append('( oops. something failed. )')
            console.log("error for " + geonameid );
            console.log(err);
        })
}
function load_current_locations() {
    let current_locations = jQuery('#current-locations')
    return jQuery.ajax({
        type: "POST",
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dtSMOptionAPI.root + 'dt/v1/network/load_current_locations',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtSMOptionAPI.nonce);
        },
    })
        .done(function (data) {
            current_locations.empty().append(data)
            console.log("success")
        })
        .fail(function (err) {
            console.log("error");
            console.log(err);
        })
}
function load_p_countries_installed() {

    jQuery.ajax({
        type: "POST",
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dtSMOptionAPI.root+'dt/v1/network/load_p_countries_installed',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtSMOptionAPI.nonce );
        },
    })
        .done(function (data) {
            jQuery('#current-locations').empty().append(data)
        })
        .fail(function (err) {
            console.log(err);
        })
}

function load_p_list_by_country( country_code ) {

    jQuery('#link-'+ country_code ).attr("onclick","")

    let spinner = dtSMOptionAPI.spinner
    let spinner_span = jQuery( '#spinner-'+country_code )
    spinner_span.empty().append(spinner)

    let data = { "country_code": country_code }
    jQuery.ajax({
        type: "POST",
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dtSMOptionAPI.root+'dt/v1/network/load_p_list_by_country',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtSMOptionAPI.nonce );
        },
    })
        .done(function (data) {
            spinner_span.empty().append('&#9989;')
            load_p_countries_installed()
            console.log( data )
        })
        .fail(function (err) {
            spinner_span.empty().append( "error for " + country_code  )
            console.log("error for " + country_code );
            console.log(err);
        })
}

function install_geonames( type ) {
    let link = jQuery('#'+type )
    let spinner = dtSMOptionAPI.spinner

    link.attr("onclick","")
    link.append(spinner)

    let data = {"type": type }
    jQuery.ajax({
        type: "POST",
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dtSMOptionAPI.root+'dt/v1/network/import',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtSMOptionAPI.nonce );
        },
    })
        .done(function (data) {
            link.empty().append('All finished! &#9989;')
            console.log( data )
        })
        .fail(function (err) {
            link.empty().append( "Oops. Something did not work. Maybe try again."  )
            console.log(err);
        })
}

function test_download( country_code ) {
    let link = jQuery( '#test_download' )
    let spinner = dtSMOptionAPI.spinner

    link.attr("onclick","")
    link.append(spinner)

    let data = {"country_code": country_code }
    jQuery.ajax({
        type: "POST",
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dtSMOptionAPI.root+'dt/v1/network/download',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtSMOptionAPI.nonce );
        },
    })
        .done(function (data) {
            link.empty().append('All finished! &#9989;')
            console.log( data )
        })
        .fail(function (err) {
            link.empty().append( "Oops. Something did not work. Maybe try again."  )
            console.log(err);
        })
}