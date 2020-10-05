<?php

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

/**
 * Has permissions
 * @return bool
 */
function dt_network_dashboard_has_metrics_permissions() : bool {
    $permissions = ['view_any_contacts', 'view_project_metrics'];
    foreach ( $permissions as $permission ){
        if ( current_user_can( $permission ) ){
            return true;
        }
    }
    return false;
}

/**
 * Add Top Navigation
 */
add_action( 'dt_top_nav_desktop', 'dt_network_dashboard_top_nav_desktop');
function dt_network_dashboard_top_nav_desktop() {
    if ( dt_network_dashboard_has_metrics_permissions() ) {
        ?>
        <li><a href="<?php echo esc_url( site_url( '/network/' ) ); ?>"><?php esc_html_e( "Network" ); ?></a></li><?php
    }
}

// test if has permissions to network dashboard
if ( ! dt_network_dashboard_has_metrics_permissions() ){
    return;
} // test if has permissions

// test if within url /network
$url_path = dt_get_url_path();
if ('network' !== substr( $url_path, '0', 7 )) {
    return;
}

// load required files
require_once( 'base.php' );

require_once( 'network-home.php' );
require_once( 'network-activity.php' );