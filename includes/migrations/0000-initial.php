<?php
declare(strict_types=1);

require_once( 'abstract.php' );

/**
 * Class DT_Saturation_Mapping_Migration_0000
 */
class DT_Saturation_Mapping_Migration_0000 extends DT_Saturation_Mapping_Migration {

    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {
        global $wpdb;
        $expected_tables = $this->get_expected_tables();
        foreach ( $expected_tables as $name => $table) {
            $rv = $wpdb->query( $table ); // WPCS: unprepared SQL OK
            if ( $rv == false ) {
                throw new Exception( "Got error when creating table $name: $wpdb->last_error" );
            }
        }
    }

    /**
     * @throws \Exception  Got error when dropping table $name.
     */
    public function down() {
        global $wpdb;
        $expected_tables = $this->get_expected_tables();
        foreach ( $expected_tables as $name => $table) {
            $rv = $wpdb->query( "DROP TABLE `{$name}`" ); // WPCS: unprepared SQL OK
            if ( $rv == false ) {
                throw new Exception( "Got error when dropping table $name: $wpdb->last_error" );
            }
        }
    }

    /**
     * @return array
     */
    public function get_expected_tables(): array {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        return array(
            "{$wpdb->prefix}dt_geonames" =>
                "CREATE TABLE `{$wpdb->prefix}dt_geonames` (
                  `geonameid` bigint(11) unsigned NOT NULL,
                  `name` varchar(200) DEFAULT NULL,
                  `asciiname` varchar(200) DEFAULT NULL,
                  `alternatenames` varchar(10000) DEFAULT NULL,
                  `latitude` float DEFAULT NULL,
                  `longitude` float DEFAULT NULL,
                  `feature_class` char(1) DEFAULT NULL,
                  `feature_code` varchar(10) DEFAULT NULL,
                  `country_code` char(2) DEFAULT NULL,
                  `cc2` varchar(100) DEFAULT NULL,
                  `admin1_code` varchar(20) DEFAULT NULL,
                  `admin2_code` varchar(80) DEFAULT NULL,
                  `admin3_code` varchar(20) DEFAULT NULL,
                  `admin4_code` varchar(20) DEFAULT NULL,
                  `population` int(11) DEFAULT NULL,
                  `elevation` int(80) DEFAULT NULL,
                  `dem` varchar(80) DEFAULT NULL,
                  `timezone` varchar(40) DEFAULT NULL,
                  `modification_date` date DEFAULT NULL,
                  PRIMARY KEY (`geonameid`),
                  FULLTEXT KEY `feature_class` (`feature_class`),
                  FULLTEXT KEY `feature_code` (`feature_code`),
                  FULLTEXT KEY `country_code` (`country_code`),
                  FULLTEXT KEY `admin1_code` (`admin1_code`),
                  FULLTEXT KEY `admin2_code` (`admin2_code`)
                ) $charset_collate;",
            "{$wpdb->prefix}dt_geonames_polygons" =>
                "CREATE TABLE `{$wpdb->prefix}dt_geonames_polygons` (
                  `geonameid` bigint(11) unsigned NOT NULL,
                  `geoJSON` longtext,
                  PRIMARY KEY (`geonameid`)
                ) $charset_collate;",
        );
    }

    /**
     * Test function
     */
    public function test() {
        $this->test_expected_tables();
    }

}
