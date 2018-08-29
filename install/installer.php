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
        return [
            'gn_us_md' => 'US - Maryland',
            'gn_us_co' => 'US - Colorado',
            'gn_us_ky' => 'US - Kentucky',
            'gn_us' => 'US - Entire Country',
        ];
    }

    public static function import_by_file_name( $file ) {
        global $wpdb;

        $d = copy( "https://raw.githubusercontent.com/DiscipleTools/saturation-mapping-data/master/csv/".$file.".csv", plugin_dir_path( __DIR__ ) .'/install/' . $file . '.csv' );

        if ( $d ) {
            $result = $wpdb->query('LOAD DATA LOCAL INFILE "' . plugin_dir_path( __DIR__ ) . '/install/' .$file.'.csv"
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

}