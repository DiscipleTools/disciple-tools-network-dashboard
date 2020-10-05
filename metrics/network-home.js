jQuery(document).ready(function(){
    console.log('network-home.js file loaded')

    makeRequest('POST', 'network',{'id': 'test'} )
        .done(function(data) {
            console.log( data )
        })
})