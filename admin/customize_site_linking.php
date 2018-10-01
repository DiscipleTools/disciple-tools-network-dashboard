<?php
/**
 * Configures the site link system for the network reporting
 */

if ( ! get_option('dt_network_enabled') ) { // must check and see if DT already loads these customizations

    // Adds the type of network connection to the site link system
    add_filter( 'site_link_type', 'dt_network_dashboard_site_link_type', 10, 1 );
    function dt_network_dashboard_site_link_type( $type ) {
        $type[] = 'Network Dashboard (Base)';
        return $type;
    }

    // Add the specific capabilities needed for the site to site linking.
    add_filter( 'site_link_type_capabilities', 'dt_network_dashboard_site_link_capabilities', 10, 2 );
    function dt_network_dashboard_site_link_capabilities( $connection_type, $capabilities ) {
        if ( 'Network Dashboard (Base)' === $connection_type ) {
            $capabilities[] = 'network_dashboard_transfer';
        }
        return $capabilities;
    }

}