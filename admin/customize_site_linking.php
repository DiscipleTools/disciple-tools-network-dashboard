<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Configures the site link system for the network reporting
 */

if ( ! get_option( 'dt_network_enabled') ) { // test if local DT is already providing this type
    // Adds the type of network connection to the site link system
    add_filter( 'site_link_type', 'dt_network_dashboard_site_link_type', 10, 1 );
    function dt_network_dashboard_site_link_type( $type ) {
        $type['network_dashboard'] = __('Network Dashboard');
        return $type;
    }

    // Add the specific capabilities needed for the site to site linking.
    add_filter( 'site_link_type_capabilities', 'dt_network_dashboard_site_link_capabilities', 10, 1 );
    function dt_network_dashboard_site_link_capabilities( $args ) {
        if ( 'network_dashboard' === $args['connection_type'] ) {
            $args['capabilities'][] = 'network_dashboard_transfer';
        }
        return $args;
    }
}

