<?php
/**
 * Plugin Name: Disciple Tools - Saturation Mapping
 * Plugin URI: https://github.com/ZumeProject/disciple-tools-saturation-mapping
 * Description: Adds saturation mapping data.
 * Version: 0.1
 * Author URI: https://github.com/DiscipleTools
 * GitHub Plugin URI: https://github.com/DiscipleTools/disciple-tools-saturation-mapping
 * Requires at least: 4.7.0
 * (Requires 4.7+ because of the integration of the REST API at 4.7 and the security requirements of this milestone version.)
 * Tested up to: 4.9
 * @package Disciple_Tools
 * @link    https://github.com/DiscipleTools
 * @license GPL-2.0 or later
 *          https://www.gnu.org/licenses/gpl-2.0.html
 */
if ( !defined( 'ABSPATH' ) ) {
    return; // return unless accessed directly
}

/**
 * Gets the instance of the `DT_Saturation_Mapping` class.
 *
 * @since  0.1
 * @access public
 * @return object
 */
function dt_saturation_mapping() {
    $current_theme = get_option( 'current_theme' );

    if ( 'Disciple Tools' == $current_theme || dt_is_child_theme_of_disciple_tools() ) {

        /**
         * We want to make sure migrations are run on updates.
         *
         * @see https://www.sitepoint.com/wordpress-plugin-updates-right-way/
         */
        $migration_number = 0;
        try {

            require_once( plugin_dir_path( __FILE__ ) . '/includes/admin/class-migration-engine.php' );
            DT_Saturation_Mapping_Migration_Engine::migrate( $migration_number );
        } catch ( Throwable $e ) {
            new WP_Error( 'migration_error', 'Migration engine failed to migrate.' );
        }


        return DT_Saturation_Mapping::get_instance();
    }
    else {
        add_action( 'admin_notices', 'dt_saturation_mapping_admin_notice' );
        add_action( 'wp_ajax_dismissed_notice_handler', 'dt_saturation_mapping_ajax_notice_handler' );
        return new WP_Error( 'current_theme_not_dt', 'Disciple Tools Theme not active.' );
    }

}
add_action( 'plugins_loaded', 'dt_saturation_mapping' );

/**
 * Singleton class for setting up the plugin.
 *
 * @since  0.1
 * @access public
 */
class DT_Saturation_Mapping {

    /**
     * Declares public variables
     *
     * @since  0.1
     * @access public
     * @return object
     */
    public $token;
    public $version;
    public $dir_path = '';
    public $dir_uri = '';
    public $img_uri = '';
    public $includes_path;

    /**
     * Returns the instance.
     *
     * @since  0.1
     * @access public
     * @return object
     */
    public static function get_instance() {

        static $instance = null;

        if ( is_null( $instance ) ) {
            $instance = new dt_saturation_mapping();
            $instance->setup();
            $instance->includes();
            $instance->setup_actions();
        }
        return $instance;
    }

    /**
     * Constructor method.
     *
     * @since  0.1
     * @access private
     * @return void
     */
    private function __construct() {
    }

    /**
     * Loads files needed by the plugin.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    private function includes() {

        require_once( 'includes/core.php' );
        require_once( 'includes/local-endpoints.php' );
        require_once( 'includes/network-endpoints.php' );
        require_once( 'install/installer.php' );
        require_once( 'includes/stats.php' );

        if ( get_option( 'dt_saturation_mapping_enable_network' ) ) {
            require_once( 'includes/hooks.php' );

        }
        if ( is_admin() ) {
            require_once( 'includes/menu-and-tabs.php' );
            require_once( 'includes/locations-saturation-metabox.php' );

        }
    }

    /**
     * Sets up globals.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    private function setup() {
        global $wpdb;
        // Main plugin directory path and URI.
        $this->dir_path     = trailingslashit( plugin_dir_path( __FILE__ ) );
        $this->dir_uri      = trailingslashit( plugin_dir_url( __FILE__ ) );

        // Plugin directory paths.
        $this->includes_path      = trailingslashit( $this->dir_path . 'includes' );

        // Plugin directory URIs.
        $this->img_uri      = trailingslashit( $this->dir_uri . 'img' );

        // Admin and settings variables
        $this->token             = 'dt_saturation_mapping';
        $this->version             = '0.1';

        $wpdb->dt_geonames = $wpdb->prefix . 'dt_geonames';
        $wpdb->dt_geonames_polygons = $wpdb->prefix . 'dt_geonames_polygons';
        $wpdb->dt_network_reports = $wpdb->prefix . 'dt_network_reports';
        $wpdb->dt_network_reportmeta = $wpdb->prefix . 'dt_network_reportmeta';

    }

    /**
     * Sets up main plugin actions and filters.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    private function setup_actions() {

        // Check for plugin updates
        if ( ! class_exists( 'Puc_v4_Factory' ) ) {
            require( $this->includes_path . 'admin/libraries/plugin-update-checker/plugin-update-checker.php' );
        }
        /**
         * Below is the publicly hosted .json file that carries the version information. This file can be hosted
         * anywhere as long as it is publicly accessible. You can download the version file listed below and use it as
         * a template.
         * Also, see the instructions for version updating to understand the steps involved.
         * @see https://github.com/DiscipleTools/disciple-tools-version-control/wiki/How-to-Update-the-Starter-Plugin
         */
        Puc_v4_Factory::buildUpdateChecker(
            'https://raw.githubusercontent.com/DiscipleTools/disciple-tools-version-control/master/disciple-tools-saturation-mapping-version-control.json',
            __FILE__,
            'disciple-tools-starter-plugin'
        );

