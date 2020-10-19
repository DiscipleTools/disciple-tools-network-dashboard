<?php

/**
 * CREATE LOG
 */
add_action( 'dt_insert_activity', 'dt_network_dashboard_log_milestone_actions', 10, 1 );
function dt_network_dashboard_log_milestone_actions( $args ){

    /* new baptism report */
    if ( $args['object_subtype'] === 'milestones' && $args['meta_value'] === 'milestone_baptized' ) {
        $location = DT_Network_Activity_Log::get_location_details( $args['object_id'] );
        $data = [
            [
                'site_id' => dt_network_site_id(),
                'action' => 'new_baptism',
                'category' => '',
                'location_type' => $location['location_type'], // id, grid, lnglat, no_location
                'location_value' => $location['location_value'],
                'payload' => [
                    'language' => get_locale(),
                    'note' => 'is reporting a new baptism',
                    'unique_id' => hash( 'sha256', $args['object_id'] ),
                ],
                'timestamp' => time()
            ]
        ];
        DT_Network_Activity_Log::insert_log($data);
    }

}

/**
 * READ LOG
 */
add_filter( 'dt_network_dashboard_build_message', 'dt_network_dashboard_translate_log_milestone_actions', 10, 1 );
function dt_network_dashboard_translate_log_milestone_actions( $activity_log ){

    foreach( $activity_log as $index => $log ){

        /* new_baptism */
        if ( 'new_baptism' === $log['action'] ) {
            $activity_log[$index]['message'] = '('. $log['time'].')' . $log['site_name'] . ' is reporting a new baptism';
        }

    }

    return $activity_log;
}