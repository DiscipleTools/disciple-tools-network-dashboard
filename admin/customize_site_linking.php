<?php
/**
 * Configures the site link system for the network reporting
 */

// Adds the type of network connection to the site link system
add_filter( 'site_link_type', 'dt_network_dashboard_site_link_type', 10, 1 );
function dt_network_dashboard_site_link_type( $type ) {
    $type[] = 'Network Dashboard';
    return $type;
}

// Add the specific capabilities needed for the site to site linking.
add_filter( 'site_link_type_capabilities', 'dt_network_dashboard_site_link_capabilities', 10, 2 );
function dt_network_dashboard_site_link_capabilities( $connection_type, $capabilities ) {
    if ( 'Network Reports' === $connection_type ) {
        $capabilities[] = 'network_dashboard';
    }
    return $capabilities;
}