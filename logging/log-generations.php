<?php

/**
* REGISTER ACTIONS (AND CATEGORIES)
*/
add_filter( 'dt_network_dashboard_register_actions', 'dt_network_dashboard_register_action_generations', 10, 1 );
function dt_network_dashboard_register_action_generations( $actions ){

    $actions['generation_pre-group'] = array(
        'key' => 'generation_pre-group',
        'label' => __( 'Pre-Group Generations', 'disciple-tools-network-dashboard' ),
        'message_pattern' => array()
    );

    $actions['generation_group'] = array(
        'key' => 'generation_group',
        'label' => __( 'Group Generations', 'disciple-tools-network-dashboard' ),
        'message_pattern' => array()
    );

    $actions['generation_church'] = array(
        'key' => 'generation_church',
        'label' => __( 'Church Generations', 'disciple-tools-network-dashboard' ),
        'message_pattern' => array()
    );

    $actions['generation_team'] = array(
        'key' => 'generation_team',
        'label' => __( 'Team Generations', 'disciple-tools-network-dashboard' ),
        'message_pattern' => array()
    );

    return $actions;
}

/**
 * CREATE LOG
 */
add_action( 'dt_post_updated', 'dt_network_dashboard_log_generations', 10, 5 );
function dt_network_dashboard_log_generations( $post_type, $post_id, $initial_fields, $existing_post, $post ){

    /* new: pre-group, group, church, team */
    if ( $post_type === 'groups' && ( isset( $initial_fields['child_groups'] ) || isset( $initial_fields['parent_groups'] ) ) ) {

        $location = DT_Network_Activity_Log::get_location_details( $post_id );
        $data = array(
            array(
                'site_id' => dt_network_site_id(),
                'site_object_id' => $post_id,
                'action' => 'generation_' . $post['group_type']['key'],
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
add_filter( 'dt_network_dashboard_build_message', 'dt_network_dashboard_translate_log_generations', 10, 1 );
function dt_network_dashboard_translate_log_generations( $activity_log ){

    foreach ( $activity_log as $index => $log ){

        /* generation_pre-group */
        if ( 'generation_pre-group' === $log['action'] ) {
            $activity_log[$index]['message'] = sprintf( __( '%s is reporting a new pre-group generation.', 'disciple-tools-network-dashboard' ), $log['site_name'] );
        }

        /* generation_group */
        if ( 'generation_group' === $log['action'] ) {
            $activity_log[$index]['message'] = sprintf( __( '%s is reporting a new group generation.', 'disciple-tools-network-dashboard' ), $log['site_name'] );
        }

        /* generation_church */
        if ( 'generation_church' === $log['action'] ) {
            $activity_log[$index]['message'] = sprintf( __( '%s is reporting a new church generation.', 'disciple-tools-network-dashboard' ), $log['site_name'] );
        }

        /* generation_team */
        if ( 'generation_team' === $log['action'] ) {
            $activity_log[$index]['message'] = sprintf( __( '%s is reporting a new leadership team generation.', 'disciple-tools-network-dashboard' ), $log['site_name'] );
        }
    }

    return $activity_log;
}
