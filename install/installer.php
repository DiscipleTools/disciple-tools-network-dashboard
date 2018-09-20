<?php
/**
 * Installer
 */

if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}


class DT_Saturation_Mapping_Installer {

    public static $csv_host = 'https://storage.googleapis.com/discipletools/';

    public static function get_geonames_source( $file ) {
        if ( empty( $file ) ) {
            return [];
        }

        $csv = [];
        if (( $handle = fopen( self::$csv_host . "gn_".$file."_p.csv", "r" ) ) !== false) {
            while (( $data = fgetcsv( $handle, 0, "," ) ) !== false) {
                $csv[] = $data;
            }
            fclose( $handle );
        }
        return $csv;
    }

    public static function get_list_of_available_locations() {
        $json = file_get_contents( plugin_dir_path( __DIR__ ) . '/install/countries.json' );
        $json = json_decode( $json, true );
        asort( $json );
        return $json;
    }

    public static function get_list_of_installed_p_list() {
        global $wpdb;
        $master_list = self::get_list_of_available_locations();

        $installed_list = $wpdb->get_results("
          SELECT DISTINCT country_code  
          FROM $wpdb->dt_geonames 
          WHERE feature_class = 'p'", ARRAY_A );

        $status_list = [];
        $installed_list = array_column( $installed_list, 'country_code' );

        foreach ( $master_list as $key => $item ) {
            $status_list[$key] = false;
            if ( in_array( $key, $installed_list ) ) {
                $status_list[$key] = true;
            }
        }
        dt_write_log( $status_list );
        return $status_list;
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
                if ( $value2['admin1_code'] === $value1['admin1_code'] ) {
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
            return new WP_Error( __METHOD__, 'Empty query' );
        }


    }

    public static function load_p_list_by_country( $country_code ) {
        $file = strtolower( $country_code );
        $install_reponse = self::import_by_file_name( $file );
        return $install_reponse;
    }

    public static function import_by_file_name( $file ) {
        global $wpdb;
        $file = strtolower( $file );

        $d = copy( self::$csv_host . "gn_".$file."_p.csv", plugin_dir_path( __DIR__ ) .'/install/gn_' . $file . '_p.csv' );

        if ( $d ) {
            $result = $wpdb->query('LOAD DATA LOCAL INFILE "' . plugin_dir_path( __DIR__ ) . 'install/gn_' .$file.'_p.csv"
                INTO TABLE '.$wpdb->dt_geonames.'
                FIELDS TERMINATED by \',\'
                ENCLOSED BY \'"\'
                LINES TERMINATED BY \'\n\'
                ');
            if ( $result ) {
                return $result;
            } else {
                return $wpdb->last_error;
            }
        } else {
            return new WP_Error( __METHOD__, 'Failed to copy file.' );
        }

    }

    public static function load_cities( $geonameid ) {
        // the geoname is build for an admin2 geoname, so that we can expect data to find the cities within it

        global $wpdb;
        $error = new WP_Error();

        $query_results = $wpdb->get_row( $wpdb->prepare( "
              SELECT (
                  SELECT a.geonameid  
                  FROM $wpdb->dt_geonames as a 
                  WHERE a.feature_class = 'P' 
                    AND a.country_code = t.country_code 
                    LIMIT 1) as installed,
                  (SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'gn_geonameid' AND meta_value = %s ) as admin2_post_id, 
                    t.*
              FROM $wpdb->dt_geonames as t 
              WHERE t.geonameid = %s
              ", $geonameid, $geonameid ),
            ARRAY_A
        );
        if ( empty( $query_results ) ) {
            $error->add( __METHOD__, "No results. Bad geonameid." );
            return [
                'status' => false,
                'message' => 'No results. Bad geonameid.',
                'error' => $error,
                'installed' => 0,
            ];
        }
        if ( empty( $query_results['installed'] ) ) {
            // install data set
            $data_install = self::import_by_file_name( $query_results['country_code'] ); // downloads and installs datasource
            if ( is_wp_error( $data_install ) ) {
                $error->add( __METHOD__, "Unable to install " . $query_results['country_code'] . " dataset. " . $data_install->get_error_message() );
                return [
                    'status' => false,
                    'message' => "Unable to install " . $query_results['country_code'] . " dataset. " . $data_install->get_error_message(),
                    'error' => $error,
                    'installed' => 0,
                ];
            }
        }

        $country_code = $query_results['country_code'] ?? null;
        $admin1_code = $query_results['admin1_code'] ?? null;
        $admin2_code = $query_results['admin2_code'] ?? null;

        $cities = $wpdb->get_results( $wpdb->prepare( "
            SELECT geonameid, name
            FROM $wpdb->dt_geonames 
            WHERE feature_class = 'P' 
              AND country_code = %s 
              AND admin1_code = %s 
              AND admin2_code = %s
              ORDER BY name 
        ", $country_code, $admin1_code, $admin2_code), ARRAY_A );

        return [
            'status' => true,
            'message' => "",
            'error' => $error,
            'installed' => 0,
            'admin2' => $geonameid,
            'cities' => $cities,
        ];
    }

    public static function install_single_city( $geonameid, $admin2 ) {
        global $wpdb;
        $error = new WP_Error();

        $geonameid_column = $wpdb->get_col( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = 'gn_geonameid';" );
        if ( in_array( $geonameid, $geonameid_column ) ) {
            $error->add( __METHOD__, "Already installed" );
            return [
                'status' => false,
                'message' => 'Already installed',
                'error' => $error,
                'installed' => 0,
            ];
        }

        $query_results = $wpdb->get_row( $wpdb->prepare( "
              SELECT t.*
              FROM $wpdb->dt_geonames as t 
              WHERE t.geonameid = %s
              ", $geonameid ),
            ARRAY_A
        );
        if ( empty( $query_results ) ) {
            $error->add( __METHOD__, "No results. Bad geonameid." );
            return [
                'status' => false,
                'message' => 'No results. Bad geonameid.',
                'error' => $error,
                'installed' => 0,
            ];
        }

        if ( empty( $admin2 ) ) {
            $error->add( __METHOD__, "Missing parent geonameid" );
            return [
                'status' => false,
                'message' => 'Missing parent geonameid.',
                'error' => $error,
                'installed' => 0,
            ];
        }

        $admin2_post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'gn_geonameid' AND meta_value = %s", $admin2 ) );
        if ( empty( $admin2_post_id ) ) {
            $build = self::install_admin2_by_geoname( $admin2 );
            if ( 'OK' === $build['status'] ) {
                $admin2_post_id = $build['ids']['admin2_post_id'];
            } else {
                $error->add( __METHOD__, $build['error'] );
                return [
                    'status' => false,
                    'message' => $build['message'] ,
                    'error' => $build['error'] ,
                    'installed' => 0,
                ];
            }
        }

        $args = [
            'post_title' => $query_results['name'],
            'post_status' => 'publish',
            'post_name' => $query_results['geonameid'],
            'post_type' => 'locations',
            'post_parent' => $admin2_post_id,
            'meta_input' => [
                'gn_geonameid' => $query_results['geonameid'],
                'gn_name' => $query_results['name'],
                'gn_asciiname' => $query_results['asciiname'],
                'gn_alternatenames' => $query_results['alternatenames'],
                'gn_latitude' => $query_results['latitude'],
                'gn_longitude' => $query_results['longitude'],
                'gn_feature_class' => $query_results['feature_class'],
                'gn_feature_code' => $query_results['feature_code'],
                'gn_country_code' => $query_results['country_code'],
                'gn_admin1_code' => $query_results['admin1_code'],
                'gn_admin2_code' => $query_results['admin2_code'],
                'gn_admin3_code' => $query_results['admin3_code'],
                'gn_admin4_code' => $query_results['admin4_code'],
                'gn_population' => $query_results['population'],
                'gn_elevation' => $query_results['elevation'],
                'gn_dem' => $query_results['dem'],
                'gn_timezone' => $query_results['timezone'],
                'gn_modification_date' => $query_results['modification_date'],
            ],
        ];
        $city_post_id = wp_insert_post( $args, true );

        $address = $query_results['name'] . ',' . $query_results['admin1_code'] . ',' . $query_results['country_code'];
        self::geocode_location( $address, $city_post_id );

        return [
            'status' => true,
            'message' => "Install process successful.",
            'error' => $error,
            'installed' => $city_post_id,
        ];
    }


    public static function install_admin2_by_geoname( $geonameid ) {
        global $wpdb;
        $error = new WP_Error();
        $installed = [];

        $result = $wpdb->get_row( $wpdb->prepare("
        SELECT  
            (SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'gn_geonameid' AND meta_value = %s LIMIT 1) as admin2_post_id,
            atwo.*
        FROM $wpdb->dt_geonames as atwo
        WHERE atwo.geonameid = %s
        ",
        $geonameid, $geonameid ), ARRAY_A );

        // test query results
        if ( ! $result ) {
            $error->add( __METHOD__, 'No matching results for this geoname' );
            return [
                'status' => 'FAIL',
                'message' => 'No geoname found for parameter provided.',
                'error' => $error,
                'installed' => ['admin2', 0],
                'post_id' => null,
            ];
        }

        // admin2 duplicate check
        if ( isset( $result['admin2_post_id'] ) && ! empty( $result['admin2_post_id'] ) ) {
            return [
                'status' => 'OK',
                'message' => 'Duplicate: This location is already installed.',
                'error' => $error,
                'installed' => ['admin2', 0],
                'post_id' => $result['admin2_post_id'],
                'ids' => [
                    'country_post_id' => $result['country_post_id'],
                    'admin1_post_id' => $result['admin1_post_id'],
                    'admin2_post_id' => $result['admin2_post_id'],
                    ]
            ];
        }

        $country_row = $wpdb->get_row( $wpdb->prepare("
            SELECT azero.geonameid as country_id, 
              (SELECT post_id 
              FROM $wpdb->postmeta 
              WHERE meta_key = 'gn_geonameid' 
                AND meta_value = azero.geonameid LIMIT 1) as country_post_id
            FROM $wpdb->dt_geonames as azero 
            WHERE azero.feature_class = 'A' 
                AND azero.feature_code = 'PCLI' 
                AND azero.country_code = %s LIMIT 1
            ", $result['country_code'] ), ARRAY_A);

        // country duplicate check else query build
        if ( empty( $country_row['country_post_id'] ) ) {
            $country_result = self::install_country_by_geoname( $country_row['country_id'], $error, $installed );

            if ( 'OK' === $country_result['status'] ) {
                $error = $country_result['error'];
                $installed = $country_result['installed'];
                $result['country_post_id'] = $country_result['post_id'];
                $installed[] = ['country', $country_result['post_id']];
            } else {
                dt_write_log( $country_result );
                $error = $country_result['error'];
                $installed = $country_result['installed'];
                $result['country_post_id'] = $country_result['post_id'];
                $installed[] = ['country', $country_result['post_id']];
            }
        } else {
            $result['country_post_id'] = $country_row['country_post_id'];
        }

        // admin1 duplicate check else query build
        $admin1_row = $wpdb->get_row( $wpdb->prepare("
            SELECT aone.geonameid as admin1_id,
                (SELECT post_id FROM wp_3_postmeta WHERE meta_key = 'gn_geonameid' AND meta_value = aone.geonameid LIMIT 1) as admin1_post_id
                 FROM wp_3_dt_geonames as aone 
                WHERE aone.feature_class = 'A' 
                                AND aone.feature_code = 'ADM1'
                                AND aone.admin1_code = %s
                                AND aone.country_code = %s LIMIT 1
            ", $result['admin1_code'], $result['country_code'] ), ARRAY_A);

        if ( empty( $result['admin1_post_id'] ) ) {

            $admin1_result = self::install_admin1_by_geoname( $admin1_row['admin1_id'], $error, $installed );

            if ( 'OK' === $admin1_result['status'] ) {
                $error = $admin1_result['error'];
                $installed = $admin1_result['installed'];
                $result['admin1_post_id'] = $admin1_result['post_id'];
                $installed[] = ['admin1', $admin1_result['post_id']];
            } else {
                dt_write_log( $admin1_result );

                $error = $admin1_result['error'];
                $installed = $admin1_result['installed'];
                $result['admin1_post_id'] = $admin1_result['post_id'];
                $installed[] = ['admin1', $admin1_result['post_id']];
            }
        } else {
            $result['admin1_post_id'] = $admin1_row['admin1_post_id'];
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
                'gn_country_code' => $result['country_code'],
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
            $error->add( __METHOD__, 'Error inserting admin2 level. ' . $admin2_post_id->get_error_message() );
            return [
                'status' => 'FAIL',
                'message' => 'Failed to install admin2',
                'error' => $error,
                'installed' => $installed,
                'post_id' => null,
            ];
        } else {

            $address = $result['name'] . ',' . $result['admin1_code'] . ',' . $result['country_code'];
            self::geocode_location( $address, $admin2_post_id );

            $installed[] = ['admin2', $admin2_post_id];
            return [
                'status' => 'OK',
                'message' => 'Successfully installed admin2',
                'error' => $error,
                'installed' => $installed,
                'post_id' => $admin2_post_id,
                'ids' => [
                    'country_post_id' => $result['country_post_id'],
                    'admin1_post_id' => $result['admin1_post_id'],
                    'admin2_post_id' => $admin2_post_id,
                ]
            ];
        }
    }

    public static function install_admin1_by_geoname( $geonameid, $error = [], $installed = [] ) {
        global $wpdb;
        if ( empty( $error ) ) {
            $error = new WP_Error();
        }
        dt_write_log( __METHOD__ );
        dt_write_log( microtime() );

        $result = $wpdb->get_row( $wpdb->prepare("
        SELECT  
            (SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'gn_geonameid' AND meta_value = atwo.geonameid LIMIT 1) as admin1_post_id,
            atwo.*
        FROM $wpdb->dt_geonames as atwo
        WHERE atwo.geonameid = %s
        ",
        $geonameid), ARRAY_A );
        dt_write_log( microtime() );

        // test query results
        if ( ! $result ) {
            $error->add( __METHOD__, 'No matching results for this geoname' );
            return [
                'status' => 'FAIL',
                'message' => 'No geoname found for parameter provided.',
                'error' => $error,
                'installed' => ['admin1', 0],
                'post_id' => null,
            ];
        }

        // admin2 duplicate check
        if ( isset( $result['admin1_post_id'] ) && ! empty( $result['admin1_post_id'] ) ) {
            return [
                'status' => 'DUPLICATE',
                'message' => 'This location is already installed.',
                'error' => '',
                'installed' => ['admin1', 0],
                'post_id' => $result['admin1_post_id'],
            ];
        }
        dt_write_log( microtime() );

        // country duplicate check else query build
        $country_row = $wpdb->get_row( $wpdb->prepare("
            SELECT azero.geonameid as country_id, 
              (SELECT post_id 
              FROM $wpdb->postmeta 
              WHERE meta_key = 'gn_geonameid' 
                AND meta_value = azero.geonameid LIMIT 1) as country_post_id
            FROM $wpdb->dt_geonames as azero 
            WHERE azero.feature_class = 'A' 
                AND azero.feature_code = 'PCLI' 
                AND azero.country_code = %s LIMIT 1
            ", $result['country_code'] ), ARRAY_A);

        if ( empty( $country_row['country_post_id'] ) ) {
            $country_result = self::install_country_by_geoname( $country_row['country_id'], $error, $installed );

            if ( 'OK' === $country_result['status'] ) {
                $error = $country_result['error'];
                $installed = $country_result['installed'];
                $result['country_post_id'] = $country_result['post_id'];
                $installed[] = ['country', $country_result['post_id']];
            } else {
                dt_write_log( $country_result );
                $error = $country_result['error'];
                $installed = $country_result['installed'];
                $result['country_post_id'] = $country_result['post_id'];
                $installed[] = ['country', $country_result['post_id']];
            }
        } else {
            $result['country_post_id'] = $country_row['country_post_id'];
        }
        dt_write_log( microtime() );

        $admin1_result = $wpdb->get_row( $wpdb->prepare(
            "SELECT * 
                        FROM $wpdb->dt_geonames 
                        WHERE geonameid = %s", $geonameid ), ARRAY_A );

        if ( $admin1_result ) {
            $args = [
                'post_title'  => $admin1_result['name'],
                'post_status' => 'publish',
                'post_name'   => $admin1_result['geonameid'],
                'post_type'   => 'locations',
                'post_parent' => $result['country_post_id'],
                'meta_input'  => [
                    'gn_geonameid'         => $admin1_result['geonameid'],
                    'gn_name'              => $admin1_result['name'],
                    'gn_asciiname'         => $admin1_result['asciiname'],
                    'gn_alternatenames'    => $admin1_result['alternatenames'],
                    'gn_latitude'          => $admin1_result['latitude'],
                    'gn_longitude'         => $admin1_result['longitude'],
                    'gn_feature_class'     => $admin1_result['feature_class'],
                    'gn_feature_code'      => $admin1_result['feature_code'],
                    'gn_country_code'      => $admin1_result['country_code'],
                    'gn_admin1_code'       => $admin1_result['admin1_code'],
                    'gn_admin2_code'       => $admin1_result['admin2_code'],
                    'gn_admin3_code'       => $admin1_result['admin3_code'],
                    'gn_admin4_code'       => $admin1_result['admin4_code'],
                    'gn_population'        => $admin1_result['population'],
                    'gn_elevation'         => $admin1_result['elevation'],
                    'gn_dem'               => $admin1_result['dem'],
                    'gn_timezone'          => $admin1_result['timezone'],
                    'gn_modification_date' => $admin1_result['modification_date'],
                ],
            ];
            $admin1_post_id = wp_insert_post( $args, true );
            if ( ! is_wp_error( $admin1_post_id ) ) {

                if ( ! get_post_meta( $admin1_post_id, 'raw', true ) ) {
                    $address = $admin1_result[ 'name' ] . ',' . $admin1_result[ 'country_code' ];
                    self::geocode_location( $address, $admin1_post_id );
                }

                $installed[] = ['admin1',$admin1_post_id];
                return [
                    'status'    => 'OK',
                    'message'   => 'Successfully installed admin1',
                    'error'     => $error,
                    'installed' => $installed,
                    'post_id' => $admin1_post_id,
                ];
            } else {
                $error->add( __METHOD__, 'Error inserting admin2 level. ' . $admin1_post_id->get_error_message() );

                return [
                    'status'    => 'FAIL',
                    'message'   => 'Failed to install admin1',
                    'error'     => $error,
                    'installed' => $installed,
                    'post_id' => null,
                ];
            }
        } else {
            return [
                'status'    => 'FAIL',
                'message'   => 'Failed to install admin1 because no geonames results found',
                'error'     => $error,
                'installed' => $installed,
                'post_id' => null,
            ];
        }
    }

    /**
     * @param       $geonameid
     * @param array $error
     * @param array $installed
     *
     * @return array
     */
    public static function install_country_by_geoname( $geonameid, $error = [], $installed = [], $post_id = null ) {
        global $wpdb;
        if ( empty( $error ) ) {
            $error = new WP_Error();
        }

        $country_result = $wpdb->get_row( $wpdb->prepare(
            "SELECT * 
                    FROM $wpdb->dt_geonames 
                    WHERE geonameid = %s", $geonameid ), ARRAY_A );
        if ( ! $country_result ) {
            $error->add( __METHOD__, 'No results for country in geonames.' );
            return [
                'status'    => 'FAIL',
                'message'   => 'No results for country in geonames.',
                'error'     => $error,
                'installed' => $installed,
                'post_id' => null,
            ];
        }


        $args = [
            'post_title'  => $country_result[ 'name' ],
            'post_status' => 'publish',
            'post_name'   => $country_result[ 'geonameid' ],
            'post_type'   => 'locations',
            'meta_input'  => [
                'gn_geonameid'         => $country_result[ 'geonameid' ],
                'gn_name'              => $country_result[ 'name' ],
                'gn_asciiname'         => $country_result[ 'asciiname' ],
                'gn_alternatenames'    => $country_result[ 'alternatenames' ],
                'gn_latitude'          => $country_result[ 'latitude' ],
                'gn_longitude'         => $country_result[ 'longitude' ],
                'gn_feature_class'     => $country_result[ 'feature_class' ],
                'gn_feature_code'      => $country_result[ 'feature_code' ],
                'gn_country_code'      => $country_result[ 'country_code' ],
                'gn_admin1_code'       => $country_result[ 'admin1_code' ],
                'gn_admin2_code'       => $country_result[ 'admin2_code' ],
                'gn_admin3_code'       => $country_result[ 'admin3_code' ],
                'gn_admin4_code'       => $country_result[ 'admin4_code' ],
                'gn_population'        => $country_result[ 'population' ],
                'gn_elevation'         => $country_result[ 'elevation' ],
                'gn_dem'               => $country_result[ 'dem' ],
                'gn_timezone'          => $country_result[ 'timezone' ],
                'gn_modification_date' => $country_result[ 'modification_date' ],
            ],
        ];

        // Check if updating record
        if ( ! empty( $post_id ) ) {
            $args['ID'] = $post_id;
            $country_post_id = wp_update_post( $args, true );

            if ( is_wp_error( $country_post_id ) ) {
                dt_write_log( $country_post_id );

                $error->add( __METHOD__, 'Error inserting country location' );
                return [
                    'status'    => 'FAIL',
                    'message'   => 'Successfully installed country',
                    'error'     => $error,
                    'installed' => $installed,
                    'post_id' => null,
                ];
            } else {

                if ( ! get_post_meta( $country_post_id, 'raw', true ) ) {
                    $address = $country_result[ 'name' ];
                    self::geocode_location( $address, $country_post_id );
                }

                $installed[ 'country' ] = $country_post_id;
                return [
                    'status'    => 'OK',
                    'message'   => 'Successfully installed country',
                    'error'     => $error,
                    'installed' => $installed,
                    'post_id' => $country_post_id,
                ];
            }
        }

        // Check for duplicate before create new
        $duplicate = $wpdb->get_var( $wpdb->prepare( "
                SELECT post_id 
                FROM $wpdb->postmeta 
                WHERE meta_key = 'gn_geonameid' 
                  AND meta_value = %s",
            $geonameid ) );
        if ( $duplicate ) {
            $installed[] = ['country', $duplicate];
            return [
                'status'    => 'DUPLICATE',
                'message'   => 'Already installed country',
                'error'     => $error,
                'installed' => $installed,
                'post_id' => $duplicate
            ];

            // Create new record
        } else {
            $country_post_id = wp_insert_post( $args, true );

            if ( is_wp_error( $country_post_id ) ) {
                dt_write_log( "Fail" );
                dt_write_log( $country_post_id );

                $error->add( __METHOD__, 'Error inserting country location' );

                return [
                    'status'    => 'FAIL',
                    'message'   => 'Successfully installed country',
                    'error'     => $error,
                    'installed' => $installed,
                    'post_id'   => null,
                ];
            } else {

                if ( ! get_post_meta( $country_post_id, 'raw', true ) ) {
                    $address = $country_result[ 'name' ];
                    self::geocode_location( $address, $country_post_id );
                }

                $installed[ 'country' ] = $country_post_id;

                return [
                    'status'    => 'OK',
                    'message'   => 'Successfully installed country',
                    'error'     => $error,
                    'installed' => $installed,
                    'post_id'   => $country_post_id,
                ];
            }
        }
    }

    public static function geocode_location( $address, $post_id ) {
        if ( class_exists( 'Disciple_Tools_Google_Geocode_API' ) ) {
            $geocode = new Disciple_Tools_Google_Geocode_API();
            $raw_response = $geocode::query_google_api( $address );
            if ( $geocode::check_valid_request_result( $raw_response ) ) {

                update_post_meta( $post_id, 'location_address', $geocode::parse_raw_result( $raw_response, 'formatted_address' ) );
                update_post_meta( $post_id, 'base_name', $geocode::parse_raw_result( $raw_response, 'base_name' ) );
                update_post_meta( $post_id, 'types', $geocode::parse_raw_result( $raw_response, 'types' ) );
                update_post_meta( $post_id, 'raw', $raw_response );

            }
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

    public static function load_p_countries_installed() {
        $available_locations = self::get_list_of_available_locations();
        $installed_list = self::get_list_of_installed_p_list();

        $html = '';
        foreach ( $installed_list as $key => $value ) {
            if ( $value ) {
                $html .= '<dd>' . $available_locations[$key] . '</dd>';
            }
        }

        return $html;
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
}