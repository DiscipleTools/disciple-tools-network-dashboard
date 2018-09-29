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
 * Class DT_Network_Dashboard_Installer_Endpoints
 */
class DT_Network_Dashboard_Installer_Endpoints
{

    private $version = 1;
    private $context = "dt";
    private $namespace;

    /**
     * DT_Network_Dashboard_Installer_Endpoints The single instance of DT_Network_Dashboard_Installer_Endpoints.
     *
     * @var     object
     * @access    private
     * @since     0.1.0
     */
    private static $_instance = null;

    /**
     * Main DT_Network_Dashboard_Installer_Endpoints Instance
     * Ensures only one instance of DT_Network_Dashboard_Installer_Endpoints is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return DT_Network_Dashboard_Installer_Endpoints instance
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
            $this->namespace, '/network/import', [
                'methods'  => 'POST',
                'callback' => [ $this, 'import' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/network/download', [
                'methods'  => 'POST',
                'callback' => [ $this, 'download' ],
            ]
        );

        // local install endpoints
        register_rest_route(
            $this->namespace, '/network/load_by_country', [
                'methods'  => 'POST',
                'callback' => [ $this, 'load_by_country' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/network/install_admin2_geoname', [
                'methods'  => 'POST',
                'callback' => [ $this, 'install_admin2_geoname' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/network/install_admin1_geoname', [
                'methods'  => 'POST',
                'callback' => [ $this, 'install_admin1_geoname' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/network/load_current_locations', [
                'methods'  => 'POST',
                'callback' => [ $this, 'load_current_locations' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/network/load_cities', [
                'methods'  => 'POST',
                'callback' => [ $this, 'load_cities' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/network/install_single_city', [
                'methods'  => 'POST',
                'callback' => [ $this, 'install_single_city' ],
            ]
        );

    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return bool|array|WP_Error
     */
    public function import( WP_REST_Request $request ) {

        if ( ! user_can( get_current_user_id(), 'manage_dt' ) ) {
            return new WP_Error( __METHOD__, 'Permission error.' );
        }

        $params = $request->get_params();
        if ( ! isset( $params['type'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }

        switch ( $params['type'] ) {

            case 'geonames_basic':
                return DT_Network_Dashboard_Installer::install_world_admin_set();
                break;

            case 'load_by_country':
                if ( isset( $params['country_code'] ) ) {
                    $result = DT_Network_Dashboard_Installer::load_by_country( $params['country_code'] );
                    return $result;
                } else {
                    return new WP_Error( __METHOD__, 'Missing parameters.' );
                }
                break;

            case 'install_admin1_geoname':
                if ( isset( $params['geonameid'] ) ) {
                    return DT_Network_Dashboard_Installer::install_admin1_by_geoname( $params['geonameid'] );
                } else {
                    return new WP_Error( __METHOD__, 'Missing parameters.' );
                }
                break;

            case 'install_admin2_geoname':
                if ( isset( $params['geonameid'] ) ) {
                    return DT_Network_Dashboard_Installer::install_admin2_by_geoname( $params['geonameid'] );
                } else {
                    return new WP_Error( __METHOD__, 'Missing parameters.' );
                }
                break;

            case 'load_cities':
                if ( isset( $params['geonameid'] ) ) {
                    $result = DT_Network_Dashboard_Installer::load_cities( $params['geonameid'] );
                    if ( $result['status'] ) {
                        return $result;
                    } else {
                        return new WP_Error( 'install_cities', $result['message'], $result );
                    }
                } else {
                    return new WP_Error( __METHOD__, 'Missing parameters.' );
                }
                break;

            case 'install_single_city':
                if ( isset( $params['geonameid'] ) && $params['admin2'] ) {
                    $result = DT_Network_Dashboard_Installer::install_single_city( $params['geonameid'], $params['admin2'] );
                    if ( $result['status'] ) {
                        return $result;
                    } else {
                        return new WP_Error( 'install_cities', $result['message'], $result );
                    }
                } else {
                    return new WP_Error( __METHOD__, 'Missing parameters.' );
                }
                break;

            case 'load_current_locations':
                return DT_Network_Dashboard_Installer::load_current_locations();
                break;

            default:
                return false;
                break;
        }
    }



    public function load_by_country( WP_REST_Request $request ) {

        if ( ! user_can( get_current_user_id(), 'manage_dt' ) ) {
            return new WP_Error( __METHOD__, 'Permission error.' );
        }

        $params = $request->get_params();
        if ( isset( $params['country_code'] ) ) {
            $result = DT_Network_Dashboard_Installer::load_by_country( $params['country_code'] );
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
            return DT_Network_Dashboard_Installer::install_admin1_by_geoname( $params['geonameid'] );
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
            return DT_Network_Dashboard_Installer::install_admin2_by_geoname( $params['geonameid'] );
        } else {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }
    }

    public function load_cities( WP_REST_Request $request ) {

        if ( ! user_can( get_current_user_id(), 'manage_dt' ) ) {
            return new WP_Error( __METHOD__, 'Permission error.' );
        }

        $params = $request->get_params();
        if ( isset( $params['geonameid'] ) ) {
            $result = DT_Network_Dashboard_Installer::load_cities( $params['geonameid'] );
            if ( $result['status'] ) {
                return $result;
            } else {
                return new WP_Error( 'install_cities', $result['message'], $result );
            }
        } else {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }
    }

    public function install_single_city( WP_REST_Request $request ) {

        if ( ! user_can( get_current_user_id(), 'manage_dt' ) ) {
            return new WP_Error( __METHOD__, 'Permission error.' );
        }

        $params = $request->get_params();
        if ( isset( $params['geonameid'] ) && $params['admin2'] ) {
            $result = DT_Network_Dashboard_Installer::install_single_city( $params['geonameid'], $params['admin2'] );
            if ( $result['status'] ) {
                return $result;
            } else {
                return new WP_Error( 'install_cities', $result['message'], $result );
            }
        } else {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }
    }

    public function load_current_locations( WP_REST_Request $request ) {

        if ( ! user_can( get_current_user_id(), 'manage_dt' ) ) {
            return new WP_Error( __METHOD__, 'Permission error.' );
        }

        return DT_Network_Dashboard_Installer::load_current_locations();
    }

    public function download( WP_REST_Request $request ) {

        if ( ! user_can( get_current_user_id(), 'manage_dt' ) ) {
            return new WP_Error( __METHOD__, 'Permission error.' );
        }

        $params = $request->get_params();
        if ( ! isset( $params['country_code'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }

        return DT_Network_Dashboard_Installer::get_geonames_zip_download( $params['country_code'] );
    }
}
DT_Network_Dashboard_Installer_Endpoints::instance();