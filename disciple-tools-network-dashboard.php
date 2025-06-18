<?php
/**
 *Plugin Name: Disciple.Tools - Network Dashboard
 * Plugin URI: https://github.com/DiscipleTools/disciple-tools-network-dashboard
 * Description: Connect this Disciple.Tools site to a larger network of sites. Adds security sensitive totals, mapping, activity logging.
 * Text Domain: disciple-tools-network-dashboard
 * Domain Path: /languages
 * Version: 2.10.4
 * Author URI: https://github.com/DiscipleTools
 * GitHub Plugin URI: https://github.com/DiscipleTools/disciple-tools-network-dashboard
 * Requires at least: 4.7.0
 * (Requires 4.7+ because of the integration of the REST API at 4.7 and the security requirements of this milestone version.)
 * Tested up to: 6.1
 * @package Disciple_Tools
 * @link    https://github.com/DiscipleTools
 * @license GPL-2.0 or later
 *          https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @version 2.2 Fixed filters on activity log.
 * @version 2.3 Fix to the map and filter data
 * @version 2.6 Banners
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
    return; // return if accessed directly
}

$dt_network_dashboard_required_dt_theme_version = '1.42.3';

/**
 * Gets the instance of the `DT_Network_Dashboard` class.
 *
 * @since  0.1
 * @access public
 * @return object|bool
 */
add_action( 'after_setup_theme', function () {
    global $dt_network_dashboard_required_dt_theme_version;
    $wp_theme = wp_get_theme();
    $version = $wp_theme->version;

    /*
     * Check if the Disciple.Tools theme is loaded and is the latest required version
     */
    $is_theme_dt = class_exists( "Disciple_Tools" );
    if ( $is_theme_dt && version_compare( $version, $dt_network_dashboard_required_dt_theme_version, "<" ) ) {
        if ( ! is_multisite() ) {
            add_action( 'admin_notices', 'dt_network_dashboard_admin_notice' );
            add_action( 'wp_ajax_dismissed_notice_handler', 'dt_hook_ajax_notice_handler' );
        }
        return false;
    }
    if ( ! $is_theme_dt ){
        return false;
    }

    /**
     * Load useful function from the theme
     */
    if ( !defined( 'DT_FUNCTIONS_READY' ) ){
        require_once get_template_directory() . '/dt-core/global-functions.php';
    }

    /**
     * We want to make sure migrations are run on updates.
     * @see https://www.sitepoint.com/wordpress-plugin-updates-right-way/
     *
     *      Note: this migration is for the Network Dashboard plugin. The migration for the mapping module
     *      is handled inside the /mapping-module/mapping.php file and migrations engine.
     */
    require_once( plugin_dir_path( __FILE__ ) . '/admin/migration-jobs.php' );
    try {
        require_once( plugin_dir_path( __FILE__ ) . '/admin/class-migration-engine.php' );
        DT_Network_Dashboard_Migration_Engine::migrate( DT_Network_Dashboard_Migration_Engine::$migration_number );
    } catch ( Throwable $e ) {
        new WP_Error( 'migration_error', 'Migration engine failed to migrate.' );
    }
    DT_Network_Dashboard_Migration_Engine::display_migration_and_lock();

    DT_Network_Dashboard::get_instance();

    /**
     * Use this action fires after the DT_Network_Dashboard plugin has loaded.
     * Use this to hook expansions to the metrics or snapshot collection.
     */
    do_action( 'dt_network_dashboard_loaded' );

    return true;
}, 50 );


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
        $this->dir_path = plugin_dir_path( __FILE__ );
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
        require_once( 'shared/endpoints-base.php' );
        require_once( 'shared/site-post-type.php' );
        require_once( 'shared/permissions.php' );
        require_once( 'shared/site-profile.php' );
        require_once( 'shared/actions.php' );

        require_once( 'collection/collection-queries.php' );
        require_once( 'collection/collection-endpoints.php' );

        // SNAPSHOTS
        require_once( 'snapshots/loader.php' );

        // LOGGING
        require_once( 'logging/loader.php' );

        // METRICS
        require_once( 'metrics/loader.php' );
        require_once( 'metrics/mapping-module-config.php' );

        // CRON
        if ( ! class_exists( 'Disciple_Tools_Async_Task' ) ) {
            require_once( get_theme_file_path() . '/dt-core/wp-async-request.php' ); // must load before cron
        }
        require_once( 'cron/loader.php' );

        if ( is_admin() ) {
            require_once( 'admin/menu-and-tabs.php' );
        }

        if ( 'yes' === get_option( 'dt_network_dashboard_dedicated' ) ) {
            add_filter( 'dt_front_page', function(){
                return home_url( '/network/' );
            } );
            add_filter( 'dt_non_standard_front_page', function(){
                return home_url( '/network/' );
            } );
        }


        add_filter( 'dt_set_roles_and_permissions', [ $this, 'dt_set_roles_and_permissions' ], 100, 1 );
    }

    public function dt_set_roles_and_permissions( $expected_roles ) {
        $expected_roles["network_dashboard_viewer"] = [
            "label" => __( 'Network Dashboard Viewer', 'disciple_tools' ),
            "description" => "Can view the network dashboard.",
            "permissions" => [
                'access_settings' => true,
                'view_project_metrics' => true,
            ],
            "order" => 100
        ];

        return $expected_roles;
    }

    /**
     * Sets up globals.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    private function setup() {
        // Main plugin directory path and URI.
        $this->dir_path     = trailingslashit( plugin_dir_path( __FILE__ ) );
        $this->dir_uri      = trailingslashit( plugin_dir_url( __FILE__ ) );

        // Admin and settings variables
        $this->token          = 'dt_network_dashboard';
        $this->version        = '2.1';

        global $wpdb;
        $wpdb->dt_movement_log = $wpdb->prefix . 'dt_movement_log';

        if ( ! class_exists( 'Site_Link_System' ) ){
            require_once( get_theme_file_path() . '/dt-core/admin/site-link-post-type.php' );
        }

    }

    /**
     * Sets up main plugin actions and filters.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    private function setup_actions() {

        // load internationalization
        add_action( 'after_setup_theme', array( $this, 'i18n' ), 51 );
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
    }

    /**
     * Loads the translation files.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function i18n() {
        $domain = 'disciple-tools-network-dashboard';
        $locale = apply_filters(
            'plugin_locale',
            ( is_admin() && function_exists( 'get_user_locale' ) ) ? get_user_locale() : get_locale(),
            $domain
        );

        $mo_file = $domain . '-' . $locale . '.mo';
        $path = realpath( dirname( __FILE__ ) . '/languages' );

        if ( $path && file_exists( $path ) ) {
            load_textdomain( $domain, $path . '/' . $mo_file );
        }
    }

    /**
     * Magic method to output a string if trying to use the object as a string.
     *
     * @since  0.1
     * @access public
     * @return string
     */
    public function __toString() {
        return 'disciple-tools-network-dashboard';
    }

    /**
     * Magic method to keep the object from being cloned.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, esc_html( 'Whoah, partner!' ), '0.1' );
    }

    /**
     * Magic method to keep the object from being unserialized.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, esc_html( 'Whoah, partner!' ), '0.1' );
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
        _doing_it_wrong( "dt_network_dashboard::{$method}", esc_html( 'Method does not exist.' ), '0.1' );
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
register_deactivation_hook( __FILE__, array( 'DT_Network_Dashboard', 'deactivation' ) );


/**
 * Admin alert for when Disciple.Tools Theme is not available
 */
