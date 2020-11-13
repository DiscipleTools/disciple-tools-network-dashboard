<?php

/**
 * REGISTER ACTIONS (AND CATEGORIES)
 */
add_filter( 'dt_network_dashboard_register_actions', 'dt_network_dashboard_register_action_new_posts', 10, 1 );
function dt_network_dashboard_register_action_new_posts( $actions ){

    $actions['new_contact'] = array(
        'key' => 'new_contact',
        'label' => 'New Contact',
        'message_pattern' => array()
    );
    $actions['new_pre-group'] = array(
        'key' => 'new_pre-group',
        'label' => 'New Pre-Group',
        'message_pattern' => array()
    );
    $actions['new_group'] = array(
        'key' => 'new_group',
        'label' => 'New Group',
        'message_pattern' => array()
    );
    $actions['new_church'] = array(
        'key' => 'new_church',
        'label' => 'New Church',
        'message_pattern' => array()
    );
    $actions['new_team'] = array(
        'key' => 'new_team',
        'label' => 'New Team',
        'message_pattern' => array()
    );
    $actions['new_baptism'] = array(
        'key' => 'new_baptism',
        'label' => 'New Baptism',
        'message_pattern' => array()
    );
    $actions['new_coaching'] = array(
        'key' => 'new_coaching',
        'label' => 'New Coaching',
        'message_pattern' => array()
    );

    return $actions;
}

/**
 * CREATE LOG
 */
add_action( 'dt_post_created', 'dt_network_dashboard_log_create_posts', 10, 3 );
function dt_network_dashboard_log_create_posts( $post_type, $post_id, $initial_fields ){

    /* new: pre-group, group, church, team */
    if ( $post_type === 'groups' ) {

        if ( isset( $initial_fields['group_type'] ) ){
            $action = $initial_fields['group_type'];
        } else {
            $action = 'group';
        }

        $location = DT_Network_Activity_Log::get_location_details( $post_id );
        $data = array(
            array(
                'site_id' => dt_network_site_id(),
                'site_object_id' => $post_id,
                'action' => 'new_' . $action,
                'category' => '',
                'location_type' => $location['location_type'], // id, grid, lnglat, no_location
                'location_value' => $location['location_value'],
                'payload' => array(
                    'language' => get_locale(),
                ),
                'timestamp' => time()
            )
        );

        DT_Network_Activity_Log::insert_log( $data );
    }

    /* new_contact */
    if ( $post_type === 'contacts' ) {

        $location = DT_Network_Activity_Log::get_location_details( $post_id );
        $data = array(
            array(
                'site_id' => dt_network_site_id(),
                'site_object_id' => $post_id,
                'action' => 'new_contact',
                'category' => '',
                'location_type' => $location['location_type'], // id, grid, lnglat, no_location
                'location_value' => $location['location_value'],
                'payload' => array(
                    'language' => get_locale(),
                ),
                'timestamp' => time()
            )
        );

        DT_Network_Activity_Log::insert_log( $data );
    }

}

add_action( 'dt_post_updated', 'dt_network_dashboard_log_update_posts', 10, 3 );
function dt_network_dashboard_log_update_posts( $post_type, $post_id, $initial_fields ){

    /* check if contact was created through the baptized_by contact create widget */
    if ( $post_type === 'contacts' && isset( $initial_fields['baptized_by'] ) ) {

        $location = DT_Network_Activity_Log::get_location_details( $post_id );
        $data = array(
            array(
                'site_id' => dt_network_site_id(),
                'site_object_id' => $post_id,
                'action' => 'new_baptism',
                'category' => '',
                'location_type' => $location['location_type'], // id, grid, lnglat, no_location
                'location_value' => $location['location_value'],
                'payload' => array(
                    'language' => get_locale(),
                ),
                'timestamp' => time()
            )
        );

        DT_Network_Activity_Log::insert_log( $data );
    }

    /* check if contact was created through the baptized_by contact create widget */
    if ( $post_type === 'contacts' && ( isset( $initial_fields['coached_by'] ) || isset( $initial_fields['coaching'] ) ) ) {

        $location = DT_Network_Activity_Log::get_location_details( $post_id );
        $data = array(
            array(
                'site_id' => dt_network_site_id(),
                'site_object_id' => $post_id,
                'action' => 'new_coaching',
                'category' => '',
                'location_type' => $location['location_type'], // id, grid, lnglat, no_location
                'location_value' => $location['location_value'],
                'payload' => array(
                    'language' => get_locale(),
                ),
                'timestamp' => time()
            )
        );

        DT_Network_Activity_Log::insert_log( $data );
    }
}

/**
 * READ LOG
 */
add_filter( 'dt_network_dashboard_build_message', 'dt_network_dashboard_translate_log_new_posts', 10, 1 );
function dt_network_dashboard_translate_log_new_posts( $activity_log ){

    foreach ( $activity_log as $index => $log ){

        /* new_contact */
        if ( 'new_contact' === $log['action'] ) {
            $activity_log[$index]['message'] = $log['site_name'] . ' is reporting a new contact.';
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
