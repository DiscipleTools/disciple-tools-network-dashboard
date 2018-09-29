<?php
/**
 * DT_Network_Dashboard_Menu class for the admin page
 *
 * @class       DT_Network_Dashboard_Menu
 * @version     0.1.0
 * @since       0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}

/**
 * Initialize menu class
 */
DT_Network_Dashboard_Menu::instance();

/**
 * Class DT_Network_Dashboard_Menu
 */
class DT_Network_Dashboard_Menu {

    public $token = 'dt_network_dashboard';

    private static $_instance = null;

    /**
     * DT_Network_Dashboard_Menu Instance
     *
     * Ensures only one instance of DT_Network_Dashboard_Menu is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return DT_Network_Dashboard_Menu instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {
        add_action( "admin_menu", array( $this, "register_menu" ) );
        add_action("admin_head", [ $this, 'header_script' ] );
    } // End __construct()

    /**
     * Loads the subnav page
     * @since 0.1
     */
    public function register_menu() {
        add_menu_page( __( 'Extensions (DT)', 'disciple_tools' ), __( 'Extensions (DT)', 'disciple_tools' ),
        'manage_dt', 'dt_extensions', [ $this, 'extensions_menu' ], 'dashicons-admin-generic', 59 );
        add_submenu_page( 'dt_extensions', __( 'Network Dashboard', 'dt_network_dashboard' ),
        __( 'Network Dashboard', 'dt_network_dashboard' ), 'manage_dt', $this->token, [ $this, 'content' ] );
    }

    public function header_script() {
        ?>
        <style>
            a.pointer { cursor: pointer; }
        </style>
        <script>
            jQuery(document).ready(function(){

            })
        </script>
        <?php
    }

    /**
     * Menu stub. Replaced when Disciple Tools Theme fully loads.
     */
    public function extensions_menu() {}

    /**
     * Builds page contents
     * @since 0.1
     */
    public function content() {

        if ( !current_user_can( 'manage_dt' ) ) { // manage dt is a permission that is specific to Disciple Tools and allows admins, strategists and dispatchers into the wp-admin
            wp_die( esc_attr__( 'You do not have sufficient permissions to access this page.' ) );
        }

        if ( isset( $_GET["tab"] ) ) {
            $tab = sanitize_key( wp_unslash( $_GET["tab"] ) );
        } else {
            $tab = 'general';
        }

        $link = 'admin.php?page='.$this->token.'&tab=';

        ?>
        <div class="wrap">
            <h2><?php esc_attr_e( 'Network Dashboard', 'dt_network_dashboard' ) ?></h2>
            <h2 class="nav-tab-wrapper">
                <a href="<?php echo esc_attr( $link ) . 'general' ?>" class="nav-tab
                <?php ( $tab == 'general' || ! isset( $tab ) ) ? esc_attr_e( 'nav-tab-active', 'dt_network_dashboard' ) : print ''; ?>">
                    <?php esc_attr_e( 'Overview', 'dt_network_dashboard' ) ?></a>

                <a href="<?php echo esc_attr( $link ) . 'network' ?>" class="nav-tab
                <?php ( $tab == 'network' ) ? esc_attr_e( 'nav-tab-active', 'dt_network_dashboard' ) : print ''; ?>">
                    <?php esc_attr_e( 'Install Network Locations', 'dt_network_dashboard' ) ?></a>
            </h2>

            <?php
            switch ($tab) {
                case "general":
                    $object = new DT_Network_Dashboard_Tab_General();
                    $object->content();
                    break;
                case "network":
                    $object = new DT_Network_Dashboard_Tab_Network();
                    $object->content();
                    break;
                default:
                    break;
            }
            ?>
        </div><!-- End wrap -->
        <?php
    }
}

