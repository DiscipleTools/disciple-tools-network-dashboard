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
        try {
            require_once( plugin_dir_path( __FILE__ ) . '/admin/class-migration-engine.php' );
            DT_Network_Dashboard_Migration_Engine::migrate( DT_Network_Dashboard_Migration_Engine::$migration_number );
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

        // SHARED RESOURCES
        require_once( 'shared/permissions.php' );
        require_once( 'shared/site-profile.php' );
        require_once( 'shared/wp-async-request.php' );
        require_once( 'shared/customize-site-linking.php' ); // loads capabilities
        require_once( 'shared/remove-top-nav-config.php' );
        require_once( 'shared/queries.php' );

        require_once('local-snapshot/snapshot-endpoints.php');
        require_once('local-snapshot/snapshot-report.php');
        require_once('local-snapshot/snapshot-queries.php');

        require_once( 'activity/activity-log.php');
        require_once( 'activity/hooks.php');

        // UI METRICS FOR NETWORK TAB
        require_once( 'ui/ui.php' );
        require_once( 'ui/mapbox-metrics.php' );
        require_once( 'ui/activity-metrics.php' );
        require_once( 'ui/mapping-module-config.php' );


        // CRON
        if ( file_exists( get_theme_file_path() . '/dt-core/wp-async-request.php' ) ) {
            require_once( get_theme_file_path() . '/dt-core/wp-async-request.php' ); // must load before cron
            require_once( get_theme_file_path() . '/dt-core/admin/site-link-post-type.php' ); // must load before cron

            require_once( 'cron/cron-log.php' );
            require_once( 'cron/cron-admin.php');
            require_once( 'cron/cron-1-build-snapshot.php' );
            if ( dt_is_current_multisite_dashboard_approved() ) { // load if approved for multisite collection
                require_once('cron/cron-2-build-multisite-snapshot.php');
            }
            require_once( 'cron/cron-3-push-snapshot.php' );
            require_once( 'cron/cron-4-trigger-remote-sites.php' );
            require_once( 'cron/cron-5-collect-remote-sites.php' );

        }

        require_once( 'admin/menu-and-tabs-endpoints.php' );
        if ( is_admin() ) {
            require_once( 'admin/menu-and-tabs.php' );
            require_once( 'admin/metabox-site-profile.php' );
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
        $this->token          = 'dt_network_dashboard';
        $this->version        = '2.0';

        global $wpdb;
        $wpdb->dt_movement_log = $wpdb->prefix . 'dt_movement_log';
        $wpdb->dt_movement_log_meta = $wpdb->prefix . 'dt_movement_log_meta';

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
        delete_option( 'dismissed-dt-network-dashboard' );

//        // delete cron jobs
//        $timestamp = wp_next_scheduled( 'bl_cron_hook' );
//        wp_unschedule_event( $timestamp, 'bl_cron_hook' );
    }

    /**
     * Loads the translation files.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function i18n() {
        load_plugin_textdomain( 'dt_network_dashboard', false,
            trailingslashit( dirname( plugin_basename( __FILE__ ) ) ). 'languages' );
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


/**
 * Admin alert for when Disciple Tools Theme is not available
 */
function dt_network_dashboard_no_disciple_tools_theme_found() {
    ?>
    <div class="notice notice-error">
        <p><?php esc_html_e( "'Disciple Tools - Network Dashboard' requires 'Disciple Tools' theme to work. Please activate 'Disciple Tools' theme or deactivate 'Disciple Tools - Network Dashboard' plugin.", "dt_network_dashboard" ); ?></p>
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

function dt_network_dashboard_admin_notice() {
    // Check if it's been dismissed...
    if ( ! get_option( 'dismissed-dt-network-dashboard', false ) ) {
        // multiple dismissible notice states ?>
        <div class="notice notice-error notice-dt-network-dashboard is-dismissible" data-notice="dt-demo">
            <p><?php esc_html_e( "'Disciple Tools - Network Dashboard' requires 'Disciple Tools' theme to work. Please activate 'Disciple Tools' theme or deactivate 'Disciple Tools - Network Dashboard'." ); ?></p>
        </div>
        <script>
            jQuery(function($) {
                $( document ).on( 'click', '.notice-dt-network-dashboard .notice-dismiss', function () {
                    let type = $( this ).closest( '.notice-dt-network-dashboard' ).data( 'notice' );
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
function dt_network_dashboard_ajax_notice_handler() {
    $type = 'dt-network-dashboard';
    update_option( 'dismissed-' . $type, true );
}