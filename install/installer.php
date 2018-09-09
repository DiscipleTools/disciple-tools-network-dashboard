<?php
/**
 * Installer
 */

if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}

class DT_Saturation_Mapping_Installer {

    public static function get_geonames_source( $file ) {
        if ( empty( $file ) ) {
            return [];
        }

        $csv = [];
        if (( $handle = fopen( "https://raw.githubusercontent.com/DiscipleTools/saturation-mapping-data/master/csv/".$file.".csv", "r" ) ) !== false) {
            while (( $data = fgetcsv( $handle, 0, "," ) ) !== false) {
                $csv[] = $data;
            }
            fclose( $handle );
        }
        return $csv;
    }

    public static function get_list_of_available_locations() {
        $json = file_get_contents(plugin_dir_path( __DIR__ ) . '/install/countries.json' );
        $json = json_decode( $json, true );
        asort(  $json );
        return $json;
    }

    /**
     * Load country
     * @param $country_code
     *
     * @return array|\WP_Error
     */
    public static function load_by_country( $country_code ) {
        global $wpdb;
        $nested_array = [];

        $adm1 = $wpdb->get_results( $wpdb->prepare("
            SELECT geonameid, name, admin1_code
            FROM $wpdb->dt_geonames
            WHERE country_code = %s
              AND feature_code = 'ADM1'
              AND feature_class = 'A'
            ORDER BY name ASC
        ",
        $country_code
        ), ARRAY_A );


        $adm2 = $wpdb->get_results( $wpdb->prepare("
            SELECT geonameid, name, admin1_code
            FROM $wpdb->dt_geonames
            WHERE country_code = %s
              AND feature_code = 'ADM2'
              AND feature_class = 'A'
            ORDER BY name ASC
        ",
            $country_code
        ), ARRAY_A );


        foreach ( $adm1 as $value1 ) {
            $nested_array[ $value1['admin1_code'] ] = [
                'geonameid' => $value1['geonameid'],
                'name' => $value1['name'],
                'adm2' => [],
            ];
            foreach ( $adm2 as $value2 ) {
                if( $value2['admin1_code'] === $value1['admin1_code'] ) {
                    $nested_array[ $value2['admin1_code'] ]['adm2'][] = [
                        'geonameid' => $value2['geonameid'],
                        'name' => $value2['name'],
                    ];
                }
            }
        }

        if ( $nested_array ) {
            return $nested_array;
        } else {
            return new WP_Error(__METHOD__, 'Empty query');
        }


    }

