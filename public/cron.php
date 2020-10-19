<?php

if (defined('ABSPATH')) {
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';

do_action('dt_network_dashboard_external_cron' );