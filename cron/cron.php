<?php

if (defined('ABSPATH')) {
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';

do_action('dt_network_dashboard_external_cron' );

try {
    $object = new DT_Network_Dashboard_Cron_Collect_Remote_Sites_Async();
    return $object->force_run_action();
} catch ( Exception $e ) {
    dt_write_log( $e );
    return $e;
}
