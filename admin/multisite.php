<?php

/**
 * Tests if the site is approved as a network dashboard for the current multisite system.
 * This must be enabled in the Network Admin Panel of the multisite by a super admin
 *
 * @return bool
 */
function dt_multisite_network_dashboard_is_approved() :bool {
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

/**
 * @return array
 */
function dt_multisite_dashboard_snapshots() {
    if ( ! dt_is_current_multisite_dashboard_approved() ) {
        return [];
    }

    $snapshot = wp_cache_get( 'multisite_dashboard_snapshots' );

    if ( false === $snapshot ) {

        $site_ids = DT_Network_Dashboard_Queries::all_multisite_ids();

        $snapshot = [];
        foreach ( $site_ids as $id ) {
            if ( get_blog_option( $id, 'current_theme' ) !== 'Disciple Tools' ) {
                continue;
            }
            $snapshot[$id] = get_blog_option( $id, '_transient_dt_snapshot_report' );
        }
        wp_cache_set( 'multisite_dashboard_snapshots', $snapshot );
    }

    return $snapshot;
}