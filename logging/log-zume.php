<?php

/**
 * READ LOG
 */
add_filter( 'dt_network_dashboard_build_message', 'zume_log_actions', 10, 1 );
function zume_log_actions( $activity_log ){

    foreach( $activity_log as $index => $log ){

        /* new_baptism */
        if ( 'studying' === substr( $log['action'], 0, 8 ) ) {
            $activity_log[$index]['message'] = '('. $log['time'].') ' . $log['site_name'] . '  is studying "' . $log['payload']['title'] . '" (' . $log['payload']['country'] . ')';
        }

        if ( 'leading' === substr( $log['action'], 0, 7 ) ) {
            $activity_log[$index]['message'] = '('. $log['time'].') ' . $log['site_name'] . '  is leading a group through session '.substr( $log['action'], -1, 1 ).'! (' . $log['payload']['country'] . ')';
        }

        if ( 'zume_training' === $log['action'] && 'joining' === $log['category'] ) {
            $activity_log[$index]['message'] = '('. $log['time'].') ' .'XX is registering for Zúme Training! (' . $log['payload']['country'] . ')';
        }

        if ( 'zume_vision' === $log['action'] && 'joining' === $log['category'] ) {
            $activity_log[$index]['message'] = '('. $log['time'].') ' .'XX is registering for Zúme Community! (' . $log['payload']['country'] . ')';
        }

    }

    return $activity_log;
}