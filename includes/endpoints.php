<?php
/**
 * Rest endpoints
 *
 * @package  Disciple_Tools
 * @category Plugin
 * @since    0.1
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

/**
 * Class DT_Saturation_Mapping_Endpoints
 */
class DT_Saturation_Mapping_Endpoints
{

    private $version = 1;
    private $context = "dt";
    private $namespace;

    /**
     * DT_Saturation_Mapping_Endpoints The single instance of DT_Saturation_Mapping_Endpoints.
     *
     * @var     object
     * @access    private
     * @since     0.1.0
     */
    private static $_instance = null;

    /**
     * Main DT_Saturation_Mapping_Endpoints Instance
     * Ensures only one instance of DT_Saturation_Mapping_Endpoints is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return DT_Saturation_Mapping_Endpoints instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {
        $this->namespace = $this->context . "/v" . intval( $this->version );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    } // End __construct()

    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/saturation/import', [
                'methods'  => 'POST',
                'callback' => [ $this, 'import' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/saturation/load_by_country', [
                'methods'  => 'POST',
                'callback' => [ $this, 'load_by_country' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/saturation/install_admin2_geoname', [
                'methods'  => 'POST',
                'callback' => [ $this, 'install_admin2_geoname' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/saturation/install_admin1_geoname', [
                'methods'  => 'POST',
                'callback' => [ $this, 'install_admin1_geoname' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/saturation/load_current_locations', [
                'methods'  => 'POST',
                'callback' => [ $this, 'load_current_locations' ],
            ]
        );
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public function import( WP_REST_Request $request ) {

        if ( ! user_can( get_current_user_id(), 'manage_dt' ) ) {
            return new WP_Error( __METHOD__, 'Permission error.' );
        }

        $params = $request->get_params();
        if ( isset( $params['file'] ) ) {
            $result = DT_Saturation_Mapping_Installer::import_by_file_name( $params['file'] );
            return $result;
        } else {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }
    }

    public function load_by_country( WP_REST_Request $request ) {

        if ( ! user_can( get_current_user_id(), 'manage_dt' ) ) {
            return new WP_Error( __METHOD__, 'Permission error.' );
        }

        $params = $request->get_params();
        if ( isset( $params['country_code'] ) ) {
            $result = DT_Saturation_Mapping_Installer::load_by_country( $params['country_code'] );
            return $result;
        } else {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }
    }

    public function install_admin1_geoname( WP_REST_Request $request ) {

        if ( ! user_can( get_current_user_id(), 'manage_dt' ) ) {
            return new WP_Error( __METHOD__, 'Permission error.' );
        }

        $params = $request->get_params();
        if ( isset( $params['geonameid'] ) ) {
            return DT_Saturation_Mapping_Installer::install_admin1_geoname( $params['geonameid'] );
        } else {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }
    }

    public function install_admin2_geoname( WP_REST_Request $request ) {

        if ( ! user_can( get_current_user_id(), 'manage_dt' ) ) {
            return new WP_Error( __METHOD__, 'Permission error.' );
        }

        $params = $request->get_params();
        if ( isset( $params['geonameid'] ) ) {
            return DT_Saturation_Mapping_Installer::install_admin2_geoname( $params['geonameid'] );
        } else {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }
    }


    public function load_current_locations( WP_REST_Request $request ) {

        if ( ! user_can( get_current_user_id(), 'manage_dt' ) ) {
            return new WP_Error( __METHOD__, 'Permission error.' );
        }

        return DT_Saturation_Mapping_Installer::load_current_locations();
    }
}
DT_Saturation_Mapping_Endpoints::instance();