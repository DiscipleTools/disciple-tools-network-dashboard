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
        add_filter( 'dt_network_dashboard_snapshot_report', array( $this, 'report' ), 10, 1 );
    }

    public function report( $report_data ) {

        $report_data['locations'] = array(
            'data_types' => self::location_data_types(),
            'countries' => self::get_locations_list( true ),
            'current_state' => self::get_locations_current_state(),
            'list' => self::get_locations_list(),
            'contacts' => array(
                'all' => Disciple_Tools_Mapping_Queries::query_contacts_location_grid_totals(),
                'active' => Disciple_Tools_Mapping_Queries::query_contacts_location_grid_totals( 'active' ),
                'paused' => Disciple_Tools_Mapping_Queries::query_contacts_location_grid_totals( 'paused' ),
                'closed' => Disciple_Tools_Mapping_Queries::query_contacts_location_grid_totals( 'closed' ),
            ),
            'groups' => array(
                'all' => Disciple_Tools_Mapping_Queries::query_groups_location_grid_totals(),
                'active' => Disciple_Tools_Mapping_Queries::query_groups_location_grid_totals( 'active' ),
                'inactive' => Disciple_Tools_Mapping_Queries::query_groups_location_grid_totals( 'inactive' ),
            ),
            'churches' => array(
                'all' => Disciple_Tools_Mapping_Queries::query_church_location_grid_totals(),
                'active' => Disciple_Tools_Mapping_Queries::query_church_location_grid_totals( 'active' ),
                'inactive' => Disciple_Tools_Mapping_Queries::query_church_location_grid_totals( 'inactive' ),
            ),
            'users' => array(
                'all' => Disciple_Tools_Mapping_Queries::query_user_location_grid_totals(),
                'active' => Disciple_Tools_Mapping_Queries::query_user_location_grid_totals( 'active' ),
                'inactive' => Disciple_Tools_Mapping_Queries::query_user_location_grid_totals( 'inactive' ),
            )
        );

        return $report_data;
    }


    public static function location_data_types( $preset = false ) {
        if ( $preset ) {
            return array(
                'contacts' => 0,
                'groups' => 0,
                'churches' => 0,
                'users' => 0,
            );
        } else {
            return array(
                'contacts',
                'groups',
                'churches',
                'users',
            );
        }
    }

    public static function get_locations_list( $countries_only = false ) {

        $data = array();

        if ( $countries_only ) {
            $results = Disciple_Tools_Mapping_Queries::get_location_grid_totals_for_countries();
        } else {
            $results = Disciple_Tools_Mapping_Queries::get_location_grid_totals();
        }

        if ( !empty( $results ) ) {
            foreach ( $results as $item ) {
                // skip custom location_grid. Their totals are represented in the standard parents.
                if ( $item['grid_id'] > 1000000000 ) {
                    continue;
                }
                // set array, if not set
                if ( !isset( $data[$item['grid_id']] ) ) {
                    $data[$item['grid_id']] = self::location_data_types( true );
                }
                // increment existing item type or add new
                if ( isset( $data[$item['grid_id']][$item['type']] ) ) {
                    $data[$item['grid_id']][$item['type']] = (int) $data[$item['grid_id']][$item['type']] + (int) $item['count'];
                } else {
                    $data[$item['grid_id']][$item['type']] = (int) $item['count'];
                }
            }
        }

        return $data;
    }

    public static function get_locations_current_state() {
        $data = array(
            'active_admin0' => 0,
            'active_admin0_grid_ids' => array(),
            'active_admin1' => 0,
            'active_admin1_grid_ids' => array(),
            'active_admin2' => 0,
            'active_admin2_grid_ids' => array(),
        );

        $results = DT_Network_Dashboard_Snapshot_Queries::locations_current_state();
        if ( !empty( $results['active_countries'] ) ) {
            $data['active_countries'] = (int) $results['active_countries'];
        }
        if ( !empty( $results['active_countries'] ) ) {
            $data['active_admin1'] = (int) $results['active_admin1'];
        }
        if ( !empty( $results['active_countries'] ) ) {
            $data['active_admin2'] = (int) $results['active_admin2'];
        }

        $active_admin0_grid_ids = Disciple_Tools_Mapping_Queries::active_admin0_grid_ids();
        if ( !empty( $active_admin0_grid_ids ) ) {
            foreach ( $active_admin0_grid_ids as $grid_id ) {
                $data['active_admin0_grid_ids'][] = (int) $grid_id;
            }
        }
        $active_admin1_grid_ids = Disciple_Tools_Mapping_Queries::active_admin1_grid_ids();
        if ( !empty( $active_admin1_grid_ids ) ) {
            foreach ( $active_admin1_grid_ids as $grid_id ) {
                $data['active_admin1_grid_ids'][] = (int) $grid_id;
            }
        }
        $active_admin2_grid_ids = Disciple_Tools_Mapping_Queries::active_admin2_grid_ids();
        if ( !empty( $active_admin2_grid_ids ) ) {
            foreach ( $active_admin2_grid_ids as $grid_id ) {
                $data['active_admin2_grid_ids'][] = (int) $grid_id;
            }
        }

        return $data;
    }

    public static function query_contacts_location_grid_totals( $status = null ) {

        global $wpdb;

        if ( $status ) {
            $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
            FROM (
              SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'contacts'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
                AND pm.post_id NOT IN (
                  SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p
                  WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '')
                  OR ( p.meta_key = 'overall_status' AND p.meta_value = 'closed')
                )
            ) as t0
            UNION
            SELECT t1.admin1_grid_id as grid_id, count(t1.admin1_grid_id) as count
            FROM (
             SELECT lg.admin1_grid_id FROM $wpdb->dt_location_grid_meta as lgm LEFT JOIN $wpdb->dt_location_grid as lg  ON lg.grid_id=lgm.grid_id
             JOIN $wpdb->postmeta as pm ON pm.post_id=lgm.post_id AND meta_key = 'overall_status' AND meta_value = %s
             WHERE lgm.post_type = 'contacts'
             AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') )
            ) as t1
            GROUP BY t1.admin1_grid_id
            UNION
            SELECT t2.admin2_grid_id as grid_id, count(t2.admin2_grid_id) as count
            FROM (
             SELECT lg.admin2_grid_id FROM $wpdb->dt_location_grid_meta as lgm LEFT JOIN $wpdb->dt_location_grid as lg  ON lg.grid_id=lgm.grid_id
             JOIN $wpdb->postmeta as pm ON pm.post_id=lgm.post_id AND meta_key = 'overall_status' AND meta_value = %s
             WHERE lgm.post_type = 'contacts'
             AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') )
            ) as t2
            GROUP BY t2.admin2_grid_id
            UNION
            SELECT t3.admin3_grid_id as grid_id, count(t3.admin3_grid_id) as count
            FROM (
             SELECT lg.admin3_grid_id FROM $wpdb->dt_location_grid_meta as lgm LEFT JOIN $wpdb->dt_location_grid as lg  ON lg.grid_id=lgm.grid_id
             JOIN $wpdb->postmeta as pm ON pm.post_id=lgm.post_id AND meta_key = 'overall_status' AND meta_value = %s
             WHERE lgm.post_type = 'contacts'
             AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') )
            ) as t3
            GROUP BY t3.admin3_grid_id
            UNION
            SELECT t4.admin4_grid_id as grid_id, count(t4.admin4_grid_id) as count
            FROM (
             SELECT lg.admin4_grid_id FROM $wpdb->dt_location_grid_meta as lgm LEFT JOIN $wpdb->dt_location_grid as lg  ON lg.grid_id=lgm.grid_id
             JOIN $wpdb->postmeta as pm ON pm.post_id=lgm.post_id AND meta_key = 'overall_status' AND meta_value = %s
             WHERE lgm.post_type = 'contacts'
             AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') )
            ) as t4
            GROUP BY t4.admin4_grid_id
            UNION
            SELECT t5.admin5_grid_id as grid_id, count(t5.admin5_grid_id) as count
            FROM (
             SELECT lg.admin5_grid_id FROM $wpdb->dt_location_grid_meta as lgm LEFT JOIN $wpdb->dt_location_grid as lg  ON lg.grid_id=lgm.grid_id
             JOIN $wpdb->postmeta as pm ON pm.post_id=lgm.post_id AND meta_key = 'overall_status' AND meta_value = %s
             WHERE lgm.post_type = 'contacts'
             AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') )
            ) as t5
            GROUP BY t5.admin5_grid_id;
            ", $status, $status, $status, $status, $status ), ARRAY_A );

        } else {

            $results = $wpdb->get_results( "
            SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
            FROM (
             SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'contacts'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
                AND pm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') OR ( p.meta_key = 'overall_status' AND p.meta_value = 'closed'))
            ) as t0
            GROUP BY t0.admin0_grid_id
            UNION
            SELECT t1.admin1_grid_id as grid_id, count(t1.admin1_grid_id) as count
            FROM (
             SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'contacts'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
                AND pm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') OR ( p.meta_key = 'overall_status' AND p.meta_value = 'closed'))
            ) as t1
            GROUP BY t1.admin1_grid_id
            UNION
            SELECT t2.admin2_grid_id as grid_id, count(t2.admin2_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'contacts'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
                AND pm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') OR ( p.meta_key = 'overall_status' AND p.meta_value = 'closed'))
            ) as t2
            GROUP BY t2.admin2_grid_id
            UNION
            SELECT t3.admin3_grid_id as grid_id, count(t3.admin3_grid_id) as count
            FROM (
             SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'contacts'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
                AND pm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') OR ( p.meta_key = 'overall_status' AND p.meta_value = 'closed'))
            ) as t3
            GROUP BY t3.admin3_grid_id
            UNION
            SELECT t4.admin4_grid_id as grid_id, count(t4.admin4_grid_id) as count
            FROM (
             SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'contacts'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
                AND pm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') OR ( p.meta_key = 'overall_status' AND p.meta_value = 'closed'))
            ) as t4
            GROUP BY t4.admin4_grid_id
            UNION
            SELECT t5.admin5_grid_id as grid_id, count(t5.admin5_grid_id) as count
            FROM (
             SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'contacts'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
                AND pm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') OR ( p.meta_key = 'overall_status' AND p.meta_value = 'closed'))
            ) as t5
            GROUP BY t5.admin5_grid_id;
            ", ARRAY_A );
        }

        $list = [];
        if ( is_array( $results ) ) {
            foreach ( $results as $result ) {
                $list[$result['grid_id']] = $result;
            }
        }

        return $list;
    }

    public static function query_groups_location_grid_totals( $status = null ) {

        global $wpdb;

        if ( $status ) {
            $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
                AND pm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE p.meta_key = 'group_status' AND p.meta_value = %s )
            ) as t0
            GROUP BY t0.admin0_grid_id
            UNION
            SELECT t1.admin1_grid_id as grid_id, count(t1.admin1_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
                AND pm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE p.meta_key = 'group_status' AND p.meta_value = %s )
            ) as t1
            GROUP BY t1.admin1_grid_id
            UNION
            SELECT t2.admin2_grid_id as grid_id, count(t2.admin2_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
                AND pm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE p.meta_key = 'group_status' AND p.meta_value = %s )
            ) as t2
            GROUP BY t2.admin2_grid_id
            UNION
            SELECT t3.admin3_grid_id as grid_id, count(t3.admin3_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
                AND pm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE p.meta_key = 'group_status' AND p.meta_value = %s )
            ) as t3
            GROUP BY t3.admin3_grid_id
            UNION
            SELECT t4.admin4_grid_id as grid_id, count(t4.admin4_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
                AND pm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE p.meta_key = 'group_status' AND p.meta_value = %s )
            ) as t4
            GROUP BY t4.admin4_grid_id
            UNION
            SELECT t5.admin5_grid_id as grid_id, count(t5.admin5_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
                AND pm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE p.meta_key = 'group_status' AND p.meta_value = %s )
            ) as t5
            GROUP BY t5.admin5_grid_id;
            ", $status, $status, $status, $status, $status, $status
            ), ARRAY_A );

        } else {

            $results = $wpdb->get_results( "
            SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t0
            GROUP BY t0.admin0_grid_id
            UNION
            SELECT t1.admin1_grid_id as grid_id, count(t1.admin1_grid_id) as count
            FROM (
            SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t1
            GROUP BY t1.admin1_grid_id
            UNION
            SELECT t2.admin2_grid_id as grid_id, count(t2.admin2_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t2
            GROUP BY t2.admin2_grid_id
            UNION
            SELECT t3.admin3_grid_id as grid_id, count(t3.admin3_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t3
            GROUP BY t3.admin3_grid_id
            UNION
            SELECT t4.admin4_grid_id as grid_id, count(t4.admin4_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t4
            GROUP BY t4.admin4_grid_id
            UNION
            SELECT t5.admin5_grid_id as grid_id, count(t5.admin5_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t5
            GROUP BY t5.admin5_grid_id;
            ", ARRAY_A );
        }

        $list = [];
        if ( is_array( $results ) ) {
            foreach ( $results as $result ) {
                $list[$result['grid_id']] = $result;
            }
        }

        return $list;
    }


    public static function query_church_location_grid_totals( $status = null ) {

        global $wpdb;

        if ( $status ) {
            $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                JOIN $wpdb->postmeta as pm2 ON pm2.post_id=pm.post_id AND pm2.meta_key = 'group_type' AND pm2.meta_value = 'church'
                JOIN $wpdb->postmeta as pm3 ON pm3.post_id=pm.post_id AND pm3.meta_key = 'group_status' AND pm3.meta_value = %s
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t0
            GROUP BY t0.admin0_grid_id
            UNION
            SELECT t1.admin1_grid_id as grid_id, count(t1.admin1_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                JOIN $wpdb->postmeta as pm2 ON pm2.post_id=pm.post_id AND pm2.meta_key = 'group_type' AND pm2.meta_value = 'church'
                JOIN $wpdb->postmeta as pm3 ON pm3.post_id=pm.post_id AND pm3.meta_key = 'group_status' AND pm3.meta_value = %s
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t1
            GROUP BY t1.admin1_grid_id
            UNION
            SELECT t2.admin2_grid_id as grid_id, count(t2.admin2_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                JOIN $wpdb->postmeta as pm2 ON pm2.post_id=pm.post_id AND pm2.meta_key = 'group_type' AND pm2.meta_value = 'church'
                JOIN $wpdb->postmeta as pm3 ON pm3.post_id=pm.post_id AND pm3.meta_key = 'group_status' AND pm3.meta_value = %s
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t2
            GROUP BY t2.admin2_grid_id
            UNION
            SELECT t3.admin3_grid_id as grid_id, count(t3.admin3_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                JOIN $wpdb->postmeta as pm2 ON pm2.post_id=pm.post_id AND pm2.meta_key = 'group_type' AND pm2.meta_value = 'church'
                JOIN $wpdb->postmeta as pm3 ON pm3.post_id=pm.post_id AND pm3.meta_key = 'group_status' AND pm3.meta_value = %s
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t3
            GROUP BY t3.admin3_grid_id
            UNION
            SELECT t4.admin4_grid_id as grid_id, count(t4.admin4_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                JOIN $wpdb->postmeta as pm2 ON pm2.post_id=pm.post_id AND pm2.meta_key = 'group_type' AND pm2.meta_value = 'church'
                JOIN $wpdb->postmeta as pm3 ON pm3.post_id=pm.post_id AND pm3.meta_key = 'group_status' AND pm3.meta_value = %s
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t4
            GROUP BY t4.admin4_grid_id
            UNION
            SELECT t5.admin5_grid_id as grid_id, count(t5.admin5_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                JOIN $wpdb->postmeta as pm2 ON pm2.post_id=pm.post_id AND pm2.meta_key = 'group_type' AND pm2.meta_value = 'church'
                JOIN $wpdb->postmeta as pm3 ON pm3.post_id=pm.post_id AND pm3.meta_key = 'group_status' AND pm3.meta_value = %s
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t5
            GROUP BY t5.admin5_grid_id;
            ", $status, $status, $status, $status, $status, $status
            ), ARRAY_A );

        } else {

            $results = $wpdb->get_results( "
            SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
            FROM (
             SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                JOIN $wpdb->postmeta as pm2 ON pm2.post_id=pm.post_id AND pm2.meta_key = 'group_type' AND pm2.meta_value = 'church'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t0
            GROUP BY t0.admin0_grid_id
            UNION
            SELECT t1.admin1_grid_id as grid_id, count(t1.admin1_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                JOIN $wpdb->postmeta as pm2 ON pm2.post_id=pm.post_id AND pm2.meta_key = 'group_type' AND pm2.meta_value = 'church'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t1
            GROUP BY t1.admin1_grid_id
            UNION
            SELECT t2.admin2_grid_id as grid_id, count(t2.admin2_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                JOIN $wpdb->postmeta as pm2 ON pm2.post_id=pm.post_id AND pm2.meta_key = 'group_type' AND pm2.meta_value = 'church'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t2
            GROUP BY t2.admin2_grid_id
            UNION
            SELECT t3.admin3_grid_id as grid_id, count(t3.admin3_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                JOIN $wpdb->postmeta as pm2 ON pm2.post_id=pm.post_id AND pm2.meta_key = 'group_type' AND pm2.meta_value = 'church'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t3
            GROUP BY t3.admin3_grid_id
            UNION
            SELECT t4.admin4_grid_id as grid_id, count(t4.admin4_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                JOIN $wpdb->postmeta as pm2 ON pm2.post_id=pm.post_id AND pm2.meta_key = 'group_type' AND pm2.meta_value = 'church'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t4
            GROUP BY t4.admin4_grid_id
            UNION
            SELECT t5.admin5_grid_id as grid_id, count(t5.admin5_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                JOIN $wpdb->postmeta as pm2 ON pm2.post_id=pm.post_id AND pm2.meta_key = 'group_type' AND pm2.meta_value = 'church'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t5
            GROUP BY t5.admin5_grid_id;
            ", ARRAY_A );
        }

        $list = [];
        if ( is_array( $results ) ) {
            foreach ( $results as $result ) {
                $list[$result['grid_id']] = $result;
            }
        }

        return $list;
    }


    public static function query_user_location_grid_totals( $status = null ) {

        global $wpdb;

        if ( $status ) {
            $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
            FROM (
             SELECT lg.admin0_grid_id FROM $wpdb->usermeta as um
             	 LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=um.meta_value
             	 LEFT JOIN $wpdb->usermeta as um2 ON um.user_id=um2.user_id AND um2.meta_key = %s AND um2.meta_value = %s
             	 WHERE um.meta_key = %s AND um2.meta_value = %s
            ) as t0
            GROUP BY t0.admin0_grid_id
            UNION
            SELECT t1.admin1_grid_id as grid_id, count(t1.admin1_grid_id) as count
            FROM (
             SELECT lg.admin1_grid_id FROM $wpdb->usermeta as um
             	 LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=um.meta_value
             	 LEFT JOIN $wpdb->usermeta as um2 ON um.user_id=um2.user_id AND um2.meta_key = %s AND um2.meta_value = %s
             	 WHERE um.meta_key = %s AND um2.meta_value = %s
            ) as t1
            GROUP BY t1.admin1_grid_id
            UNION
            SELECT t2.admin2_grid_id as grid_id, count(t2.admin2_grid_id) as count
            FROM (
             SELECT lg.admin2_grid_id FROM $wpdb->usermeta as um
             	 LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=um.meta_value
             	 LEFT JOIN $wpdb->usermeta as um2 ON um.user_id=um2.user_id AND um2.meta_key = %s AND um2.meta_value = %s
             	 WHERE um.meta_key = %s AND um2.meta_value = %s
            ) as t2
            GROUP BY t2.admin2_grid_id
            UNION
            SELECT t3.admin3_grid_id as grid_id, count(t3.admin3_grid_id) as count
            FROM (
             SELECT lg.admin3_grid_id FROM $wpdb->usermeta as um
             	 LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=um.meta_value
             	 LEFT JOIN $wpdb->usermeta as um2 ON um.user_id=um2.user_id AND um2.meta_key = %s AND um2.meta_value = %s
             	 WHERE um.meta_key = %s AND um2.meta_value = %s
            ) as t3
            GROUP BY t3.admin3_grid_id
            UNION
            SELECT t4.admin4_grid_id as grid_id, count(t4.admin4_grid_id) as count
            FROM (
             SELECT lg.admin4_grid_id FROM $wpdb->usermeta as um
             	 LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=um.meta_value
             	 LEFT JOIN $wpdb->usermeta as um2 ON um.user_id=um2.user_id AND um2.meta_key = %s AND um2.meta_value = %s
             	 WHERE um.meta_key = %s AND um2.meta_value = %s
            ) as t4
            GROUP BY t4.admin4_grid_id
            UNION
            SELECT t5.admin5_grid_id as grid_id, count(t5.admin5_grid_id) as count
            FROM (
             SELECT lg.admin5_grid_id FROM $wpdb->usermeta as um
             	 LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=um.meta_value
             	 LEFT JOIN $wpdb->usermeta as um2 ON um.user_id=um2.user_id AND um2.meta_key = %s AND um2.meta_value = %s
             	 WHERE um.meta_key = %s AND um2.meta_value = %s
            ) as t5
            GROUP BY t5.admin5_grid_id;
            ",
                // 0
                $wpdb->prefix .'user_status',
                $status,
                $wpdb->prefix .'location_grid',
                $status,
                // 1
                $wpdb->prefix .'user_status',
                $status,
                $wpdb->prefix .'location_grid',
                $status,
                // 2
                $wpdb->prefix .'user_status',
                $status,
                $wpdb->prefix .'location_grid',
                $status,
                // 3
                $wpdb->prefix .'user_status',
                $status,
                $wpdb->prefix .'location_grid',
                $status,
                // 4
                $wpdb->prefix .'user_status',
                $status,
                $wpdb->prefix .'location_grid',
                $status,
                // 5
                $wpdb->prefix .'user_status',
                $status,
                $wpdb->prefix .'location_grid',
                $status
            ), ARRAY_A );

        } else {

            $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
            FROM (
             SELECT lg.admin0_grid_id FROM $wpdb->usermeta as um LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=um.meta_value WHERE meta_key = %s
            ) as t0
            GROUP BY t0.admin0_grid_id
            UNION
            SELECT t1.admin1_grid_id as grid_id, count(t1.admin1_grid_id) as count
            FROM (
             SELECT lg.admin1_grid_id FROM $wpdb->usermeta as um LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=um.meta_value WHERE meta_key = %s
            ) as t1
            GROUP BY t1.admin1_grid_id
            UNION
            SELECT t2.admin2_grid_id as grid_id, count(t2.admin2_grid_id) as count
            FROM (
             SELECT lg.admin2_grid_id FROM $wpdb->usermeta as um LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=um.meta_value WHERE meta_key = %s
            ) as t2
            GROUP BY t2.admin2_grid_id
            UNION
            SELECT t3.admin3_grid_id as grid_id, count(t3.admin3_grid_id) as count
            FROM (
             SELECT lg.admin3_grid_id FROM $wpdb->usermeta as um LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=um.meta_value WHERE meta_key = %s
            ) as t3
            GROUP BY t3.admin3_grid_id
            UNION
            SELECT t4.admin4_grid_id as grid_id, count(t4.admin4_grid_id) as count
            FROM (
             SELECT lg.admin4_grid_id FROM $wpdb->usermeta as um LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=um.meta_value WHERE meta_key = %s
            ) as t4
            GROUP BY t4.admin4_grid_id
            UNION
            SELECT t5.admin5_grid_id as grid_id, count(t5.admin5_grid_id) as count
            FROM (
             SELECT lg.admin5_grid_id FROM $wpdb->usermeta as um LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=um.meta_value WHERE meta_key = %s
            ) as t5
            GROUP BY t5.admin5_grid_id;
            ", $wpdb->prefix . 'location_grid', $wpdb->prefix . 'location_grid', $wpdb->prefix . 'location_grid', $wpdb->prefix . 'location_grid', $wpdb->prefix . 'location_grid', $wpdb->prefix . 'location_grid' ), ARRAY_A );
        }

        $list = [];
        if ( is_array( $results ) ) {
            foreach ( $results as $result ) {
                $list[$result['grid_id']] = $result;
            }
        }

        return $list;
    }
}
DT_Network_Dashboard_Snapshot_Locations::instance();
