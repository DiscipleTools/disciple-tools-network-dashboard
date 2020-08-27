<?php

class DT_Network_Activity_Hooks {
    private static $_instance = null;
    public static function instance() {
        if (is_null( self::$_instance )) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        add_action( 'dt_insert_activity', [ $this, 'filter_activity' ], 10, 1 );
    }

    public function filter_activity( $args ){
        /**
         * The purpose of these tests is to quickly filter out un-needed logs.
         */
        // Approved post type/object type
        $approved_object_type = [
            'contacts',
            'groups'
        ];
        if ( ! in_array( $args['object_type'], $approved_object_type ) ){
            return;
        }

        // Approved actions
        $approved_actions = [
            'field_update'
        ];
        if ( ! in_array( $args['action'], $approved_actions ) ){
            return;
        }

        // Approved subtypes
        $approved_object_subtype = [
            'milestones',
            'overall_status',
            'group_status',
            'baptism_generation',
            'seeker_path'
        ];
        if ( ! in_array( $args['object_subtype'], $approved_object_subtype ) ){
            return;
        } else {
            // Test for types of approved subtypes
            switch($args['object_subtype']){
                case 'milestones':
                    $approved = [
                        'milestone_belief',
                        'milestone_has_bible',
                        'milestone_reading_bible',
                        'milestone_baptized',
                        'milestone_baptizing',
                    ];
                    if ( ! in_array( $args['meta_value'], $approved ) ){
                        return;
                    }
                    break;
                case 'overall_status':
                    $approved = [
                        'new',
                        'active',
                    ];
                    if ( ! in_array( $args['meta_value'], $approved ) ){
                        return;
                    }
                    break;
                case 'group_status':
                    $approved = [
                        'active',
                    ];
                    if ( ! in_array( $args['meta_value'], $approved ) ){
                        return;
                    }
                    break;
                case 'seeker_path':
                    $approved = [
                        'met',
                        'coaching',
                        'established',
                    ];
                    if ( ! in_array( $args['meta_value'], $approved ) ){
                        return;
                    }
                    break;

                default:
                    break;
            }
        }



        $this->process_log( $args );
    }

    public function process_log( $args ){
        /*
         Array
            (
                [action] => logged_in
                [object_type] => User
                [object_subtype] =>
                [object_name] => chrischasm
                [object_id] => 2
                [hist_ip] => 0
                [hist_time] => 1598555052
                [object_note] =>
                [meta_id] =>
                [meta_key] =>
                [meta_value] =>
                [meta_parent] =>
                [old_value] =>
                [field_type] =>
                [user_caps] =>
                [user_id] => 0
            )
         *
         */

        $data = [
            [
                'site_id' => dt_network_site_id(),
                'action' => $args['object_subtype'],
                'category' => $args['object_type'],
                'location_type' => 'complete', // ip, grid, lnglat
                'location_value' => [
                    'lng' => '-104.968',
                    'lat' => '39.7075',
                    'level' => 'admin2',
                    'label' => 'Denver, Colorado, US',
                    'grid_id' => '100364508'
                ], // ip, grid, lnglat
                'payload' => [
                    'initials' => 'CC',
                    'group_size' => '3',
                    'country' => 'United States',
                    'language' => 'en',
                    'note' => $args['object_note']
                ],
                'timestamp' => time()
            ]
        ];
        DT_Network_Activity_Log::post_activity($data);

    }

}
DT_Network_Activity_Hooks::instance();