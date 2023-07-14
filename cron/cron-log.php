<?php


function dt_save_log( $name, $content = "", $timestamp = true ) {
    if ( empty( $name ) ) {
        return false;
    }

    $file = dt_get_log_location( $name );
    if ( $timestamp ) {
        $content = current_time( 'mysql' ) . ': ' . $content;
    }
    dt_write_log( $content );
    return file_put_contents( $file, $content.PHP_EOL, FILE_APPEND | LOCK_EX );
}

function dt_reset_log( $name ) {
    if ( empty( $name ) ) {
        return false;
    }

    $file = dt_get_log_location( $name );

    $fh = fopen( $file, 'w' );
    ftruncate( $fh, 0 );
    fclose( $fh );
    return true;
}

function dt_get_log_location( $name, $type = null ) {
    if ( ! file_exists( WP_CONTENT_DIR . '/uploads/network-dashboard/' ) ) {
        mkdir( WP_CONTENT_DIR . '/uploads/network-dashboard/', 0777, true );
    }
    if ( 'url' === $type ) {
        return set_url_scheme( WP_CONTENT_URL, 'https' ) . '/uploads/network-dashboard/' . hash( 'sha256', dt_network_site_id() ) . '-' . $name . '.txt';
    }
    else {
        // default returns path
        return WP_CONTENT_DIR . '/uploads/network-dashboard/' . hash( 'sha256', dt_network_site_id() ) . '-' . $name . '.txt';
    }
}

/* Test if the log was created today. */
function dt_is_todays_log( $name ) : bool {
    if ( empty( $name ) ) {
        return false;
    }

    $file = dt_get_log_location( $name );

    if ( ! file_exists( $file ) ){
        return false;
    }

    $time = filemtime( $file );

    return ( $time < strtotime( 'today' ) );
}


//keep activity_timestamp from being recorded in dt_activity_log
add_filter( 'dt_ignore_fields_logging', function ( $fields ){
    $fields[] = 'activity_timestamp';
    return $fields;
});