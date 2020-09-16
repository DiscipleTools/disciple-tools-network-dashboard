<?php
/**
 * This filter allows the person to hide the default top nav of Disciple Tools and just show the Network Dashboard link
 *
 * @param $state
 *
 * @return bool
 */
function dt_remove_top_nav_for_dashboard( $state ) {
    if ( get_option( 'dt_hide_top_menu' ) ) {
        return false;
    }
    return $state;
}
add_filter( 'dt_show_default_top_menu', 'dt_remove_top_nav_for_dashboard', 99, 1 );

function dt_redirect_front_page( $url ) {
    if ( get_option( 'dt_hide_top_menu' ) ) {
        return home_url( '/network' );
    }
    return $url;
}
add_filter( 'dt_front_page', 'dt_redirect_front_page' );