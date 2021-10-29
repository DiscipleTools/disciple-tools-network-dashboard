<?php

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

if ( 'show' !== get_option( 'dt_network_dashboard_show_tab' ) ){
    return;
}
/**
 * Add Top Navigation
 */
add_filter( 'desktop_navbar_menu_options', 'dt_network_dashboard_top_nav_desktop', 110, 1 );
function dt_network_dashboard_top_nav_desktop( $tabs ) {
    if ( dt_network_dashboard_has_metrics_permissions() ) {
        if ( 'yes' === get_option( 'dt_network_dashboard_dedicated' ) && ! user_can( get_current_user_id(), 'manage_dt' ) ){
            $tabs = [];
        }
        $tabs['network'] = [
            "link" => site_url( '/network/' ),
            "label" => __( "Network", 'disciple-tools-network-dashboard' )
        ];
    }
    return $tabs;
}


/**
 * Has permissions
 * @return bool
 */
function dt_network_dashboard_has_metrics_permissions() : bool {
    $permissions = array( 'dt_all_access_contacts', 'view_project_metrics', 'view_network_dashboard' );
    foreach ( $permissions as $permission ){
        if ( current_user_can( $permission ) ){
            return true;
        }
    }
    return false;
}


// test if has permissions to network dashboard
if ( ! dt_network_dashboard_has_metrics_permissions() ){
    return;
} // test if has permissions

// test if within url /network
$url_path = dt_get_url_path();
$is_rest = dt_is_rest();
if ( 'network' !== substr( $url_path, '0', 7 ) && ! $is_rest ) {
    return;
}

// load required files
require_once( 'base.php' );
require_once( 'mapping-module-config.php' );

// scan load
$dir = scandir( __DIR__ );
foreach ( $dir as $file ){
    if ( 'network' === substr( $file, 0, 7 ) && 'php' === substr( $file, -3, 3 ) ){
        require_once( $file );
    }
}

/**
 * Build Menu
 */
add_filter( 'dt_metrics_menu', 'dt_network_dashboard_build_menu', 10 );
function dt_network_dashboard_build_menu( $content ){
    $menu = apply_filters( 'dt_network_dashboard_build_menu', array() );

    // l1
    foreach ( $menu as $key => $value ){
        $content .= '<li><a href="' . esc_url( $value['url'] )  . '" id="'.esc_attr( $value['key'] ).'">' . esc_html( $value['label'] ). '</a>';
        if ( isset( $value['children'] ) && ! empty( $value['children'] ) ){
            $content .= '<ul class="menu vertical nested is-active" aria-expanded="true" id="'.esc_attr( $value['key'] ).'">';
            // l2
            foreach ( $value['children'] as $child ){
                $content .= '<li><a href="' . esc_url( $child['url'] )  . '" id="'.esc_attr( $child['key'] ).'">' . esc_html( $child['label'] ). '</a>';
                if ( isset( $child['children'] ) && ! empty( $child['children'] ) ){
                    $content .= '<ul class="menu vertical nested is-active" aria-expanded="true" id="'.esc_attr( $child['key'] ).'">';
                    // l3
                    foreach ( $child['children'] as $grandchild ){
                        $content .= '<li><a href="' . esc_url( $grandchild['url'] )  . '" id="'.esc_attr( $grandchild['key'] ).'">' . esc_html( $grandchild['label'] ). '</a></li>';
                    }
                    $content .= '</ul>';
                }
                $content .= '</li>';
            }
            $content .= '</ul>';
        }
        $content .= '</li>';

    }

    return $content;
}

