<?php

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class DT_Network_Dashboard_Snapshot_Queries {


    /**
     * Gets an array of the last number of days.
     *
     * @param int $number_of_days
     *
     * @return array
     */
    public static function get_day_list( $number_of_days = 60) {
        $d = [];
        for ($i = 0; $i < $number_of_days; $i++) {
            $d[] = gmdate( "Y-m-d", strtotime( '-' . $i . ' days' ) );
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
    public static function get_month_list( $number_of_months = 25) {
        $d = [];
        for ($i = 0; $i < $number_of_months; $i++) {
            $d[] = gmdate( "Y-m", strtotime( '-' . $i . ' months' ) );
        }

        return $d;
    }

    public static function counted_by_day( $type = null) {
        $data1 = [];
        $data2 = [];
        $data3 = [];

        switch ($type) {
            case 'groups':
                $dates = self::query_counted_by_day( 'created', 'groups' );
                break;
            case 'logged_in':
                $dates = self::query_counted_by_day( 'logged_in', 'user' );
                break;
            case 'baptisms':
                $dates = self::baptisms_counted_by_day();
                break;
            default: // contacts
                $dates = self::query_counted_by_day( 'created', 'contacts' );
                break;
        }

        foreach ($dates as $date) {
            $date['value'] = (int) $date['value'];
            $data1[$date['date']] = $date;
        }

        $day_list = self::get_day_list( 60 );
        foreach ($day_list as $day) {
            if (isset( $data1[$day] )) {
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

        arsort( $data2 );

        foreach ($data2 as $d) {
            $data3[] = $d;
        }

        return $data3;
    }

    public static function counted_by_month( $type = null) {
        $data1 = [];
        $data2 = [];
        $data3 = [];

        switch ($type) {
            case 'groups':
                $dates = self::query_counted_by_month( 'created', 'groups' );
                break;
            case 'logged_in':
                $dates = self::query_counted_by_month( 'logged_in', 'user' );
                break;
            case 'baptisms':
                $dates = self::baptisms_counted_by_month();
                break;
            default: // contacts
                $dates = self::query_counted_by_month( 'created', 'contacts' );
                break;
        }

        foreach ($dates as $date) {
            $date['value'] = (int) $date['value'];
            $data1[$date['date']] = $date;
        }

        $list = self::get_month_list( 25 );
        foreach ($list as $month) {
            if (isset( $data1[$month] )) {
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

        arsort( $data2 );

        foreach ($data2 as $d) {
            $data3[] = $d;
        }

        return $data3;
    }

    public static function generations( $type = null) {

        $data = [];

        switch ($type) {
            case 'groups':
                $generation = Disciple_Tools_Counter::critical_path( 'all_group_generations', 0, PHP_INT_MAX );
                $item = 'group';
                break;
            case 'baptisms':
                $baptisms = Disciple_Tools_Counter::critical_path( 'baptism_generations', 0, PHP_INT_MAX );
                if (empty( $baptisms )) {
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
                $generation = Disciple_Tools_Counter::critical_path( 'all_group_generations', 0, PHP_INT_MAX );
                $item = 'church';
                break;
        }

        if (empty( $generation )) {
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

    public static function contacts_current_state() {
        $data = [
            'all_contacts' => 0,
            'critical_path' => [],
        ];

        // Add critical path
        if ( !class_exists( 'DT_Metrics_Contacts_Overview' )) {
            require_once( get_template_directory() . '/dt-metrics/contacts/overview.php' );
        }
        $contacts = new DT_Metrics_Contacts_Overview();

        $critical_path = $contacts->query_project_contacts_progress();
        foreach ($critical_path as $path) {
            $data['critical_path'][$path['key']] = $path;
        }

        // Add
        $data['status'] = self::get_contacts_status();

        $data['all_contacts'] = self::all_contacts();

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
        $contact_fields = DT_Posts::get_post_field_settings( "contacts" );
        $status_defaults = $contact_fields['overall_status']['default'];
        $current_state = self::query_contacts_current_state();
        foreach ($status_defaults as $key => $status) {
            $data[$key] = 0;
            foreach ($current_state as $state) {
                if ($state['status'] === $key) {
                    $data[$key] = (int) $state['count'];
                }
            }
        }

        return $data;
    }




    public static function follow_up_funnel() {
        $data = [];
        $labels = [];
        $keyed_result = [];

        $contact_fields = DT_Posts::get_post_field_settings( "contacts" );

        foreach ($contact_fields['seeker_path']['default'] as $key => $value) {
            $labels[$key] = $value['label'];
        }

        require_once( get_template_directory() . '/dt-metrics/contacts/overview.php' );

        $contacts = new DT_Metrics_Contacts_Overview();

        $results = $contacts->query_project_contacts_progress();
        if (empty( $results ) || is_wp_error( $results )) {
            $results = [];
        }

        foreach ($results as $result) {
            $keyed_result[$result['key']] = $result;
        }

        foreach ($labels as $key => $label) {
            if (isset( $keyed_result[$key] )) {
                $data[] = [
                    "name" => $label,
                    "value" => (int) $keyed_result[$key]['value']
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

    public static function funnel() {
        return array_slice( self::follow_up_funnel(), 0, 5 );
    }

    public static function ongoing_meetings() {
        $data = self::follow_up_funnel();
        if (isset( $data[5] )) {
            return (int) $data[5]['value'];
        }

        return 0; // returns 0 if fail
    }

    /**
     * Selects single value from query.
     *
     * @return int
     */
    public static function coaching() {
        $data = self::follow_up_funnel();
        if (isset( $data[6] )) {
            return (int) $data[6]['value'];
        }

        return 0; // returns 0 if fail
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
    public static function groups_current_state() {
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
        $types_and_status = self::groups_types_and_status();
        foreach ($types_and_status as $value) {
            $value['type'] = str_replace( '-', '_', $value['type'] );

            $data[$value['status']][$value['type']] = (int) $value['count'];

            if ('active' === $value['status']) {
                $data ['total_active'] = $data['total_active'] + (int) $value['count'];
            }
        }

        $data['all'] = self::all_groups();

        return $data;
    }

    public static function groups_by_type() {
        $data = [];

        $types_and_status = self::groups_types_and_status();

        $keyed = [];
        foreach ($types_and_status as $status) {
            if ('active' === $status['status']) {
                $keyed[$status['type']] = $status;
            }
        }

        if (isset( $keyed['pre-group'] )) {
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

        if (isset( $keyed['group'] )) {
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

        if (isset( $keyed['church'] )) {
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

    public static function group_health() {
        $data = [];
        $labels = [];
        $keyed_practicing = [];

        // Make key list
        $group_fields = DT_Posts::get_post_field_settings( "groups" );
        foreach ($group_fields["health_metrics"]["default"] as $key => $option) {
            $labels[$key] = $option["label"];
        }

        // get results
        $practicing = self::query_group_health();

        // build keyed practicing
        foreach ($practicing as $value) {
            $keyed_practicing[$value['category']] = $value['practicing'];
        }

        // get total number
        $total_groups = self::groups_churches_total(); // total groups and churches

        // add real numbers and prepare array
        foreach ($labels as $key => $label) {
            if (isset( $keyed_practicing[$key] )) {
                $not_practicing = (int) $total_groups - $keyed_practicing[$key];
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

    public static function dashboards_to_report_to() : array {
        global $wpdb;

        $results = $wpdb->get_results("
            SELECT
              post_title as name,
              ID as id
            FROM $wpdb->posts as p
            JOIN $wpdb->postmeta as pm
              ON p.ID=pm.post_id
                AND pm.meta_key = 'type'
            LEFT JOIN $wpdb->postmeta as pm2
              ON p.ID=pm2.post_id
                AND pm2.meta_key = 'non_wp'
              WHERE p.post_type = 'site_link_system'
              AND p.post_status = 'publish'
              AND ( pm.meta_value = 'network_dashboard_both'
              OR pm.meta_value = 'network_dashboard_sending' )
                AND pm2.meta_value != '1'
        ",
        ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function dashboards_to_report_activity_to() : array {
        global $wpdb;

        $results = $wpdb->get_results("
            SELECT
              p.post_title as name,
              p.ID as id,
              pm3.meta_value as last_activity_id
            FROM $wpdb->posts as p
            JOIN $wpdb->postmeta as pm
              ON p.ID=pm.post_id
                AND pm.meta_key = 'type'
            LEFT JOIN $wpdb->postmeta as pm2
              ON p.ID=pm2.post_id
                AND pm2.meta_key = 'non_wp'
            LEFT JOIN $wpdb->postmeta as pm3
              ON p.ID=pm3.post_id
                AND pm3.meta_key = 'last_activity_id'
              WHERE p.post_type = 'site_link_system'
              AND p.post_status = 'publish'
              AND ( pm.meta_value = 'network_dashboard_both'
              OR pm.meta_value = 'network_dashboard_sending' )
                AND pm2.meta_value != '1'
        ",
        ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function query_contacts_current_state() : array  {
        global $wpdb;
        /**
         * Returns status and count of contacts according to the overall status
         * return array
         */
        $results = $wpdb->get_results("
        SELECT
            b.meta_value as status,
            count(a.ID) as count
        FROM $wpdb->posts as a
        JOIN $wpdb->postmeta as b
            ON a.ID = b.post_id
            AND b.meta_key = 'overall_status'
        WHERE a.post_status = 'publish'
        AND a.post_type = 'contacts'
        AND a.ID NOT IN (
            SELECT post_id FROM $wpdb->postmeta
            WHERE meta_key = 'type' AND meta_value = 'user'
            GROUP BY post_id
        )
        GROUP BY b.meta_value
        ", ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function all_contacts() : int {
        global $wpdb;
        /**
         * Returns single digit count of all contacts in the system.
         * return int
         */
        $results = $wpdb->get_var("
        SELECT
            count(a.ID) as count
        FROM $wpdb->posts as a
        WHERE a.post_status = 'publish'
        AND a.post_type = 'contacts'
        AND a.ID NOT IN (
            SELECT post_id FROM $wpdb->postmeta
            WHERE meta_key = 'type' AND meta_value = 'user'
            GROUP BY post_id
        )
    ");
        if ( empty( $results ) ) {
            $results = 0;
        }
        return $results;
    }

    public static function all_groups() : int {
        global $wpdb;
        /**
         * Returns single digit count of all pre-groups, groups, and churches in the system.
         * return int
         */
        $results = $wpdb->get_var("
                SELECT
                  count(a.ID) as count
                FROM $wpdb->posts as a
                WHERE a.post_status = 'publish'
                      AND a.post_type = 'groups'
            ");
        if ( empty( $results ) ) {
            $results = 0;
        }
        return $results;
    }

    public static function query_group_health() : array {
        global $wpdb;
        /**
         * Returns health numbers for groups and churches but not pre-groups
         *
         *  category            practicing
         *  church_baptism      4
         *  church_bible        5
         *  church_commitment   1
         *  church_communion    2
         *  church_fellowship   2
         *  church_giving       1
         *  church_leaders      1
         *  church_praise       1
         *  church_prayer       4
         *  church_sharing      2
         *
         */
        $results = $wpdb->get_results( "
                SELECT
                  d.meta_value           as category,
                  count(distinct (a.ID)) as practicing
                FROM $wpdb->posts as a
                  JOIN $wpdb->postmeta as c
                    ON a.ID = c.post_id
                       AND c.meta_key = 'group_status'
                       AND c.meta_value = 'active'
                  JOIN $wpdb->postmeta as d
                    ON a.ID = d.post_id
                        AND d.meta_key = 'health_metrics'
                  JOIN $wpdb->postmeta as e
                    ON a.ID = e.post_id
                       AND e.meta_key = 'group_type'
                        AND ( e.meta_value = 'group' OR e.meta_value = 'church')
                WHERE a.post_status = 'publish'
                      AND a.post_type = 'groups'
                GROUP BY d.meta_value;
            ", ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function user_logins_last_thirty_days() : int {
        global $wpdb;

        /**
         * Returns count for number of unique users signed in within the last month.
         */
        $results = $wpdb->get_var("
                SELECT
                  COUNT( DISTINCT object_id ) as value
                FROM $wpdb->dt_activity_log
                WHERE
                  object_type = 'user'
                  AND action = 'logged_in'
                  AND hist_time >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 1 MONTH );
            ");

        if ( empty( $results ) ) {
            $results = 0;
        }

        return $results;
    }

    public static function query_counted_by_month( $action, $object_type ) : array {
        global $wpdb;

        /**
         * Can collect various events just by specifying object type and action.
         *
         * Returns list grouped by timestamp
         *
         *   2019-01        9
         *   2018-12        11
         *   2018-11        9
         *   2018-10        39
         *
         */
        $results = $wpdb->get_results( $wpdb->prepare( "
                SELECT
                  from_unixtime( hist_time , '%%Y-%%m') as date,
                  count( DISTINCT object_id) as value
                FROM $wpdb->dt_activity_log
                WHERE object_type = %s
                  AND action = %s
                  AND hist_time != ''
                  AND hist_time REGEXP ('^[0-9][0-9][0-9][0-9][0-9][0-9][0-9]')
                GROUP BY date
                ORDER BY date DESC
                LIMIT 25;
            ",
            $object_type,
            $action
        ), ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function query_counted_by_day( $action, $object_type ) : array {
        global $wpdb;

        /**
         * Can collect various events just by specifying object type and action.
         *
         * Returns list grouped by timestamp
         *
         *   2019-01        9
         *   2018-12        11
         *   2018-11        9
         *   2018-10        39
         *
         */
        $results = $wpdb->get_results( $wpdb->prepare( "
                SELECT
                  from_unixtime( hist_time , '%%Y-%%m-%%d') as date,
                  count( DISTINCT object_id) as value
                FROM $wpdb->dt_activity_log
                WHERE object_type = %s
                      AND action = %s
                      AND hist_time != ''
                      AND hist_time REGEXP ('^[0-9][0-9][0-9][0-9][0-9][0-9][0-9]')
                GROUP BY date
                ORDER BY date DESC
                LIMIT 60;
            ",
            $object_type,
            $action
        ), ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function total_baptisms() : int {
        global $wpdb;

        /**
         * Returns the count for baptisms in the system
         *
         *   2018-04-30     9
         *   2018-04-29     11
         *   2018-04-28     9
         *   2018-04-27     39
         */
        $results = $wpdb->get_var( "
               SELECT
                  count( DISTINCT object_id) as value
                FROM $wpdb->dt_activity_log
                WHERE
                    object_type = 'contacts'
                    AND object_subtype = 'baptism_date'
                    AND meta_value != ''
                    AND meta_value REGEXP ('^[0-9][0-9][0-9][0-9][0-9][0-9][0-9]')
            " );
        if ( empty( $results ) ) {
            $results = 0;
        } else {
            $results = (int) $results;
        }

        return $results;
    }

    public static function baptisms_counted_by_month() : array {
        global $wpdb;

        /**
         * Can collect various events just by specifying object type and action.
         *
         * Returns list grouped by timestamp
         *
         *   2019-01        9
         *   2018-12        11
         *   2018-11        9
         *   2018-10        39
         *
         */
        $results = $wpdb->get_results( "
                SELECT
                  from_unixtime( meta_value , '%Y-%m') as date,
                  count( DISTINCT object_id) as value
                FROM $wpdb->dt_activity_log
                WHERE object_type = 'contacts'
                  AND object_subtype = 'baptism_date'
                  AND meta_value != ''
                  AND meta_value REGEXP ('^[0-9][0-9][0-9][0-9][0-9][0-9][0-9]')
                GROUP BY meta_value
                ORDER BY date DESC
                LIMIT 25;
            ", ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function baptisms_counted_by_day() : array {
        global $wpdb;

        /**
         * Returns list grouped by timestamp
         *
         *   2018-04-30     9
         *   2018-04-29     11
         *   2018-04-28     9
         *   2018-04-27     39
         */
        $results = $wpdb->get_results( "
           SELECT
              from_unixtime( meta_value , '%Y-%m-%d') as date,
              count( DISTINCT object_id) as value
            FROM $wpdb->dt_activity_log
            WHERE object_type = 'contacts'
            AND object_subtype = 'baptism_date'
            AND meta_value != ''
            AND meta_value REGEXP ('^[0-9][0-9][0-9][0-9][0-9][0-9][0-9]')
            GROUP BY meta_value
            ORDER BY date DESC
            LIMIT 60;
        ", ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function groups_types_and_status() : array {
        global $wpdb;

        /**
         * Returns the different types of groups and their count
         *
         *  pre-group   active      5
        pre-group   inactive    7
        group       active      2
        group       inactive    1
        church      active      9
        church      inactive    2
         */
        $results = $wpdb->get_results( "
                SELECT
                  c.meta_value as type,
                  b.meta_value as status,
                  count(a.ID)  as count
                FROM $wpdb->posts as a
                  JOIN $wpdb->postmeta as b
                    ON a.ID = b.post_id
                       AND b.meta_key = 'group_status'
                  JOIN $wpdb->postmeta as c
                    ON a.ID = c.post_id
                       AND c.meta_key = 'group_type'
                WHERE a.post_status = 'publish'
                      AND a.post_type = 'groups'
                GROUP BY type, status
                ORDER BY type ASC
            ", ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function groups_churches_total() : int {
        global $wpdb;

        /**
         * Returns single digit count of all groups and churches in the system.
         * return int
         */
        $results = $wpdb->get_var("
                SELECT
                  count(a.ID) as count
                FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as c
                    ON a.ID = c.post_id
                       AND c.meta_key = 'group_status'
                       AND c.meta_value = 'active'
                JOIN $wpdb->postmeta as b
                  ON a.ID=b.post_id
                  AND b.meta_key = 'group_type'
                  AND ( b.meta_value = 'group' OR b.meta_value = 'church' )
                WHERE a.post_status = 'publish'
                  AND a.post_type = 'groups'
            ");

        if ( empty( $results ) ) {
            $results = 0;
        }

        return $results;
    }

    public static function locations_current_state() : array {

        $results['active_countries'] = (int) count( Disciple_Tools_Mapping_Queries::active_admin0_grid_ids() );
        $results['active_admin1'] = (int) count( Disciple_Tools_Mapping_Queries::active_admin1_grid_ids() );
        $results['active_admin2'] = (int) count( Disciple_Tools_Mapping_Queries::active_admin2_grid_ids() );

        return $results;
    }
}

