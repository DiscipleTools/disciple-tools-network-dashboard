<?php

class DT_Network_Activity_Hooks {
    private static $_instance = null;
    public static function instance() {
        if (is_null( self::$_instance )) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public $site_id;

    public function __construct() {
        $this->site_id = dt_network_site_id();
        add_action( 'dt_insert_activity', [ $this, 'load_filters' ], 10, 1 );
    }

    public function load_filters( $args ) {


        // new seeker contact added
        $this->filter_for_new_contact( $args );
        $this->filter_for_new_group( $args );

        // new profession of faith

        // new baptism reported

        // new group reported

        // new church reported

        // new disciple generation reported

        // new group generation reported

        // new baptism generation reported
        $this->filter_for_new_baptism( $args );

        // new gospel share reported

        // new coaching relationship begun
        $this->filter_for_new_coaching( $args );

    }

    public function messages() {
        /* These can get redistributed later, but it seems easiest to consolidate them for editing and review. */
        $messages = [
            'gospel_share' => [
                'action' => 'gospel_share',
                'category' => '',
                'note'
            ],
            '' => [
                'action' => '',
                'category' => '',
                'note'
            ],
            '' => [
                'action' => '',
                'category' => '',
                'note'
            ],
            '' => [
                'action' => '',
                'category' => '',
                'note'
            ],
            '' => [
                'action' => '',
                'category' => '',
                'note'
            ],
            '' => [
                'action' => '',
                'category' => '',
                'note'
            ],
            '' => [
                'action' => '',
                'category' => '',
                'note'
            ],
            '' => [
                'action' => '',
                'category' => '',
                'note'
            ],
            '' => [
                'action' => '',
                'category' => '',
                'note'
            ],
        ];

        return apply_filters( 'dt_network_dashboard_action_messages', $messages );
    }

    // new seeker contact added
    public function filter_for_new_contact( $args ){
        $object_type = 'contacts';
        $object_subtype = '';
        $meta_value = 'created';

        if ( $args['object_type'] === $object_type && $args['object_subtype'] === $object_subtype && $args['meta_value'] === $meta_value ) {
            $location = $this->get_location_details( $args );
            $data = [
                [
                    'site_id' => $this->site_id,
                    'action' => 'new_contact',
                    'category' => 'connecting',
                    'location_type' => $location['location_type'],
                    'location_value' => $location['location_value'], // ip, grid, lnglat
                    'payload' => [
                        'language' => get_locale(),
                        'note' => 'is reporting a new contact'
                    ],
                    'timestamp' => $args['hist_time']
                ]
            ];
            DT_Network_Activity_Log::insert_log($data);
        }
    }

    public function filter_for_new_group( $args ){
        $object_type = 'groups';
        $object_subtype = '';
        $meta_value = 'created';

        if ( $args['object_type'] === $object_type && $args['object_subtype'] === $object_subtype && $args['meta_value'] === $meta_value ) {
            $location = $this->get_location_details( $args );
            $data = [
                [
                    'site_id' => $this->site_id,
                    'action' => 'new',
                    'category' => 'forming',
                    'location_type' => $location['location_type'],
                    'location_value' => $location['location_value'], // ip, grid, lnglat
                    'payload' => [
                        'language' => get_locale(),
                        'note' => 'is reporting a new group'
                    ],
                    'timestamp' => $args['hist_time']
                ]
            ];
            DT_Network_Activity_Log::insert_log($data);
        }
    }

    // new profession of faith

    // new baptism reported

    // new group reported

    // new church reported

    // new disciple generation reported

    // new group generation reported

    // new baptism generation reported
    public function filter_for_new_baptism( $args ){
        $object_subtype = 'milestones';
        $meta_value = 'milestone_baptized';

        if ( $args['object_subtype'] === $object_subtype && $args['meta_value'] === $meta_value ) {
            $location = $this->get_location_details( $args );
            $data = [
                [
                    'site_id' => $this->site_id,
                    'action' => 'milestone_baptized',
                    'category' => 'contacts',
                    'location_type' => $location['location_type'],
                    'location_value' => $location['location_value'], // ip, grid, lnglat
                    'payload' => [
                        'language' => get_locale(),
                        'note' => 'is reporting a new baptism'
                    ],
                    'timestamp' => time()
                ]
            ];
            DT_Network_Activity_Log::insert_log($data);
        }
    }

    // new coaching relationship begun
    public function filter_for_new_coaching( $args ){
        $object_subtype = 'seeker_path';
        $meta_value = 'coaching';

        if ( $args['object_subtype'] === $object_subtype && $args['meta_value'] === $meta_value ) {
            $location = $this->get_location_details( $args );
            $data = [
                [
                    'site_id' => $this->site_id,
                    'action' => 'seeker_path_coaching',
                    'category' => 'contacts',
                    'location_type' => $location['location_type'],
                    'location_value' => $location['location_value'], // ip, grid, lnglat
                    'payload' => [
                        'language' => get_locale(),
                        'note' => 'is reporting a new spiritual coaching relationship'
                    ],
                    'timestamp' => time()
                ]
            ];
            DT_Network_Activity_Log::insert_log($data);
        }
    }

    public function get_location_details( $args ){
        if ( $grid = get_post_meta( $args['object_id'], 'location_grid' ) ){
            $location = [
                'location_type' => 'grid',
                'location_value' => $grid[0],
            ];
        } else {
            $location = [
                'location_type' => 'ip',
                'location_value' => Site_Link_System::get_real_ip_address(),
            ];
        }
        return $location;
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

    public function new_item_reported( $args ){
        // new seeker contact added

        // new profession of faith

        // new baptism reported

        // new group reported

        // new church reported

        // new disciple generation reported

        // new group generation reported

        // new baptism generation reported

        // new gospel share reported

    }

    public function ongoing_meeting_reported( $args ){
        //
    }

    public function add_activity_log( $args ){
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