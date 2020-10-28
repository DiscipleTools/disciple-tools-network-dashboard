<?php

/**
* REGISTER ACTIONS (AND CATEGORIES)
*/
add_action( 'dt_network_dashboard_register_actions', 'dt_network_dashboard_register_action_generations', 10, 1 );
function dt_network_dashboard_register_action_generations( $actions ){

    $actions['generation_pre-group'] = [
        'key' => 'generation_pre-group',
        'label' => 'Pre-Group Generations',
        'message_pattern' => [

        ]
    ];

    $actions['generation_group'] = [
        'key' => 'generation_group',
        'label' => 'Group Generations',
        'message_pattern' => [

        ]
    ];

    $actions['generation_church'] = [
        'key' => 'generation_church',
        'label' => 'Church Generations',
        'message_pattern' => [

        ]
    ];

    $actions['generation_team'] = [
        'key' => 'generation_team',
        'label' => 'Team Generations',
        'message_pattern' => [

        ]
    ];

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
        $data = [
            [
                'site_id' => dt_network_site_id(),
                'site_object_id' => $post_id,
                'action' => 'generation_' . $post['group_type']['key'],
                'category' => '',
                'location_type' => $location['location_type'], // id, grid, lnglat, no_location
                'location_value' => $location['location_value'],
                'payload' => [
                    'language' => get_locale(),
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
add_filter( 'dt_network_dashboard_build_message', 'dt_network_dashboard_translate_log_generations', 10, 1 );
function dt_network_dashboard_translate_log_generations( $activity_log ){

    foreach( $activity_log as $index => $log ){

        /* generation_pre-group */
        if ( 'generation_pre-group' === $log['action'] ) {
            $activity_log[$index]['message'] = $log['site_name'] . ' is reporting a new pre-group generation';
        }

        /* generation_group */
        if ( 'generation_group' === $log['action'] ) {
            $activity_log[$index]['message'] = $log['site_name'] . ' is reporting a new group generation';
        }

        /* generation_church */
        if ( 'generation_church' === $log['action'] ) {
            $activity_log[$index]['message'] = $log['site_name'] . ' is reporting a new church generation';
        }

        /* generation_team */
        if ( 'generation_team' === $log['action'] ) {
            $activity_log[$index]['message'] = $log['site_name'] . ' is reporting a new leadership team generation';
        }

    }

    return $activity_log;
}