<?php
/**
 * Core functions to power the network features of Disciple Tools
 *
 * @class      Disciple_Tools_Notifications
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple_Tools
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class DT_Network_Dashboard_Snapshot_Report
{
    public static function snapshot_report( $force_refresh = false )
    {

        $time = get_option('dt_snapshot_report_timestamp');
        if ($time < (time() - (24 * 60 * 60))) {
            $force_refresh = true;
        }

        if (!$force_refresh) {
            return get_option('dt_snapshot_report');
        }

        $profile = dt_network_site_profile();

        $report_data = [
            'partner_id' => $profile['partner_id'],
            'profile' => $profile,
            'contacts' => [
                'current_state' => self::contacts_current_state(),
                'added' => [
                    'sixty_days' => self::counted_by_day(),
                    'twenty_four_months' => self::counted_by_month(),
                ],
                'baptisms' => [
                    'current_state' => [
                        'all_baptisms' => Disciple_Tools_Network_Queries::total_baptisms(),
                    ],
                    'added' => [
                        'sixty_days' => self::counted_by_day('baptisms'),
                        'twenty_four_months' => self::counted_by_month('baptisms'),
                    ],
                    'generations' => self::generations('baptisms'),
                ],
                'follow_up_funnel' => [
                    'funnel' => self::funnel(),
                    'ongoing_meetings' => self::ongoing_meetings(),
                    'coaching' => self::coaching(),
                ],
            ],
            'groups' => [
                'current_state' => self::groups_current_state(),
                'by_types' => self::groups_by_type(),
                'added' => [
                    'sixty_days' => self::counted_by_day('groups'),
                    'twenty_four_months' => self::counted_by_month('groups'),
                ],
                'health' => self::group_health(),
                'church_generations' => self::generations('church'),
                'group_generations' => self::generations('groups'),
            ],
            'users' => [
                'current_state' => self::users_current_state(),
                'login_activity' => [
                    'sixty_days' => self::counted_by_day('logged_in'),
                    'twenty_four_months' => self::counted_by_month('logged_in'),
                ],
                'last_thirty_day_engagement' => self::user_logins_last_thirty_days(),
            ],
            'locations' => [
                'data_types' => self::location_data_types(),
                'countries' => self::get_locations_list(true),
                'current_state' => self::get_locations_current_state(),
                'list' => self::get_locations_list(),
                'contacts' => [
                    'all' => Disciple_Tools_Mapping_Queries::query_contacts_location_grid_totals(),
                    'active' => Disciple_Tools_Mapping_Queries::query_contacts_location_grid_totals('active'),
                    'paused' => Disciple_Tools_Mapping_Queries::query_contacts_location_grid_totals('paused'),
                    'closed' => Disciple_Tools_Mapping_Queries::query_contacts_location_grid_totals('closed'),
                ],
                'groups' => [
                    'all' => Disciple_Tools_Mapping_Queries::query_groups_location_grid_totals(),
                    'active' => Disciple_Tools_Mapping_Queries::query_groups_location_grid_totals('active'),
                    'inactive' => Disciple_Tools_Mapping_Queries::query_groups_location_grid_totals('inactive'),
                ],
                'churches' => [
                    'all' => Disciple_Tools_Mapping_Queries::query_church_location_grid_totals(),
                    'active' => Disciple_Tools_Mapping_Queries::query_church_location_grid_totals('active'),
                    'inactive' => Disciple_Tools_Mapping_Queries::query_church_location_grid_totals('inactive'),
                ],
                'users' => [
                    'all' => Disciple_Tools_Mapping_Queries::query_user_location_grid_totals(),
                    'active' => Disciple_Tools_Mapping_Queries::query_user_location_grid_totals('active'),
                    'inactive' => Disciple_Tools_Mapping_Queries::query_user_location_grid_totals('inactive'),
                ]
            ]
        ];

        $report_data = apply_filters( 'dt_network_dashboard_snapshot_report', $report_data );

        $report_data['hash'] = hash( 'sha256', maybe_serialize( $report_data ) );

        $report_data['date'] = current_time('timestamp');
        $report_data['timestamp'] = time();
        $report_data['status'] = 'OK';

        if ($report_data) {
            update_option('dt_snapshot_report', $report_data, false);
            update_option('dt_snapshot_report_timestamp', time(), false);
            update_option('dt_snapshot_report_hash', $report_data['hash'], false);

            return $report_data;
        } else {
            return new WP_Error(__METHOD__, 'Failed to get report');
        }
    }

    public static function contacts_current_state()
    {
        $data = [
            'all_contacts' => 0,
            'critical_path' => [],
        ];

        // Add critical path

        if (!class_exists('DT_Metrics_Contacts_Overview')) {
            require_once(get_template_directory() . '/dt-metrics/contacts/overview.php');
        }
        $contacts = new DT_Metrics_Contacts_Overview();

        $critical_path = $contacts->query_project_contacts_progress();
        foreach ($critical_path as $path) {
            $data['critical_path'][$path['key']] = $path;
        }

        // Add
        $data['status'] = self::get_contacts_status();

        $data['all_contacts'] = Disciple_Tools_Network_Queries::all_contacts();

        return $data;
    }

    /**
     * Gets an array list of all contacts current status.
     * [new] => 0
     * [unassignable] => 0
     * [unassigned] => 0
     * [assigned] => 6
     * [active] => 38
     * [paused] => 5
     * [closed] => 5
     *
     * @return array
     */
    public static function get_contacts_status(): array
    {
        $data = [];
        $contact_fields = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
        $status_defaults = $contact_fields['overall_status']['default'];
        $current_state = Disciple_Tools_Network_Queries::contacts_current_state();
        foreach ($status_defaults as $key => $status) {
            $data[$key] = 0;
            foreach ($current_state as $state) {
                if ($state['status'] === $key) {
                    $data[$key] = (int)$state['count'];
                }
            }
        }

        return $data;
    }

    public static function counted_by_day($type = null)
    {
        $data1 = [];
        $data2 = [];
        $data3 = [];

        switch ($type) {
            case 'groups':
                $dates = Disciple_Tools_Network_Queries::counted_by_day('created', 'groups');
                break;
            case 'logged_in':
                $dates = Disciple_Tools_Network_Queries::counted_by_day('logged_in', 'user');
                break;
            case 'baptisms':
                $dates = Disciple_Tools_Network_Queries::baptisms_counted_by_day();
                break;
            default: // contacts
                $dates = Disciple_Tools_Network_Queries::counted_by_day('created', 'contacts');
                break;
        }

        foreach ($dates as $date) {
            $date['value'] = (int)$date['value'];
            $data1[$date['date']] = $date;
        }

        $day_list = self::get_day_list(60);
        foreach ($day_list as $day) {
            if (isset($data1[$day])) {
                $data2[] = [
                    'date' => $data1[$day]['date'],
                    'value' => $data1[$day]['value'],
                ];
            } else {
                $data2[] = [
                    'date' => $day,
                    'value' => 0,
                ];
            }
        }

        arsort($data2);

        foreach ($data2 as $d) {
            $data3[] = $d;
        }

        return $data3;
    }

    public static function counted_by_month($type = null)
    {
        $data1 = [];
        $data2 = [];
        $data3 = [];

        switch ($type) {
            case 'groups':
                $dates = Disciple_Tools_Network_Queries::counted_by_month('created', 'groups');
                break;
            case 'logged_in':
                $dates = Disciple_Tools_Network_Queries::counted_by_month('logged_in', 'user');
                break;
            case 'baptisms':
                $dates = Disciple_Tools_Network_Queries::baptisms_counted_by_month();
                break;
            default: // contacts
                $dates = Disciple_Tools_Network_Queries::counted_by_month('created', 'contacts');
                break;
        }

        foreach ($dates as $date) {
            $date['value'] = (int)$date['value'];
            $data1[$date['date']] = $date;
        }

        $list = self::get_month_list(25);
        foreach ($list as $month) {
            if (isset($data1[$month])) {
                $data2[] = [
                    'date' => $data1[$month]['date'] . '-01',
                    'value' => $data1[$month]['value'],
                ];
            } else {
                $data2[] = [
                    'date' => $month . '-01',
                    'value' => 0,
                ];
            }
        }

        arsort($data2);

        foreach ($data2 as $d) {
            $data3[] = $d;
        }

        return $data3;
    }

    public static function user_logins_last_thirty_days()
    {

        $active = Disciple_Tools_Network_Queries::user_logins_last_thirty_days();

        $total_users = count_users();

        $inactive = $total_users['total_users'] - $active;
        if ($inactive < 1) {
            $inactive = 0;
        }

        $data = [
            [
                'label' => 'Active',
                'value' => $active,
            ],
            [
                'label' => 'Inactive',
                'value' => $inactive,
            ]
        ];

        return $data;
    }

    /**
     * Gets an array of the last number of days.
     *
     * @param int $number_of_days
     *
     * @return array
     */
    public static function get_day_list($number_of_days = 60)
    {
        $d = [];
        for ($i = 0; $i < $number_of_days; $i++) {
            $d[] = gmdate("Y-m-d", strtotime('-' . $i . ' days'));
        }

        return $d;
    }

    /**
     * Gets an array of last 25 months.
     *
     * @note 25 months allows you to get 3 years to compare of this month.
     *
     * @param int $number_of_months
     *
     * @return array
     */
    public static function get_month_list($number_of_months = 25)
    {
        $d = [];
        for ($i = 0; $i < $number_of_months; $i++) {
            $d[] = gmdate("Y-m", strtotime('-' . $i . ' months'));
        }

        return $d;
    }

    /**
     * Gets an array of the current state of groups
     * [active] => Array
     * (
     * [pre_group] => 3
     * [group] => 0
     * [church] => 3
     * )
     * [inactive] => Array
     * (
     * [pre_group] => 0
     * [group] => 0
     * [church] => 0
     * )
     * [total_active] => 6
     * [all] => 6
     *
     * @return array
     */
    public static function groups_current_state()
    {
        $data = [
            'active' => [
                'pre_group' => 0,
                'group' => 0,
                'church' => 0,
            ],
            'inactive' => [
                'pre_group' => 0,
                'group' => 0,
                'church' => 0,
            ],
            'total_active' => 0, // all non-duplicate groups in the system active or inactive.
            'all' => 0,
        ];

        // Add types and status
        $types_and_status = Disciple_Tools_Network_Queries::groups_types_and_status();
        foreach ($types_and_status as $value) {
            $value['type'] = str_replace('-', '_', $value['type']);

            $data[$value['status']][$value['type']] = (int)$value['count'];

            if ('active' === $value['status']) {
                $data ['total_active'] = $data['total_active'] + (int)$value['count'];
            }
        }

        $data['all'] = Disciple_Tools_Network_Queries::all_groups();

        return $data;
    }

    public static function groups_by_type()
    {
        $data = [];

        $types_and_status = Disciple_Tools_Network_Queries::groups_types_and_status();

        $keyed = [];
        foreach ($types_and_status as $status) {
            if ('active' === $status['status']) {
                $keyed[$status['type']] = $status;
            }
        }

        if (isset($keyed['pre-group'])) {
            $data[] = [
                'name' => 'Pre-Group',
                'value' => $keyed['pre-group']['count'],
            ];
        } else {
            $data[] = [
                'name' => 'Pre-Group',
                'value' => 0,
            ];
        }

        if (isset($keyed['group'])) {
            $data[] = [
                'name' => 'Group',
                'value' => $keyed['group']['count'],
            ];
        } else {
            $data[] = [
                'name' => 'Group',
                'value' => 0,
            ];
        }

        if (isset($keyed['church'])) {
            $data[] = [
                'name' => 'Church',
                'value' => $keyed['church']['count'],
            ];
        } else {
            $data[] = [
                'name' => 'Church',
                'value' => 0,
            ];
        }

        return $data;
    }

    public static function group_health()
    {
        $data = [];
        $labels = [];
        $keyed_practicing = [];

        // Make key list
        $group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings();
        foreach ($group_fields["health_metrics"]["default"] as $key => $option) {
            $labels[$key] = $option["label"];
        }

        // get results
        $practicing = Disciple_Tools_Network_Queries::group_health();

        // build keyed practicing
        foreach ($practicing as $value) {
            $keyed_practicing[$value['category']] = $value['practicing'];
        }

        // get total number
        $total_groups = Disciple_Tools_Network_Queries::groups_churches_total(); // total groups and churches

        // add real numbers and prepare array
        foreach ($labels as $key => $label) {
            if (isset($keyed_practicing[$key])) {
                $not_practicing = (int)$total_groups - $keyed_practicing[$key];
                if ($not_practicing < 1) {
                    $not_practicing = 0;
                }
                $data[] = [
                    'category' => $label,
                    'not_practicing' => $not_practicing,
                    'practicing' => $keyed_practicing[$key],
                ];
            } else {
                $data[] = [
                    'category' => $label,
                    'not_practicing' => $total_groups,
                    'practicing' => 0,
                ];
            }
        }

        return $data;
    }

    public static function users_current_state()
    {
        $data = [
            'total_users' => 0,
            'roles' => [
                'responders' => 0,
                'dispatchers' => 0,
                'multipliers' => 0,
                'strategists' => 0,
                'admins' => 0,
            ],
        ];

        // Add types and status
        $users = count_users();

        $data['total_users'] = (int)$users['total_users'];

        foreach ($users['avail_roles'] as $role => $count) {
            if ($role === 'marketer') {
                $data['roles']['responders'] = $data['roles']['responders'] + $count;
            }
            if ($role === 'dispatcher') {
                $data['roles']['dispatchers'] = $data['roles']['dispatchers'] + $count;
            }
            if ($role === 'multiplier') {
                $data['roles']['multipliers'] = $data['roles']['multipliers'] + $count;
            }
            if ($role === 'administrator' || $role === 'dt_admin') {
                $data['roles']['admins'] = $data['roles']['admins'] + $count;
            }
            if ($role === 'strategist') {
                $data['roles']['strategists'] = $data['roles']['strategists'] + $count;
            }
        }

        return $data;
    }

    public static function follow_up_funnel()
    {
        $data = [];
        $labels = [];
        $keyed_result = [];

        $contact_fields = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();

        foreach ($contact_fields['seeker_path']['default'] as $key => $value) {
            $labels[$key] = $value['label'];
        }

        require_once(get_template_directory() . '/dt-metrics/contacts/overview.php');

        $contacts = new DT_Metrics_Contacts_Overview();

        $results = $contacts->query_project_contacts_progress();
        if (empty($results) || is_wp_error($results)) {
            $results = [];
        }

        foreach ($results as $result) {
            $keyed_result[$result['key']] = $result;
        }

        foreach ($labels as $key => $label) {
            if (isset($keyed_result[$key])) {
                $data[] = [
                    "name" => $label,
                    "value" => (int)$keyed_result[$key]['value']
                ];
            } else {
                $data[] = [
                    "name" => $label,
                    "value" => 0
                ];
            }
        }

        return $data;
    }

    public static function funnel()
    {
        return array_slice(self::follow_up_funnel(), 0, 5);
    }

    public static function ongoing_meetings()
    {
        $data = self::follow_up_funnel();
        if (isset($data[5])) {
            return (int)$data[5]['value'];
        }

        return 0; // returns 0 if fail
    }

    /**
     * Selects single value from query.
     *
     * @return int
     */
    public static function coaching()
    {
        $data = self::follow_up_funnel();
        if (isset($data[6])) {
            return (int)$data[6]['value'];
        }

        return 0; // returns 0 if fail
    }

    public static function generations($type = null)
    {

        $data = [];

        switch ($type) {
            case 'groups':
                $generation = Disciple_Tools_Counter::critical_path('all_group_generations', 0, PHP_INT_MAX);
                $item = 'group';
                break;
            case 'baptisms':
                $baptisms = Disciple_Tools_Counter::critical_path('baptism_generations', 0, PHP_INT_MAX);
                if (empty($baptisms)) {
                    $generation = [];
                } else {
                    foreach ($baptisms as $key => $value) {
                        $generation[] = [
                            'generation' => $key,
                            'value' => $value,
                        ];
                    }
                }
                $item = 'value';
                break;
            default: // returns churches
                $generation = Disciple_Tools_Counter::critical_path('all_group_generations', 0, PHP_INT_MAX);
                $item = 'church';
                break;
        }

        if (empty($generation)) {
            return [
                [
                    'label' => 'Gen 1',
                    'value' => 0,
                ]
            ];
        }

        $end = false;
        foreach ($generation as $gen) {
            if ($end) { // this makes sure the last generation is zero but no more.
                break;
            }

            $data[] = [
                'label' => 'Gen ' . $gen['generation'],
                'value' => $gen[$item]
            ];

            if ($gen[$item] === 0) {
                $end = true;
            }
        }

        return $data;
    }

    public static function location_data_types($preset = false)
    {
        if ($preset) {
            return [
                'contacts' => 0,
                'groups' => 0,
                'churches' => 0,
                'users' => 0,
            ];
        } else {
            return [
                'contacts',
                'groups',
                'churches',
                'users',
            ];
        }
    }

    public static function get_locations_list($countries_only = false)
    {

        $data = [];

        if ($countries_only) {
            $results = Disciple_Tools_Mapping_Queries::get_location_grid_totals_for_countries();
        } else {
            $results = Disciple_Tools_Mapping_Queries::get_location_grid_totals();
        }

        if (!empty($results)) {
            foreach ($results as $item) {
                // skip custom location_grid. Their totals are represented in the standard parents.
                if ($item['grid_id'] > 1000000000) {
                    continue;
                }
                // set array, if not set
                if (!isset($data[$item['grid_id']])) {
                    $data[$item['grid_id']] = self::location_data_types(true);
                }
                // increment existing item type or add new
                if (isset($data[$item['grid_id']][$item['type']])) {
                    $data[$item['grid_id']][$item['type']] = (int)$data[$item['grid_id']][$item['type']] + (int)$item['count'];
                } else {
                    $data[$item['grid_id']][$item['type']] = (int)$item['count'];
                }
            }
        }

        return $data;
    }

    public static function get_locations_current_state()
    {
        $data = [
            'active_admin0' => 0,
            'active_admin0_grid_ids' => [],
            'active_admin1' => 0,
            'active_admin1_grid_ids' => [],
            'active_admin2' => 0,
            'active_admin2_grid_ids' => [],
        ];

        $results = Disciple_Tools_Network_Queries::locations_current_state();
        if (!empty($results['active_countries'])) {
            $data['active_countries'] = (int)$results['active_countries'];
        }
        if (!empty($results['active_countries'])) {
            $data['active_admin1'] = (int)$results['active_admin1'];
        }
        if (!empty($results['active_countries'])) {
            $data['active_admin2'] = (int)$results['active_admin2'];
        }

        $active_admin0_grid_ids = Disciple_Tools_Mapping_Queries::active_admin0_grid_ids();
        if (!empty($active_admin0_grid_ids)) {
            foreach ($active_admin0_grid_ids as $grid_id) {
                $data['active_admin0_grid_ids'][] = (int)$grid_id;
            }
        }
        $active_admin1_grid_ids = Disciple_Tools_Mapping_Queries::active_admin1_grid_ids();
        if (!empty($active_admin1_grid_ids)) {
            foreach ($active_admin1_grid_ids as $grid_id) {
                $data['active_admin1_grid_ids'][] = (int)$grid_id;
            }
        }
        $active_admin2_grid_ids = Disciple_Tools_Mapping_Queries::active_admin2_grid_ids();
        if (!empty($active_admin2_grid_ids)) {
            foreach ($active_admin2_grid_ids as $grid_id) {
                $data['active_admin2_grid_ids'][] = (int)$grid_id;
            }
        }

        return $data;
    }
}

