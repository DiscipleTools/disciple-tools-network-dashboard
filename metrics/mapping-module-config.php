<?php

class DT_Network_Mapping_Module_Config
{
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        $url = $this->get_url_path();
        if ( 'network' === substr( $url, 0, 7 ) ) {
            /**
             * dt_mapping_module_has_permissions
             *
             * @see    mapping.php:56
             */
            add_filter( 'dt_mapping_module_has_permissions', array( $this, 'custom_permission_check' ) );

            /**
             * dt_mapping_module_translations
             *
             * @see     mapping.php:119 125
             */
            add_filter( 'dt_mapping_module_translations', array( $this, 'custom_translations_filter' ) );

            /**
             * dt_mapping_module_settings
             *
             * @see     mapping.php:241
             */
            add_filter( 'dt_mapping_module_settings', array( $this, 'custom_settings_filter' ) );

            /**
             * Use this filter to add data to sub levels by location_grid
             * dt_mapping_module_map_level_by_location_grid
             *
             * @see     mapping.php:389
             */
            add_filter( 'dt_mapping_module_map_level_by_location_grid', array( $this, 'map_level_by_location_grid_filter' ), 10, 1 );

            /**
             * dt_mapping_module_url_base
             *
             * @see     mapping.php:102
             */
            add_filter( 'dt_mapping_module_url_base', array( $this, 'custom_url_base' ) );

            /**
             * dt_mapping_module_endpoints
             *
             * @see     mapping.php:77
             */
            add_filter( 'dt_mapping_module_endpoints', array( $this, 'add_custom_endpoints' ), 10, 1 );


            /**
             * Enqueue mapping scripts
             */
            add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ), 99 );

        }
    }

    public function get_url_path() {
        if ( isset( $_SERVER["HTTP_HOST"] ) ) {
            $url  = ( !isset( $_SERVER["HTTPS"] ) || @( $_SERVER["HTTPS"] != 'on' ) ) ? 'http://'. sanitize_text_field( wp_unslash( $_SERVER["HTTP_HOST"] ) ) : 'https://'. sanitize_text_field( wp_unslash( $_SERVER["HTTP_HOST"] ) );
            if ( isset( $_SERVER["REQUEST_URI"] ) ) {
                $url .= sanitize_text_field( wp_unslash( $_SERVER["REQUEST_URI"] ) );
            }
        }
        return trim( str_replace( get_site_url(), "", $url ), '/' );
    }

    /**
     * custom_permission_check
     *
     * @return bool
     */
    public function custom_permission_check(): bool {
        return dt_network_dashboard_has_permission();
    }

    public function custom_settings_filter( $data ) {
        /**
         * Add or modify current settings
         */
        return $data;
    }

    /**
     * custom_translations
     *
     * @param $translations
     *
     * @return mixed
     */
    public function custom_translations_filter( $translations ) {
        /**
         * Add translation strings
         */
        return $translations;
    }

    /**
     * Pre-processes map_level data before delivery
     *
     * @param $data
     *
     * @return mixed
     */
    public function map_level_by_location_grid_filter( $data ) {
        /**
         * Add filter here
         */
        $data = array();
        return $data;
    }

    /**
     * add_custom_endpoints
     *
     * @param $endpoints
     *
     * @return mixed
     */
    public function add_custom_endpoints( $endpoints ) {
        /**
         * Add new endpoint here
         */
        return $endpoints;
    }

    /**
     * Set the base url for the mapping links to respond to.
     *
     * @param $base_url
     *
     * @return string
     */
    public function custom_url_base( $base_url ) {
        /**
         * Add new url base for listener
         */
        $url = dt_get_url_path();
        if ( 'network' === $url ) {
            $base_url = false;
        }
        return $base_url;
    }

    public function scripts() {
//        if ( DT_Mapbox_API::get_key() ){
//            DT_Mapbox_API::load_mapbox_header_scripts();
//        }
//
//        wp_register_script( 'amcharts-core', 'https://www.amcharts.com/lib/4/core.js', false, '4' );
//        wp_register_script( 'amcharts-charts', 'https://www.amcharts.com/lib/4/charts.js', false, '4' );
//        wp_register_script( 'amcharts-animated', 'https://www.amcharts.com/lib/4/themes/animated.js', false, '4' );
//        wp_register_script( 'amcharts-maps', 'https://www.amcharts.com/lib/4/maps.js', false, '4' );
//        wp_register_script( 'amcharts-world', 'https://www.amcharts.com/lib/4/geodata/worldLow.js', false, '4' );
//
//        // Datatable
//        wp_register_style( 'datatable-css', '//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css' );
//        wp_enqueue_style( 'datatable-css' );
//        wp_register_script( 'datatable', '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js', false, '1.10' );
//
//        // Drill Down Tool
//        wp_enqueue_script( 'mapping-drill-down', get_template_directory_uri() . '/dt-mapping/drill-down.js', [ 'jquery', 'lodash' ], '1.1' );
//        wp_localize_script(
//            'mapping-drill-down',
//            'mappingModule',
//            array(
//                'mapping_module' => $this->localize_script(),
//            )
//        );
    }

    public function localize_script() {
        if ( ! class_exists( 'DT_Mapping_Module' ) ) {
            require_once( get_template_directory() . 'dt-mapping/mapping.php' );
        }
        $mapping_module = DT_Mapping_Module::instance()->localize_script();

        if ( dt_network_dashboard_denied() ) {
            return array();
        } else {
            return $mapping_module;
        }

    }

}
DT_Network_Mapping_Module_Config::instance();
