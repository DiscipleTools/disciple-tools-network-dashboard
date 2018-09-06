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
        return json_decode( $json );
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
            return $d;
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



}