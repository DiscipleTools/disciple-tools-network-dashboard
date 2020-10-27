<?php
/**
 * This function returns the registered actions for logging.
 *
 * A new action being recorded into the movement_log must registered the action. It not only provides the label for
 * dropdowns and other selectors, it also provides the message_pattern for building the log message on the output.
 *
 * @param $prepare_for_sql bool  If true, returns a string for use in IN queries.
 *
 * @return array | string
 */
function dt_network_dashboard_registered_actions( $prepare_for_sql = false ) {
    $actions = apply_filters( 'dt_network_dashboard_register_actions', [] );

    if ( empty( $actions ) ){
        return ( $prepare_for_sql ) ? '' : [];
    }

    if ( $prepare_for_sql ) {
        $keys = array_keys( $actions );
        return dt_array_to_sql( $keys );
    } else {
        return $actions;
    }
}