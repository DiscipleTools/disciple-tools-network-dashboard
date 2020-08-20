<?php
/**
 * Plugin Name: Disciple Tools - Network Dashboard
 * Plugin URI: https://github.com/DiscipleTools/disciple-tools-network-dashboard
 * Description: Connect this Disciple Tools site to a larger network of sites. Adds security sensitive totals, mapping, activity logging.
 * Version: 2.0
 * Author URI: https://github.com/DiscipleTools
 * GitHub Plugin URI: https://github.com/DiscipleTools/disciple-tools-network-dashboard
 * Requires at least: 4.7.0
 * (Requires 4.7+ because of the integration of the REST API at 4.7 and the security requirements of this milestone version.)
 * Tested up to: 4.9
 * @package Disciple_Tools
 * @link    https://github.com/DiscipleTools
 * @license GPL-2.0 or later
 *          https://www.gnu.org/licenses/gpl-2.0.html
 */
if ( ! defined( 'ABSPATH' ) ) {
    return; // return if accessed directly
}

/**
 * Gets the instance of the `DT_Network_Dashboard` class.
 *
 * @since  0.1
 * @access public
 * @return object|bool
 */
function dt_network_dashboard() {
    $current_theme = get_option( 'current_theme' );

    if ( 'Disciple Tools' == $current_theme || dt_is_child_theme_of_disciple_tools() ) {

        /**
         * We want to make sure migrations are run on updates.
         * @see https://www.sitepoint.com/wordpress-plugin-updates-right-way/
         *
         *      Note: this migration is for the Network Dashboard plugin. The migration for the mapping module
         *      is handled inside the /mapping-module/mapping.php file and migrations engine.
         */
        $migration_number = 2;
        try {
            require_once( plugin_dir_path( __FILE__ ) . '/admin/class-migration-engine.php' );
            DT_Network_Dashboard_Migration_Engine::migrate( $migration_number );
        } catch ( Throwable $e ) {
            new WP_Error( 'migration_error', 'Migration engine failed to migrate.' );
        }

        return DT_Network_Dashboard::get_instance();
    }
    else {
        if ( ! is_multisite() ) {
            add_action('admin_notices', 'dt_network_dashboard_admin_notice');
            add_action('wp_ajax_dismissed_notice_handler', 'dt_network_dashboard_ajax_notice_handler');
        }

        return false;
    }

}
add_action( 'after_setup_theme', 'dt_network_dashboard' );

/**
 * Singleton class for setting up the plugin.
 *
 * @since  0.1
 * @access public
 */
class DT_Network_Dashboard {

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
    public $admin_path;

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
            $instance = new dt_network_dashboard();
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

        require_once( 'admin/functions.php' );
        require_once( 'admin/site-profile.php' );

        require_once( 'v1/network-endpoints.php' );
        require_once( 'v1/network.php' );
        require_once( 'v1/network-queries.php' );

        require_once( 'admin/permissions.php' );
        require_once( 'admin/queries.php' );
        require_once( 'admin/customize-site-linking.php' ); // loads capabilities

        require_once( 'admin/multisite.php' );

        require_once( 'admin/remove-top-nav-config.php' );

        // adds charts and metrics to the network tab
        require_once( 'ui/ui.php' );
        require_once( 'ui/mapbox-metrics.php' );

        require_once( 'cron/cron-log.php' );

        require_once( 'admin/mapping-module-config.php' );


        if ( file_exists( get_theme_file_path() . '/dt-core/wp-async-request.php' ) ) {
            require_once( get_theme_file_path() . '/dt-core/wp-async-request.php' ); // must load before cron
            require_once( get_theme_file_path() . '/dt-core/admin/site-link-post-type.php' ); // must load before cron
            require_once( 'cron/cron-get-remote-snapshots.php' );

            new DT_Network_Cron_Scheduler();

            try {
                new DT_Get_Sites_SnapShot_Async();
            } catch ( Exception $e ) {
                dt_write_log( $e );
            }

            // load if approved for multisite collection
            if ( dt_is_current_multisite_dashboard_approved() ) {
                require_once( 'cron/cron-get-multisite-snapshots.php' );

                new DT_Network_Multisite_Cron_Scheduler();

                try {
                    new DT_Get_Network_Multisite_SnapShot_Async();
                } catch ( Exception $e ) {
                    dt_write_log( $e );
                }
            }
        }

        require_once('admin/menu-and-tabs-endpoints.php');
        if ( is_admin() ) {
            require_once( 'admin/menu-and-tabs.php' );
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
        $this->admin_path      = trailingslashit( $this->dir_path . 'admin' );

        // Plugin directory URIs.
        $this->img_uri      = trailingslashit( $this->dir_uri . 'img' );

        // Admin and settings variables
        $this->token             = 'dt_network_dashboard';
        $this->version             = '2.0';

        global $wpdb;
        $wpdb->movement_log = 'movement_log';

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
            require( get_template_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' );
        }
        /**
         * Below is the publicly hosted .json file that carries the version information. This file can be hosted
         * anywhere as long as it is publicly accessible. You can download the version file listed below and use it as
         * a template.
         * Also, see the instructions for version updating to understand the steps involved.
         * @see https://github.com/DiscipleTools/disciple-tools-version-control/wiki/How-to-Update-the-Starter-Plugin
         */
        Puc_v4_Factory::buildUpdateChecker(
            'https://raw.githubusercontent.com/DiscipleTools/disciple-tools-version-control/master/disciple-tools-network-dashboard-version-control.json',
            __FILE__,
            'disciple-tools-network-dashboard'
        );

        // Internationalize the text strings used.
        add_action( 'plugins_loaded', array( $this, 'i18n' ), 2 );

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
        load_plugin_textdomain( 'dt_network_dashboard', false, trailingslashit( dirname( plugin_basename( __FILE__ ) ) ). 'languages' );
    }

    /**
     * Magic method to output a string if trying to use the object as a string.
     *
     * @since  0.1
     * @access public
     * @return string
     */
    public function __toString() {
        return 'dt_network_dashboard';
    }

    /**
     * Magic method to keep the object from being cloned.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, esc_html__( 'Whoah, partner!', 'dt_network_dashboard' ), '0.1' );
    }

    /**
     * Magic method to keep the object from being unserialized.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, esc_html__( 'Whoah, partner!', 'dt_network_dashboard' ), '0.1' );
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
        _doing_it_wrong( "dt_network_dashboard::{$method}", esc_html__( 'Method does not exist.', 'dt_network_dashboard' ), '0.1' );
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
register_deactivation_hook( __FILE__, [ 'DT_Network_Dashboard', 'deactivation' ] );