<?php

if ( defined( 'ABSPATH' ) ) {
    exit;
}

$wordpress_root_path = preg_replace( '/wp-content(?!.*wp-content).*/', '', __DIR__ );
require_once( $wordpress_root_path . 'wp-load.php' );

do_action( 'dt_network_dashboard_external_cron' );
