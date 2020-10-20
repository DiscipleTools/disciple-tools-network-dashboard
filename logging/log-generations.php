<?php

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
                'action' => 'generation_' . $post['group_type']['key'],
                'category' => '',
                'location_type' => $location['location_type'], // id, grid, lnglat, no_location
                'location_value' => $location['location_value'],
                'payload' => [
                    'language' => get_locale(),
                    'note' => 'is reporting a new ' . isset( $initial_fields['child_groups'] ) ? 'child ' : 'parent ' . $post['group_type']['key'],
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