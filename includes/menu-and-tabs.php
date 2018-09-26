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

        /**
         * Catch enabling and disabling of the network feature.
         */
        if ( isset( $_POST['enable_network_form'] ) && ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'enable_network'.get_current_user_id() ) ) ) {
            if ( isset( $_POST['enable_network'] ) ) {
                update_option( 'dt_network_dashboard_enable_network', 1, false );
            } else {
                update_option( 'dt_network_dashboard_enable_network', 0, false );
            }
        }

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
                <a href="<?php echo esc_attr( $link ) . 'local' ?>" class="nav-tab
                <?php ( $tab == 'local' ) ? esc_attr_e( 'nav-tab-active', 'dt_network_dashboard' ) : print ''; ?>">
                    <?php esc_attr_e( 'Install Local Locations', 'dt_network_dashboard' ) ?></a>

                <?php // make tab dependent on network enable.
                if ( get_option( 'dt_network_dashboard_enable_network' ) ) : ?>

                    <a href="<?php echo esc_attr( $link ) . 'network' ?>" class="nav-tab
                    <?php ( $tab == 'network' ) ? esc_attr_e( 'nav-tab-active', 'dt_network_dashboard' ) : print ''; ?>">
                        <?php esc_attr_e( 'Install Network Locations', 'dt_network_dashboard' ) ?></a>
                    <a href="<?php echo esc_attr( $link ) . 'configure-network' ?>" class="nav-tab
                    <?php ( $tab == 'configure-network' ) ? esc_attr_e( 'nav-tab-active', 'dt_network_dashboard' ) : print ''; ?>">
                        <?php esc_attr_e( 'Configure Network', 'dt_network_dashboard' ) ?></a>
                    <a href="<?php echo esc_attr( $link ) . 'connected' ?>" class="nav-tab
                    <?php ( $tab == 'connected' ) ? esc_attr_e( 'nav-tab-active', 'dt_network_dashboard' ) : print ''; ?>">
                        <?php esc_attr_e( 'Connected', 'dt_network_dashboard' ) ?></a>

                <?php endif; ?>
            </h2>

            <?php
            switch ($tab) {
                case "general":
                    $object = new DT_Network_Dashboard_Tab_General();
                    $object->content();
                    break;
                case "local":
                    $object = new DT_Network_Dashboard_Tab_Local();
                    $object->content();
                    break;
                case "network":
                    $object = new DT_Network_Dashboard_Tab_Network();
                    $object->content();
                    break;
                case "configure-network":
                    $object = new DT_Network_Dashboard_Tab_Configure_Network();
                    $object->content();
                    break;
                case 'connected':
                    $object = new DT_Network_Dashboard_Tab_Connected();
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
                'spinner' => ' <span><img src="'. plugin_dir_url( __FILE__ ) . '/spinner.svg" width="12px" /></span>',
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
                        <?php $this->enable_network_box() ?>

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

    public function enable_network_box() {
        /**
         * Note: post processing is done in the construct of DT_Network_Dashboard_Menu
         */
        $network = get_option( 'dt_network_dashboard_enable_network' );

        ?>
        <!-- Box -->
        <form method="post">
            <table class="widefat striped">
                <thead>
                <th>Enable Network</th>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <?php wp_nonce_field( 'enable_network'.get_current_user_id() ); ?>
                        <label for="enable_network">Enable the network features: </label>
                        <input type="checkbox" class="text" id="enable_network" name="enable_network" <?php $network ? print 'checked' : print ''; ?> />
                        <br>
                        <p><em></em></p>
                        <button type="submit" name="enable_network_form" value="1" class="button">Update</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
        <br>
        <!-- End Box -->
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
                        This saturation mapping plugin attempts to set up a globally consistent mapping schema.</dd>

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

                        <dt></dt>
                        <dd></dd>

                        <dt></dt>
                        <dd></dd>

                        <dt></dt>
                        <dd></dd>

                    </dl>

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
class DT_Network_Dashboard_Tab_Local
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
        ?>
        <!-- Box -->
        <form method="post">
        <table class="widefat striped">
            <thead>
            <th>Install</th>
            </thead>
            <tbody>
            <tr>
                <td>
                    <select name="selected_country" id="selected_country">
                        <option>Select</option>
                        <?php
                        echo '<option>----</option>';
                        echo '<option value="US">United States of America</option>';
                        echo '<option>----</option>';
                        foreach ( $available_locations as $country_code => $name ) {
                            echo '<option value="' . $country_code . '">'.$name.'</option>';
                        }
                        ?>

                    </select>
                    <a href="javascript:void(0);" onclick="load_list_by_country()" class="button" id="import_button">Load</a>
                    <script>
                        jQuery(document).ready(function() {
                            load_current_locations()
                        })

                    </script>
                    <style>
                        dd, li {
                            margin-bottom: 15px;
                        }
                        dt, li {
                            margin-bottom: 20px;
                            margin-top: 20px;
                        }
                        #results .page-title-action {
                            vertical-align: middle;
                        }
                        .show-city-link {
                            cursor: pointer;
                        }

                    </style>
                    <div id="results"></div>

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
            <th>Current Locations</th>
            </thead>
            <tbody>
            <tr>
                <td>
                    <div id="current-locations"></div>
                    <hr>
                    <a href="<?php echo esc_url( admin_url( '/edit.php?post_type=locations' ) ) ?>">View Locations</a>
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

/**
 * Class DT_Starter_Tab_Second
 */
class DT_Network_Dashboard_Tab_Configure_Network
{
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->partner_profile_metabox() ?>
                        <?php $this->population_metabox() ?>

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

    public function population_metabox() {
        // process post action
        if ( isset( $_POST['population_division'] ) && ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'population_division'.get_current_user_id() ) ) ) {
            $new = (int) sanitize_text_field( wp_unslash( $_POST['population_division'] ) );
            update_option( 'dt_network_dashboard_pd', $new, false );
        }
        $population_division = get_option( 'dt_network_dashboard_pd' );
        if ( empty( $population_division ) ) {
            update_option( 'dt_network_dashboard_pd', 5000, false );
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

    public function partner_profile_metabox() {
        // process post action
        if ( isset( $_POST['partner_profile_form'] ) && ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'partner_profile'.get_current_user_id() ) ) ) {

            $partner_profile = [
                'partner_name' => sanitize_text_field( wp_unslash( $_POST['partner_name'] ) ),
                'partner_description' => sanitize_text_field( wp_unslash( $_POST['partner_description'] ) ),
                'partner_id' => sanitize_text_field( wp_unslash( $_POST['partner_id'] ) ),
            ];
            update_option( 'dt_site_partner_profile', $partner_profile, false );

        }
        $partner_profile = get_option( 'dt_site_partner_profile' );


        ?>
        <!-- Box -->
        <form method="post">
            <?php wp_nonce_field( 'partner_profile'.get_current_user_id() ); ?>
            <table class="widefat striped">
                <thead>
                <th>Your Partner Profile</th>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <table class="widefat">
                            <tbody>
                            <tr>
                                <td><label for="partner_name">Your Group Name</label></td>
                                <td><input type="text" class="regular-text" name="partner_name"
                                           id="partner_name" value="<?php echo $partner_profile['partner_name'] ?>" /></td>
                            </tr>
                            <tr>
                                <td><label for="partner_description">Your Group Description</label></td>
                                <td><input type="text" class="regular-text" name="partner_description"
                                           id="partner_description" value="<?php echo $partner_profile['partner_description'] ?>" /></td>
                            </tr>
                            <tr>
                                <td><label for="partner_id">Site ID</label></td>
                                <td><?php echo $partner_profile['partner_id'] ?>
                                    <input type="hidden" class="regular-text" name="partner_id"
                                           id="partner_id" value="<?php echo $partner_profile['partner_id'] ?>" /></td>
                            </tr>
                            </tbody>
                        </table>

                        <p><br>
                            <button type="submit" id="partner_profile_form" name="partner_profile_form" class="button">Update</button>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
        <br>
        <!-- End Box -->
        <?php
    }
}

/**
 * Class DT_Starter_Tab_Second
 */
class DT_Network_Dashboard_Tab_Connected
{
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->overview_message() ?>

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
        <!-- Box -->
        <table class="widefat striped">
            <thead>
            <th>Connected Sites</th>
            </thead>
            <tbody>
            <tr>
                <td>

                <!-- @todo add connnection process.-->

                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }
}