function dt_network_dashboard_no_disciple_tools_theme_found() {
    ?>
    <div class="notice notice-error">
        <p><?php echo esc_html( "'Disciple.Tools - Network Dashboard' requires 'Disciple.Tools' theme to work. Please activate 'Disciple.Tools' theme or deactivate 'Disciple.Tools - Network Dashboard'." ); ?></p>
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
if ( ! function_exists( 'dt_write_log' ) ) {
    /**
     * A function to assist development only.
     * This function allows you to post a string, array, or object to the WP_DEBUG log.
     * It also prints elapsed time since the last call.
     *
     * @param $log
     */
    function dt_write_log( $log ) {
        if ( true === WP_DEBUG ) {
            global $dt_write_log_microtime;
            $now = microtime( true );
            if ( $dt_write_log_microtime > 0 ) {
                $elapsed_log = sprintf( '[elapsed:%5dms]', ( $now - $dt_write_log_microtime ) * 1000 );
            } else {
                $elapsed_log = '[elapsed:-------]';
            }
            $dt_write_log_microtime = $now;
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( $elapsed_log . ' ' . print_r( $log, true ) );
            } else {
                error_log( "$elapsed_log $log" );
            }
        }
    }
}

if ( ! function_exists( 'dt_is_child_theme_of_disciple_tools' ) ) {
    /**
     * Returns true if this is a child theme of Disciple.Tools, and false if it is not.
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
        global $dt_network_dashboard_required_dt_theme_version;
        // multiple dismissible notice states ?>
        <div class="notice notice-error notice-dt-network-dashboard is-dismissible" data-notice="dt-demo">
            <p><?php echo esc_html( sprintf( "'Disciple.Tools - Network Dashboard' requires 'Disciple.Tools' theme version %s to work. Please activate 'Disciple.Tools' theme or deactivate 'Disciple.Tools - Network Dashboard'.", $dt_network_dashboard_required_dt_theme_version ) ); ?></p>
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

if ( ! function_exists( 'recursive_sanitize_text_field' ) ){
    function recursive_sanitize_text_field( array $array ) {
        _deprecated_function( __FUNCTION__, '2.0', 'dt_recursive_sanitize_array()' );
        return dt_recursive_sanitize_array( $array );
    }
}


/**
 * Check for plugin updates even when the active theme is not Disciple.Tools
 *
 * Below is the publicly hosted .json file that carries the version information. This file can be hosted
 * anywhere as long as it is publicly accessible. You can download the version file listed below and use it as
 * a template.
 * Also, see the instructions for version updating to understand the steps involved.
 * @see https://github.com/DiscipleTools/disciple-tools-version-control/wiki/How-to-Update-the-Starter-Plugin
 */
add_action( 'plugins_loaded', function (){
    if ( is_admin() && !( is_multisite() && class_exists( "DT_Multisite" ) ) || wp_doing_cron() ){
        if ( ! class_exists( 'Puc_v4_Factory' ) ) {
            // find the Disciple.Tools theme and load the plugin update checker.
            foreach ( wp_get_themes() as $theme ){
                if ( $theme->get( 'TextDomain' ) === "disciple_tools" && file_exists( $theme->get_stylesheet_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' ) ){
                    require( $theme->get_stylesheet_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' );
                }
            }
        }
        if ( class_exists( 'Puc_v4_Factory' ) ){
            Puc_v4_Factory::buildUpdateChecker(
                'https://raw.githubusercontent.com/DiscipleTools/disciple-tools-network-dashboard/master/version-control.json',
                __FILE__,
                'disciple-tools-network-dashboard'
            );
        }
    }
});
