<?php
/**
 * Globally set the permissions to check
 */

/**
 * If a person has permissions, the it returns false
 * All other cases it returns a true
 * @param null $type
 *
 * @return bool
 */
function dt_network_dashboard_denied( $type = null ): bool {

    switch ( $type ) {
        // add other cases if necessary
        default:
            if ( current_user_can( 'view_any_contacts' ) || current_user_can( 'view_project_metrics' ) ) {
                return false;
            }
            break;
    }
    return true;

}

function dt_network_dashboard_has_permission(){
    if ( current_user_can( 'view_any_contacts' ) || current_user_can( 'view_project_metrics' ) ) {
        return true;
    }
    return false;
}

/**
 * Configures the site link system for the network reporting
 */

// Adds the type of network connection to the site link system
add_filter( 'site_link_type', 'dt_network_dashboard_site_link_type', 10, 1 );
function dt_network_dashboard_site_link_type( $type ) {
    $type['network_dashboard_both'] = __( 'Network Dashboard Transfer Both Ways' );
    $type['network_dashboard_receiving'] = __( 'Network Dashboard Receiving Only' );
    $type['network_dashboard_sending'] = __( 'Network Dashboard Sending Only' );
    return $type;
}

// Add the specific capabilities needed for the site to site linking.
add_filter( 'site_link_type_capabilities', 'dt_network_dashboard_site_link_capabilities', 10, 1 );
function dt_network_dashboard_site_link_capabilities( $args ) {
    if ( 'network_dashboard_receiving' === $args['connection_type'] || 'network_dashboard_both' === $args['connection_type'] || 'network_dashboard_sending' === $args['connection_type'] ) {
        $args['capabilities'][] = 'network_dashboard_both';
        $args['capabilities'][] = 'network_dashboard_receiving';
        $args['capabilities'][] = 'network_dashboard_sending';
    }
    return $args;
}


/**
 * Tests if the site is approved as a network dashboard for the current multisite system.
 * This must be enabled in the Network Admin Panel of the multisite by a super admin
 *
 * @return bool
 */
function dt_network_dashboard_multisite_is_approved() :bool {
    if ( ! is_multisite() ) {
        return false;
    }

    $approved_sites = dt_dashboard_approved_sites();
    if ( empty( $approved_sites ) ) {
        return false;
    }
    foreach ( $approved_sites as $key => $site ) {
        if ( get_current_blog_id() === $key ) {
            return true;
        }
    }

    return false;
}

/**
 * @return bool|array
 *                   if get is empty, return empty array
 *                   if get success, return option array
 *                   if update success, return true, else false
 *                   if delete success, return true, else false
 *                   anything else return false
 */
function dt_dashboard_approved_sites( $type = 'get', $data = null ) {
    if ( $type === 'get' ) {
        return get_site_option( 'dt_dashboard_approved_sites', [] );
    } else if ( $type === 'update' ) {
        return update_site_option( 'dt_dashboard_approved_sites', $data );
    } else if ( $type === 'delete' ) {
        return delete_site_option( 'dt_dashboard_approved_sites' );
    } else {
        return false;
    }
}

/**
 * @return bool
 */
function dt_is_current_multisite_dashboard_approved() :bool {
    if ( ! is_multisite() ) {
        return false;
    }

    $current_site_id = get_current_blog_id();
    $enabled_sites = dt_dashboard_approved_sites();
    if ( ! isset( $enabled_sites[$current_site_id] ) ) {
        return false;
    }

    return true;
}

function dt_is_network_dashboard_plugin_active( $site_id = null ) :bool {

    if ( is_multisite() ){
        $active_plugins = get_blog_option( $site_id, 'active_plugins' );
        if ( in_array( 'disciple-tools-network-dashboard/disciple-tools-network-dashboard.php', $active_plugins ) ){
            return true;
        }
        if ( is_plugin_active_for_network( 'disciple-tools-network-dashboard/disciple-tools-network-dashboard.php' ) ) {
            return true;
        }
    } else {
        $active_plugins = get_option( 'active_plugins' );
        if ( in_array( 'disciple-tools-network-dashboard/disciple-tools-network-dashboard.php', $active_plugins ) ){
            return true;
        }
    }

    return false;
}

/**
 * @param int $id
 *
 * @return array
 */
function dt_get_dashboard_approved_sites_by_id( int $id ) :array {
    $approved_sites = dt_dashboard_approved_sites();
    if ( isset( $approved_sites[$id] ) ) {
        return $approved_sites[$id];
    }
    return [];
}