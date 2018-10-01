<?php

function dtnd_get_report_by_id(  $id ) {
    global $wpdb;
    $results = $wpdb->get_results($wpdb->prepare( "SELECT * FROM $wpdb->dt_network_reports WHERE id = %s", $id), ARRAY_A);
    return $results;
}