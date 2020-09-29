<?php

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class DT_Network_Dashboard_Snapshot_Locations {

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_filter( 'dt_network_dashboard_snapshot_report', [ $this, 'report' ], 10, 1 );
    }

    public function report( $report_data ) {

        $report_data['locations'] = [
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
        ];

        return $report_data;
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

        $results = DT_Network_Dashboard_Snapshot_Queries::locations_current_state();
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
DT_Network_Dashboard_Snapshot_Locations::instance();