    public static function import_by_file_name( $file ) {
        global $wpdb;

        $d = copy( "https://raw.githubusercontent.com/DiscipleTools/saturation-mapping-data/master/csv/gn".$file.".csv", plugin_dir_path( __DIR__ ) .'/install/' . $file . '.csv' );

        if ( $d ) {
            $result = $wpdb->query('LOAD DATA LOCAL INFILE "' . plugin_dir_path( __DIR__ ) . 'install/' .$file.'.csv"
                INTO TABLE '.$wpdb->dt_geonames.'
                FIELDS TERMINATED by \',\'
                ENCLOSED BY \'"\'
                LINES TERMINATED BY \'\n\'
                IGNORE 1 LINES');
            if ( $result ) {
                return $result;
            } else {
                return $wpdb->last_error;
            }
        } else {
            return new WP_Error(__METHOD__, 'Failed to copy file from github.');
        }

    }

    public static function install_world_admin_set() {
        global $wpdb;
        $file = 'gn_world_admin';
        $result = $wpdb->query('LOAD DATA LOCAL INFILE "' . plugin_dir_path( __DIR__ ) . 'install/' .$file.'.csv"
            INTO TABLE '.$wpdb->dt_geonames.'
            FIELDS TERMINATED by \',\'
            ENCLOSED BY \'"\'
            LINES TERMINATED BY \'\n\'
            IGNORE 1 LINES');
        if ($result) {
            return true;
        } else {
            dt_write_log( $result );
            return false;
        }
    }

    public static function install_admin2_geoname( $geonameid ) {
        global $wpdb;
        $error = new WP_Error();
        $installed = [];

        $result = $wpdb->get_row( $wpdb->prepare("
        SELECT  
            (SELECT azero.geonameid 
            FROM $wpdb->dt_geonames as azero 
            WHERE azero.feature_class = 'A' 
                AND azero.feature_code = 'PCLI' 
                AND azero.country_code = atwo.country_code LIMIT 1) as country_id,
            (SELECT aone.geonameid 
            FROM $wpdb->dt_geonames as aone 
            WHERE aone.feature_class = 'A' 
                AND aone.feature_code = 'ADM1'
                AND aone.admin1_code = atwo.admin1_code
                AND aone.country_code = atwo.country_code LIMIT 1) as admin1_id,
            atwo.geonameid as admin2_id,
            (SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'gn_geonameid' AND meta_value = country_id LIMIT 1) as country_post_id,
            (SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'gn_geonameid' AND meta_value = admin1_id LIMIT 1) as admin1_post_id,
            (SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'gn_geonameid' AND meta_value = admin2_id LIMIT 1) as admin2_post_id,
            atwo.*
        FROM $wpdb->dt_geonames as atwo
        WHERE atwo.geonameid = %s
        ",
            $geonameid), ARRAY_A );


        // test query results
        if ( ! $result ) {
            $error->add(__METHOD__, 'No matching results for this geoname' );
            return [
                'status' => 'FAIL',
                'message' => 'No geoname found for parameter provided.',
                'error' => $error,
                'installed' => '',
            ];
        }

        // admin2 duplicate check
        if ( isset( $result['admin2_post_id'] ) && ! empty( $result['admin2_post_id'] ) ) {
            return [
                'status' => 'DUPLICATE',
                'message' => 'This location is already installed.',
                'error' => '',
                'installed' => '',
            ];
        }

        // country duplicate check else query build
        if ( empty( $result['country_post_id'] ) ) {
            $country_result = $wpdb->get_row( $wpdb->prepare(
                "SELECT * 
                        FROM $wpdb->dt_geonames 
                        WHERE geonameid = %s", $result['country_id'] ), ARRAY_A );
            if ( $country_result ) {
                $args = [
                    'post_title' => $country_result['name'],
                    'post_status' => 'publish',
                    'post_name' => $country_result['geonameid'],
                    'post_type' => 'locations',
                    'meta_input' => [
                        'gn_geonameid' => $country_result['geonameid'],
                        'gn_name' => $country_result['name'],
                        'gn_asciiname' => $country_result['asciiname'],
                        'gn_alternatenames' => $country_result['alternatenames'],
                        'gn_latitude' => $country_result['latitude'],
                        'gn_longitude' => $country_result['longitude'],
                        'gn_feature_class' => $country_result['feature_class'],
                        'gn_feature_code' => $country_result['feature_code'],
                        'gn_admin1_code' => $country_result['admin1_code'],
                        'gn_admin2_code' => $country_result['admin2_code'],
                        'gn_admin3_code' => $country_result['admin3_code'],
                        'gn_admin4_code' => $country_result['admin4_code'],
                        'gn_population' => $country_result['population'],
                        'gn_elevation' => $country_result['elevation'],
                        'gn_dem' => $country_result['dem'],
                        'gn_timezone' => $country_result['timezone'],
                        'gn_modification_date' => $country_result['modification_date'],
                    ],
                ];
                $duplicate = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'gn_geonameid' AND meta_value = %s", $result['country_id'] ) );
                if ( $duplicate ) {
                    $result['country_post_id'] = $duplicate;
                    $installed['country'] = $duplicate;
                } else {
                    $country_post_id = wp_insert_post( $args, true );
                    if ( is_wp_error( $country_post_id ) ) {
                        $error->add(__METHOD__, 'Error inserting country location' );
                    } else {
                        $result['country_post_id'] = $country_post_id;
                        $installed['country'] = $country_post_id;
                    }
                }

            } else {
                $error->add(__METHOD__, 'No results for country in geonames.' );
            }
        }

        // admin1 duplicate check else query build
        if ( empty( $result['admin1_post_id'] ) ) {

            $admin1_result = $wpdb->get_row( $wpdb->prepare(
                "SELECT * 
                        FROM $wpdb->dt_geonames 
                        WHERE geonameid = %s", $result['admin1_id'] ), ARRAY_A );

            if ( $admin1_result ) {
                $args = [
                    'post_title' => $admin1_result['name'],
                    'post_status' => 'publish',
                    'post_name' => $admin1_result['geonameid'],
                    'post_type' => 'locations',
                    'post_parent' => $result['country_post_id'],
                    'meta_input' => [
                        'gn_geonameid' => $admin1_result['geonameid'],
                        'gn_name' => $admin1_result['name'],
                        'gn_asciiname' => $admin1_result['asciiname'],
                        'gn_alternatenames' => $admin1_result['alternatenames'],
                        'gn_latitude' => $admin1_result['latitude'],
                        'gn_longitude' => $admin1_result['longitude'],
                        'gn_feature_class' => $admin1_result['feature_class'],
                        'gn_feature_code' => $admin1_result['feature_code'],
                        'gn_admin1_code' => $admin1_result['admin1_code'],
                        'gn_admin2_code' => $admin1_result['admin2_code'],
                        'gn_admin3_code' => $admin1_result['admin3_code'],
                        'gn_admin4_code' => $admin1_result['admin4_code'],
                        'gn_population' => $admin1_result['population'],
                        'gn_elevation' => $admin1_result['elevation'],
                        'gn_dem' => $admin1_result['dem'],
                        'gn_timezone' => $admin1_result['timezone'],
                        'gn_modification_date' => $admin1_result['modification_date'],
                    ],
                ];
                $duplicate = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'gn_geonameid' AND meta_value = %s", $result['admin1_id'] ) );
                if ( $duplicate ) { // check for duplicate
                    $result['admin1_post_id'] = $duplicate;
                    $installed['admin1'] = $duplicate;
                } else { // insert if no duplicate
                    $admin1_post_id = wp_insert_post( $args, true );
                    if ( ! is_wp_error( $admin1_post_id ) ) {
                        $result[ 'admin1_post_id' ] = $admin1_post_id;
                        $installed[ 'admin1' ] = $admin1_post_id;
                    } else {
                        $error->add( __METHOD__, 'Failed to insert admin1 level. ' . $admin1_post_id->get_error_message() );
                    }
                }
            } else {
                $error->add(__METHOD__, 'No results for admin1 in geonames. ' . $admin1_result->get_error_message() );
            }
        }

        $args = [
            'post_title' => $result['name'],
            'post_status' => 'publish',
            'post_name' => $result['geonameid'],
            'post_type' => 'locations',
            'post_parent' => $result['admin1_post_id'],
            'meta_input' => [
                'gn_geonameid' => $result['geonameid'],
                'gn_name' => $result['name'],
                'gn_asciiname' => $result['asciiname'],
                'gn_alternatenames' => $result['alternatenames'],
                'gn_latitude' => $result['latitude'],
                'gn_longitude' => $result['longitude'],
                'gn_feature_class' => $result['feature_class'],
                'gn_feature_code' => $result['feature_code'],
                'gn_admin1_code' => $result['admin1_code'],
                'gn_admin2_code' => $result['admin2_code'],
                'gn_admin3_code' => $result['admin3_code'],
                'gn_admin4_code' => $result['admin4_code'],
                'gn_population' => $result['population'],
                'gn_elevation' => $result['elevation'],
                'gn_dem' => $result['dem'],
                'gn_timezone' => $result['timezone'],
                'gn_modification_date' => $result['modification_date'],
            ],
        ];
        $admin2_post_id = wp_insert_post( $args, true );
        if ( is_wp_error( $admin2_post_id ) ) {
            $error->add(__METHOD__, 'Error inserting admin2 level. ' . $admin2_post_id->get_error_message() );
            return [
                'status' => 'FAIL',
                'message' => 'Failed to install admin2',
                'error' => $error,
                'installed' => $installed,
            ];
        } else {
            $installed['admin2'] = $admin2_post_id;
            return [
                'status' => 'OK',
                'message' => 'Successfully installed admin2',
                'error' => $error,
                'installed' => $installed,
            ];
        }
    }

    public static function install_admin1_geoname( $geonameid ) {
        global $wpdb;
        $error = new WP_Error();
        $installed = [];

        $result = $wpdb->get_row( $wpdb->prepare("
        SELECT  
            (SELECT azero.geonameid 
            FROM $wpdb->dt_geonames as azero 
            WHERE azero.feature_class = 'A' 
                AND azero.feature_code = 'PCLI' 
                AND azero.country_code = atwo.country_code LIMIT 1) as country_id,
            (SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'gn_geonameid' AND meta_value = country_id LIMIT 1) as country_post_id,
            (SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'gn_geonameid' AND meta_value = atwo.geonameid LIMIT 1) as admin1_post_id,
            atwo.*
        FROM $wpdb->dt_geonames as atwo
        WHERE atwo.geonameid = %s
        ",
            $geonameid), ARRAY_A );


        // test query results
        if ( ! $result ) {
            $error->add(__METHOD__, 'No matching results for this geoname' );
            return [
                'status' => 'FAIL',
                'message' => 'No geoname found for parameter provided.',
                'error' => $error,
                'installed' => '',
            ];
        }

        // admin2 duplicate check
        if ( isset( $result['admin1_post_id'] ) && ! empty( $result['admin1_post_id'] ) ) {
            return [
                'status' => 'DUPLICATE',
                'message' => 'This location is already installed.',
                'error' => '',
                'installed' => '',
            ];
        }

        // country duplicate check else query build
        if ( empty( $result['country_post_id'] ) ) {
            $country_result = $wpdb->get_row( $wpdb->prepare(
                "SELECT * 
                        FROM $wpdb->dt_geonames 
                        WHERE geonameid = %s", $result['country_id'] ), ARRAY_A );
            if ( $country_result ) {
                $args = [
                    'post_title' => $country_result['name'],
                    'post_status' => 'publish',
                    'post_name' => $country_result['geonameid'],
                    'post_type' => 'locations',
                    'meta_input' => [
                        'gn_geonameid' => $country_result['geonameid'],
                        'gn_name' => $country_result['name'],
                        'gn_asciiname' => $country_result['asciiname'],
                        'gn_alternatenames' => $country_result['alternatenames'],
                        'gn_latitude' => $country_result['latitude'],
                        'gn_longitude' => $country_result['longitude'],
                        'gn_feature_class' => $country_result['feature_class'],
                        'gn_feature_code' => $country_result['feature_code'],
                        'gn_admin1_code' => $country_result['admin1_code'],
                        'gn_admin2_code' => $country_result['admin2_code'],
                        'gn_admin3_code' => $country_result['admin3_code'],
                        'gn_admin4_code' => $country_result['admin4_code'],
                        'gn_population' => $country_result['population'],
                        'gn_elevation' => $country_result['elevation'],
                        'gn_dem' => $country_result['dem'],
                        'gn_timezone' => $country_result['timezone'],
                        'gn_modification_date' => $country_result['modification_date'],
                    ],
                ];
                $duplicate = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'gn_geonameid' AND meta_value = %s", $result['country_id'] ) );
                if ( $duplicate ) {
                    $result['country_post_id'] = $duplicate;
                    $installed['country'] = $duplicate;
                } else {
                    $country_post_id = wp_insert_post( $args, true );
                    if ( is_wp_error( $country_post_id ) ) {
                        $error->add(__METHOD__, 'Error inserting country location' );
                    } else {
                        $result['country_post_id'] = $country_post_id;
                        $installed['country'] = $country_post_id;
                    }
                }

            } else {
                $error->add(__METHOD__, 'No results for country in geonames.' );
            }
        }

        $admin1_result = $wpdb->get_row( $wpdb->prepare(
            "SELECT * 
                        FROM $wpdb->dt_geonames 
                        WHERE geonameid = %s", $geonameid ), ARRAY_A );

        if ( $admin1_result ) {
            $args = [
                'post_title'  => $admin1_result[ 'name' ],
                'post_status' => 'publish',
                'post_name'   => $admin1_result[ 'geonameid' ],
                'post_type'   => 'locations',
                'post_parent' => $result[ 'country_post_id' ],
                'meta_input'  => [
                    'gn_geonameid'         => $admin1_result[ 'geonameid' ],
                    'gn_name'              => $admin1_result[ 'name' ],
                    'gn_asciiname'         => $admin1_result[ 'asciiname' ],
                    'gn_alternatenames'    => $admin1_result[ 'alternatenames' ],
                    'gn_latitude'          => $admin1_result[ 'latitude' ],
                    'gn_longitude'         => $admin1_result[ 'longitude' ],
                    'gn_feature_class'     => $admin1_result[ 'feature_class' ],
                    'gn_feature_code'      => $admin1_result[ 'feature_code' ],
                    'gn_admin1_code'       => $admin1_result[ 'admin1_code' ],
                    'gn_admin2_code'       => $admin1_result[ 'admin2_code' ],
                    'gn_admin3_code'       => $admin1_result[ 'admin3_code' ],
                    'gn_admin4_code'       => $admin1_result[ 'admin4_code' ],
                    'gn_population'        => $admin1_result[ 'population' ],
                    'gn_elevation'         => $admin1_result[ 'elevation' ],
                    'gn_dem'               => $admin1_result[ 'dem' ],
                    'gn_timezone'          => $admin1_result[ 'timezone' ],
                    'gn_modification_date' => $admin1_result[ 'modification_date' ],
                ],
            ];
            $admin1_post_id = wp_insert_post( $args, true );
            if ( ! is_wp_error( $admin1_post_id ) ) {
                $installed[ 'admin1' ] = $admin1_post_id;

                return [
                    'status'    => 'OK',
                    'message'   => 'Successfully installed admin1',
                    'error'     => $error,
                    'installed' => $installed,
                ];
            } else {
                $error->add( __METHOD__, 'Error inserting admin2 level. ' . $admin1_post_id->get_error_message() );

                return [
                    'status'    => 'FAIL',
                    'message'   => 'Failed to install admin1',
                    'error'     => $error,
                    'installed' => $installed,
                ];
            }
        } else {
            return [
                'status'    => 'FAIL',
                'message'   => 'Failed to install admin1 because no geonames results found',
                'error'     => $error,
                'installed' => $installed,
            ];
        }
    }

    public static function load_current_locations() {
        global $wpdb;

        $query = $wpdb->get_results("
            SELECT
                  a.ID         as id,
                  a.post_parent as parent_id,
                  a.post_title as name
                FROM $wpdb->posts as a
                WHERE a.post_status = 'publish'
                AND a.post_type = 'locations'
            ", ARRAY_A );


        // prepare special array with parent-child relations
        $menu_data = array(
            'items' => array(),
            'parents' => array()
        );

        foreach ( $query as $menuItem )
        {
            $menu_data['items'][$menuItem['id']] = $menuItem;
            $menu_data['parents'][$menuItem['parent_id']][] = $menuItem['id'];
        }

        // output the menu
        return self::build_tree( 0, $menu_data, -1 );

    }

    public static function build_tree( $parent_id, $menu_data, $gen) {
        $html = '';

        if (isset( $menu_data['parents'][$parent_id] ))
        {
            $gen++;
            foreach ($menu_data['parents'][$parent_id] as $itemId)
            {
                if ( $gen >= 1 ) {
                    for ($i = 0; $i < $gen; $i++ ) {
                        $html .= '-- ';
                    }

                }
                $html .= $menu_data['items'][$itemId]['name'] . '<br>';

                // find childitems recursively
                $html .= self::build_tree( $itemId, $menu_data, $gen );
            }
        }
        return $html;
    }



}