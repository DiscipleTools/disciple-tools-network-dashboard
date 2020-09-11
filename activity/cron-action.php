<?php

add_action('dt_network_dashboard_external_cron', 'dt_activity_log_payload_check' );
/**
 * Checks if all the payload elements are translated into meta records.
 */
function dt_activity_log_payload_check(){
    $process_status = [];
    $process_status['event'] = __METHOD__;
    $process_status['start'] = microtime(true); // @todo remove after development

    global $wpdb;
    $results = $wpdb->get_results( "
        SELECT ml.id, ml.payload
        FROM $wpdb->dt_movement_log as ml
        LEFT JOIN $wpdb->dt_movement_log_meta as mlm ON mlm.ml_id=ml.id
        WHERE mlm.meta_id IS NULL
    ", ARRAY_A );

    foreach( $results as $result ) {
        $payload = maybe_unserialize( $result['payload'] );
        DT_Network_Activity_Log::build_meta( $result['id'], $payload, true );
    }
    $process_status['stop'] = microtime(true);
}