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