<?php

if ( defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! empty( $_SERVER['DOCUMENT_ROOT'] ) ){
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'; // @phpcs:ignore

do_action( 'dt_network_dashboard_external_cron' );