        // Internationalize the text strings used.
        add_action( 'plugins_loaded', array( $this, 'i18n' ), 2 );
    }

    /**
     * Method that runs only when the plugin is activated.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public static function activation() {

        // Confirm 'Administrator' has 'manage_dt' privilege. This is key in 'remote' configuration when
        // Disciple Tools theme is not installed, otherwise this will already have been installed by the Disciple Tools Theme


//        /**
//         * Add custom tables
//         */
//        global $wpdb;
//        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
//
//        // Add tables
//        $charset_collate = $wpdb->get_charset_collate();
//
//        $sql1 = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dt_geonames` (
//          `geonameid` bigint(11) unsigned NOT NULL,
//          `name` varchar(200) DEFAULT NULL,
//          `asciiname` varchar(200) DEFAULT NULL,
//          `alternatenames` varchar(10000) DEFAULT NULL,
//          `latitude` float DEFAULT NULL,
//          `longitude` float DEFAULT NULL,
//          `feature_class` char(1) DEFAULT NULL,
//          `feature_code` varchar(10) DEFAULT NULL,
//          `country_code` char(2) DEFAULT NULL,
//          `cc2` varchar(100) DEFAULT NULL,
//          `admin1_code` varchar(20) DEFAULT NULL,
//          `admin2_code` varchar(80) DEFAULT NULL,
//          `admin3_code` varchar(20) DEFAULT NULL,
//          `admin4_code` varchar(20) DEFAULT NULL,
//          `population` int(11) DEFAULT NULL,
//          `elevation` int(80) DEFAULT NULL,
//          `dem` varchar(80) DEFAULT NULL,
//          `timezone` varchar(40) DEFAULT NULL,
//          `modification_date` date DEFAULT NULL,
//          PRIMARY KEY (`geonameid`),
//          FULLTEXT KEY `feature_class` (`feature_class`),
//          FULLTEXT KEY `feature_code` (`feature_code`),
//          FULLTEXT KEY `country_code` (`country_code`),
//          FULLTEXT KEY `admin1_code` (`admin1_code`),
//          FULLTEXT KEY `admin2_code` (`admin2_code`)
//        ) $charset_collate;";
//        $result1 = dbDelta( $sql1 );
//        dt_write_log( $result1 );
//
//        $sql2 = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dt_geonames_polygons` (
//          `geonameid` bigint(11) unsigned NOT NULL,
//          `geoJSON` longtext,
//          PRIMARY KEY (`geonameid`)
//        ) $charset_collate;";
//
//        $result2 = dbDelta( $sql2 );
//        dt_write_log( $result2 );
//
//        /**
//         * Install initial country, admin1, and admin2 data
//         */
//        $wpdb->dt_geonames = $wpdb->prefix . 'dt_geonames';
//        require_once( 'install/installer.php' );
//        DT_Saturation_Mapping_Installer::install_world_admin_set();
//
//
//        /**
//         * Setup variables
//         */
//        update_option( 'dt_saturation_mapping_pd', 5000, false );
//
//        /**
//         * Initialize partner profile
//         */
//        $partner_profile = [
//            'partner_name' => get_option( 'blogname' ),
//            'partner_description' => get_option( 'blogdescription' ),
//            'partner_id' => DT_Saturation_Mapping::get_unique_public_key(),
//        ];
//        update_option( 'dt_site_partner_profile', $partner_profile, false );
    }

    /**
     * Method that runs only when the plugin is deactivated.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public static function deactivation() {
        delete_option( 'dismissed-dt-starter' );
    }

    /**
     * Loads the translation files.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function i18n() {
        load_plugin_textdomain( 'dt_saturation_mapping', false, trailingslashit( dirname( plugin_basename( __FILE__ ) ) ). 'languages' );
    }

    /**
     * Magic method to output a string if trying to use the object as a string.
     *
     * @since  0.1
     * @access public
     * @return string
     */
    public function __toString() {
        return 'dt_saturation_mapping';
    }

    /**
     * Magic method to keep the object from being cloned.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, esc_html__( 'Whoah, partner!', 'dt_saturation_mapping' ), '0.1' );
    }

    /**
     * Magic method to keep the object from being unserialized.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, esc_html__( 'Whoah, partner!', 'dt_saturation_mapping' ), '0.1' );
    }

    /**
     * Magic method to prevent a fatal error when calling a method that doesn't exist.
     *
     * @since  0.1
     * @access public
     * @return null
     */
    public function __call( $method = '', $args = array() ) {
        // @codingStandardsIgnoreLine
        _doing_it_wrong( "dt_saturation_mapping::{$method}", esc_html__( 'Method does not exist.', 'dt_saturation_mapping' ), '0.1' );
        unset( $method, $args );
        return null;
    }

    public static function get_unique_public_key() {
        $key = bin2hex( random_bytes( 64 ) );
        $key = str_replace( '0', '', $key );
        $key = str_replace( 'O', '', $key );
        $key = str_replace( 'o', '', $key );
        $key = strtoupper( substr( $key, 0, 5 ) );
        return $key;
    }
}
// end main plugin class