// Enqueues the admin scripts
add_action( 'admin_enqueue_scripts', 'dt_network_dashboard_options_scripts' );
function dt_network_dashboard_options_scripts() {
    global $post;
    if ( ( isset( $_GET["page"] ) && $_GET["page"] === 'dt_network_dashboard' ) || ( isset( $post->post_type ) && 'locations' === $post->post_type ) ) {
        wp_enqueue_script( 'dt_network_dashboard_options', plugin_dir_url( __FILE__ ) . 'menu-and-tabs.js', [
            'jquery',
            'jquery-ui-core',
        ], filemtime( plugin_dir_path( __FILE__ ) . 'menu-and-tabs.js' ), true );
        wp_localize_script(
            "dt_network_dashboard_options", "dtSMOptionAPI", array(
                'root' => esc_url_raw( rest_url() ),
                'plugin_uri' => plugin_dir_url( __DIR__ ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'map_key' => dt_get_option( 'map_key' ),
                'spinner' => ' <img src="'. plugin_dir_url( __FILE__ ) . '/spinner.svg" width="12px" />',
            )
        );
    }
}


/**
 * Class DT_Starter_Tab_Second
 */
class DT_Network_Dashboard_Tab_General
{
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->overview_message() ?>
                        <?php $this->population_metabox() ?>
                        <?php $this->install_basics() ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->

                        <!-- End Right Column -->
                    </div><!-- postbox-container 1 -->
                    <div id="postbox-container-2" class="postbox-container">
                    </div><!-- postbox-container 2 -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function overview_message() {
        ?>
        <style>dt { font-weight:bold;}</style>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
            <th>Overview of Plugin</th>
            </thead>
            <tbody>
            <tr>
                <td>
                    <dl>
                        <dt>Plugin Purpose</dt>
                        <dd>Collecting reports across many systems is difficult and doing it automatically, even more so. Making sure
                        counts for certain location are counted only once you need a shared database of locations to post counts to.
                        This network mapping plugin attempts to set up a globally consistent mapping schema.</dd>

                        <dt>Local vs Network Functions</dt>
                        <dd>This plugin has two functions.
                            <ol>
                                <li> First to extend Disciple Tools with structured mapping data
                                    and to make it easy to install those locations for a team to use as they reach out to a certain area.
                                </li>
                                <li>This plugin also has the ability to add a network (global) dashboard to Disciple Tools for
                                multiple Disciple Tools teams to connect their systems and share reporting (i.e. celebration) of the
                                work between them.
                                </li>
                            </ol>
                        </dd>

                    </dl>

                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }

    public function population_metabox() {
        // process post action
        if ( isset( $_POST['population_division'] ) && ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'population_division'.get_current_user_id() ) ) ) {
            $new = (int) sanitize_text_field( wp_unslash( $_POST['population_division'] ) );
            update_option( 'dt_network_dashboard_population', $new, false );
        }
        $population_division = get_option( 'dt_network_dashboard_population' );
        if ( empty( $population_division ) ) {
            update_option( 'dt_network_dashboard_population', 5000, false );
            $population_division = 5000;
        }
        ?>
        <!-- Box -->
        <form method="post">
            <table class="widefat striped">
                <thead>
                <th>Groups Per Population</th>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <?php wp_nonce_field( 'population_division'.get_current_user_id() ); ?>
                        <label for="population_division">Size of population for each group: </label>
                        <input type="number" class="text" id="population_division" name="population_division" value="<?php echo esc_attr( $population_division ); ?>" /><br>
                        <p><em>Default is a population of 5,000 for each group. This must be a number and must not be blank. </em></p>
                        <button type="submit" class="button">Update</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
        <br>
        <!-- End Box -->
        <?php
    }

    public function install_basics() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
            <th>Install Geoname Data</th>
            </thead>
            <tbody>
            <tr>
                <td>
                    <h2>Install Geonames</h2>
                    <a class="button pointer" id="geonames_basic" onclick="install_geonames('geonames_basic')">Install</a>

                </td>
            </tr>
            <tr>
                <td>
                    <h2>Install Geonames Polygons</h2>

                </td>
            </tr>
            <tr>
                <td>
                    <h2>test download</h2>
                    <a class="button pointer" id="test_download" onclick="test_download('US')">Install</a>

                </td>
            </tr>

            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }
}


/**
 * Class DT_Starter_Tab_Second
 */
class DT_Network_Dashboard_Tab_Network
{
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->main_column() ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->

                        <?php $this->right_column() ?>

                        <!-- End Right Column -->
                    </div><!-- postbox-container 1 -->
                    <div id="postbox-container-2" class="postbox-container">
                    </div><!-- postbox-container 2 -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function main_column() {
        $available_locations = DT_Network_Dashboard_Installer::get_list_of_available_locations();
        $installed_list = DT_Network_Dashboard_Installer::get_list_of_installed_p_list();
        ?>
        <!-- Box -->
        <form method="post">
            <table class="widefat striped">
                <thead>
                <th>Available Countries</th>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <?php
                        foreach ( $installed_list as $country_code => $value ) {
                            echo '<dd><strong>' . esc_html( $available_locations[$country_code] ) . '</strong>';
                            if ( $value ) {
                                echo ' ' .  '&#9989;';
                            } else {
                                echo ' <a id="link-'. esc_attr( $country_code ) .'" onclick="load_p_list_by_country(\''. esc_attr( $country_code ) .'\')" style="cursor:pointer;">install</a> <span id="spinner-'.esc_attr( $country_code ).'"></span>';
                            }
                            echo '</dd>';
                        }
                        ?>
                        <script>
                            jQuery(document).ready(function(){
                                load_p_countries_installed()
                            })
                        </script>

                    </td>
                </tr>
                </tbody>
            </table>
        </form>
        <br>
        <!-- End Box -->
        <?php
    }

    public function right_column() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
            <th>Installed Countries</th>
            </thead>
            <tbody>
            <tr>
                <td>
                    <div id="current-locations"></div>
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }
}