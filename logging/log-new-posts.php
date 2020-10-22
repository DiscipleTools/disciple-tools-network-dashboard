<?php

/**
 * CREATE LOG
 */
add_action( 'dt_post_created', 'dt_network_dashboard_log_new_posts', 10, 3 );
add_action( 'dt_post_updated', 'dt_network_dashboard_log_new_posts', 10, 3 );
function dt_network_dashboard_log_new_posts( $post_type, $post_id, $initial_fields ){

    /* new: pre-group, group, church, team */
    if ( $post_type === 'groups' && isset( $initial_fields['group_type'] ) ) {

        $location = DT_Network_Activity_Log::get_location_details( $post_id );
        $data = [
            [
                'site_id' => dt_network_site_id(),
                'action' => 'new_' . $initial_fields['group_type'],
                'category' => '',
                'location_type' => $location['location_type'], // id, grid, lnglat, no_location
                'location_value' => $location['location_value'],
                'payload' => [
                    'language' => get_locale(),
                    'note' => 'is reporting a new ' . $initial_fields['group_type'],
                    'unique_id' => hash( 'sha256', $post_id ),
                ],
                'timestamp' => time()
            ]
        ];

        DT_Network_Activity_Log::insert_log($data);
    }

    /* new_contact */
    if ( $post_type === 'contacts' ) {

        $location = DT_Network_Activity_Log::get_location_details( $post_id );
        $data = [
            [
                'site_id' => dt_network_site_id(),
                'action' => 'new_contact',
                'category' => '',
                'location_type' => $location['location_type'], // id, grid, lnglat, no_location
                'location_value' => $location['location_value'],
                'payload' => [
                    'language' => get_locale(),
                    'note' => 'is reporting a new contact',
                    'unique_id' => hash( 'sha256', $post_id ),
                ],
                'timestamp' => time()
            ]
        ];

        DT_Network_Activity_Log::insert_log($data);
    }

    /* check if contact was created through the baptized_by contact create widget */
    if ( $post_type === 'contacts' && isset( $initial_fields['baptized_by']) ) {

        $location = DT_Network_Activity_Log::get_location_details( $post_id );
        $data = [
            [
                'site_id' => dt_network_site_id(),
                'action' => 'new_baptism',
                'category' => '',
                'location_type' => $location['location_type'], // id, grid, lnglat, no_location
                'location_value' => $location['location_value'],
                'payload' => [
                    'language' => get_locale(),
                    'note' => 'is reporting a new baptism of a new contact',
                    'unique_id' => hash( 'sha256', $post_id ),
                ],
                'timestamp' => time()
            ]
        ];

        DT_Network_Activity_Log::insert_log($data);
    }

    /* check if contact was created through the baptized_by contact create widget */
    if ( $post_type === 'contacts' && ( isset( $initial_fields['coached_by'] ) || isset( $initial_fields['coaching'] ) ) ) {

        $location = DT_Network_Activity_Log::get_location_details( $post_id );
        $data = [
            [
                'site_id' => dt_network_site_id(),
                'action' => 'new_coaching',
                'category' => '',
                'location_type' => $location['location_type'], // id, grid, lnglat, no_location
                'location_value' => $location['location_value'],
                'payload' => [
                    'language' => get_locale(),
                    'note' => 'is reporting a new coaching relationship',
                    'unique_id' => hash( 'sha256', $post_id ),
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
add_filter( 'dt_network_dashboard_build_message', 'dt_network_dashboard_translate_log_new_posts', 10, 1 );
function dt_network_dashboard_translate_log_new_posts( $activity_log ){

    foreach( $activity_log as $index => $log ){

        /* new_contact */
        if ( 'new_contact' === $log['action'] ) {
            $activity_log[$index]['message'] = $log['site_name'] . ' is reporting a new contact';
        }

        /* new_pre-group */
        if ( 'new_pre-group' === $log['action'] ) {
            $activity_log[$index]['message'] = $log['site_name'] . ' is reporting a new pre-group formed';
        }

        /* new_group */
        if ( 'new_group' === $log['action'] ) {
            $activity_log[$index]['message'] = $log['site_name'] . ' is reporting a new group formed';
        }

        /* new_church */
        if ( 'new_church' === $log['action'] ) {
            $activity_log[$index]['message'] = $log['site_name'] . ' is reporting a new church';
        }

        /* new_team */
        if ( 'new_team' === $log['action'] ) {
            $activity_log[$index]['message'] = $log['site_name'] . ' is reporting a new leadership team formed';
        }

        /* new_baptism */
        if ( 'new_baptism' === $log['action'] ) {
            $activity_log[$index]['message'] = $log['site_name'] . ' is reporting a new baptism';
        }

        /* new_coaching */
        if ( 'new_coaching' === $log['action'] ) {
            $activity_log[$index]['message'] = $log['site_name'] . ' is reporting an coaching relationship';
        }
    }

    return $activity_log;
}