// Register activation hook.
//register_activation_hook( __FILE__, [ 'DT_Saturation_Mapping', 'activation' ] );
register_deactivation_hook( __FILE__, [ 'DT_Saturation_Mapping', 'deactivation' ] );

/**
 * Admin alert for when Disciple Tools Theme is not available
 */
function dt_saturation_mapping_no_disciple_tools_theme_found() {
    ?>
    <div class="notice notice-error">
        <p><?php esc_html_e( "'Disciple Tools - Saturation Mapping' requires 'Disciple Tools' theme to work. Please activate 'Disciple Tools' theme or deactivate 'Disciple Tools - Saturation Mapping' plugin.", "dt_saturation_mapping" ); ?></p>
    </div>
    <?php
}

/**
 * A simple function to assist with development and non-disruptive debugging.
 * -----------
 * -----------
 * REQUIREMENT:
 * WP Debug logging must be set to true in the wp-config.php file.
 * Add these definitions above the "That's all, stop editing! Happy blogging." line in wp-config.php
 * -----------
 * define( 'WP_DEBUG', true ); // Enable WP_DEBUG mode
 * define( 'WP_DEBUG_LOG', true ); // Enable Debug logging to the /wp-content/debug.log file
 * define( 'WP_DEBUG_DISPLAY', false ); // Disable display of errors and warnings
 * @ini_set( 'display_errors', 0 );
 * -----------
 * -----------
 * EXAMPLE USAGE:
 * (string)
 * write_log('THIS IS THE START OF MY CUSTOM DEBUG');
 * -----------
 * (array)
 * $an_array_of_things = ['an', 'array', 'of', 'things'];
 * write_log($an_array_of_things);
 * -----------
 * (object)
 * $an_object = new An_Object
 * write_log($an_object);
 */
if ( !function_exists( 'dt_write_log' ) ) {
    /**
     * A function to assist development only.
     * This function allows you to post a string, array, or object to the WP_DEBUG log.
     *
     * @param $log
     */
    function dt_write_log( $log ) {
        if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }
}

if ( ! function_exists( 'dt_is_child_theme_of_disciple_tools' ) ) {
    /**
     * Returns true if this is a child theme of Disciple Tools, and false if it is not.
     *
     * @return bool
     */
    function dt_is_child_theme_of_disciple_tools() : bool {
        if ( get_template_directory() !== get_stylesheet_directory() ) {
            $current_theme = wp_get_theme();
            if ( 'disciple-tools-theme' == $current_theme->get( 'Template' ) ) {
                return true;
            }
        }
        return false;
    }
}

function dt_saturation_mapping_admin_notice() {
    // Check if it's been dismissed...
    if ( ! get_option( 'dismissed-dt-starter', false ) ) {
        // multiple dismissible notice states ?>
        <div class="notice notice-error notice-dt-starter is-dismissible" data-notice="dt-demo">
            <p><?php esc_html_e( "'Disciple Tools - Saturation Mapping' requires 'Disciple Tools' theme to work. Please activate 'Disciple Tools' theme or deactivate 'Disciple Tools - Saturation Mapping'." ); ?></p>
        </div>
        <script>
            jQuery(function($) {
                $( document ).on( 'click', '.notice-dt-starter .notice-dismiss', function () {
                    let type = $( this ).closest( '.notice-dt-starter' ).data( 'notice' );
                    $.ajax( ajaxurl,
                        {
                            type: 'POST',
                            data: {
                                action: 'dismissed_notice_handler',
                                type: type,
                            }
                        } );
                } );
            });
        </script>

    <?php }
}

/**
 * AJAX handler to store the state of dismissible notices.
 */
function dt_saturation_mapping_ajax_notice_handler() {
    $type = 'dt-starter';
    update_option( 'dismissed-' . $type, true );
}