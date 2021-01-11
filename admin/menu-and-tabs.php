<?php
/**
 * DT_Network_Dashboard_Menu class for the admin page
 *
 * @class       DT_Network_Dashboard_Menu
 * @version     0.1.0
 * @since       0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly


/**
 * Initialize menu class
 */
DT_Network_Dashboard_Menu::instance();

/**
 * Class DT_Network_Dashboard_Menu
 */
class DT_Network_Dashboard_Menu {

    public $token = 'dt_network_dashboard';

    /**
     * DT_Network_Dashboard_Menu Instance
     *
     * Ensures only one instance of DT_Network_Dashboard_Menu is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return DT_Network_Dashboard_Menu instance
     */
    private static $_instance = null;
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
    } // End __construct()

    /**
     * Loads the subnav page
     * @since 0.1
     */
    public function register_menu() {
        add_menu_page( __( 'Extensions (DT)', 'disciple_tools' ),
            __( 'Extensions (DT)', 'disciple_tools' ),
            'manage_dt',
            'dt_extensions',
            array( $this, 'extensions_menu' ),
            'dashicons-admin-generic',
        59 );
        add_submenu_page( 'dt_extensions',
            'Network Dashboard',
            'Network Dashboard',
            'manage_dt',
            $this->token,
        array( $this, 'content' ) );
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

        if ( dt_network_dashboard_multisite_is_approved() ) {
            $approved_multisite = true;
        } else {
            $approved_multisite = false;
        }

        if ( isset( $_GET["tab"] ) ) {
            $tab = sanitize_key( wp_unslash( $_GET["tab"] ) );
        }
        else {
            $tab = 'profile';
        }

        $link = 'admin.php?page='.$this->token.'&tab=';

        ?>
        <div class="wrap">
            <h2><?php echo esc_attr( 'Network Dashboard' ) ?></h2>
            <h2 class="nav-tab-wrapper">

                 <a href="<?php echo esc_attr( $link ) . 'profile' ?>" class="nav-tab
                    <?php echo ( $tab == 'profile' ) ? esc_attr( 'nav-tab-active' ) : ''; ?>">
                        Profile
                </a>

                <?php if ( $approved_multisite ) :  // check if approved multisite dashboard?>

                    <a href="<?php echo esc_attr( $link ) . 'multisite-incoming' ?>" class="nav-tab
                        <?php echo ( $tab == 'multisite-incoming' || ! isset( $tab ) ) ? 'nav-tab-active' : ''; ?>">
                        Multisite Incoming
                    </a>
                    <a href="<?php echo esc_attr( $link ) . 'remote-incoming' ?>" class="nav-tab
                    <?php echo ( $tab == 'remote-incoming' ) ? 'nav-tab-active' : ''; ?>">
                        Remote Incoming
                    </a>

                <?php else : ?>
                    <a href="<?php echo esc_attr( $link ) . 'remote-incoming' ?>" class="nav-tab
                    <?php echo ( $tab == 'remote-incoming' || ! isset( $tab ) ) ? 'nav-tab-active' : ''; ?>">
                        Incoming
                    </a>
                <?php endif; ?>

                <a href="<?php echo esc_attr( $link ) . 'outgoing' ?>" class="nav-tab
                <?php echo ( $tab == 'outgoing' ) ? esc_attr( 'nav-tab-active' ) : ''; ?>">
                    Outgoing
                </a>

                <a href="<?php echo esc_attr( $link ) . 'system' ?>" class="nav-tab
                <?php echo ( $tab == 'system' ) ? esc_attr( 'nav-tab-active' ) : ''; ?>">
                    System
                </a>

                <a href="<?php echo esc_attr( $link ) . 'upgrades' ?>" class="nav-tab
                <?php echo ( $tab == 'upgrades' ) ? 'nav-tab-active' : ''; ?>">
                    Upgrades
                </a>

                <a href="<?php echo esc_attr( $link ) . 'tutorials' ?>" class="nav-tab
                <?php echo ( $tab == 'tutorials' ) ? 'nav-tab-active' : ''; ?>">
                    Tutorials
                </a>
            </h2>

            <?php
            switch ($tab) {
                case "profile":
                    $object = new DT_Network_Dashboard_Tab_Profile();
                    $object->content();
                    break;
                case "remote-incoming":
                    $object = new DT_Network_Dashboard_Tab_Remote_Incoming();
                    $object->content();
                    break;
                case "multisite-incoming":
                    $object = new DT_Network_Dashboard_Tab_Multisite_Incoming();
                    $object->content();
                    break;
                case "outgoing":
                    $object = new DT_Network_Dashboard_Tab_Outgoing();
                    $object->content();
                    break;
                case "system":
                    $object = new DT_Network_Dashboard_Tab_System();
                    $object->content();
                    break;
                case "upgrades":
                    $object = new DT_Network_Dashboard_Tab_Upgrades();
                    $object->content();
                    break;
                case "tutorials":
                    $object = new DT_Network_Dashboard_Tab_Tutorial();
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

/**
* Class DT_Network_Dashboard_Tab_Profile
 */
class DT_Network_Dashboard_Tab_Profile
{
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-1">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->box_network_tab(); ?>
                        <?php DT_Network_Dashboard_Site_Link_Metabox::admin_box_local_site_profile(); ?>
                        <?php $this->box_diagram() ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function box_network_tab(){
        if ( isset( $_POST['show-tab'] )
            && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['show-tab'] ) ), 'show-tab'.get_current_user_id() )
            && isset( $_POST['tab'] ) ) {
            $tab = sanitize_text_field( wp_unslash( $_POST['tab'] ) );
            update_option( 'dt_network_dashboard_show_tab', $tab, true );
        }
        $tab = get_option( 'dt_network_dashboard_show_tab' );
        ?>
        <form method="POST">
        <?php wp_nonce_field( 'show-tab'.get_current_user_id(), 'show-tab' ) ?>
        <table class="widefat striped">
            <thead>
                <tr><td>Show Tab</td><td></td><td></td></tr>
            </thead>
            <tbody>
            <tr>
                <td style="width:115px;"><img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ) ?>images/tab-screen.png" height="40px" /></td>
                <td>
                    <select name="tab">
                        <option value="hide" <?php echo ( 'hide' === $tab || empty( $tab ) ) ? 'selected' : ''; ?>>Hide</option>
                        <option value="show" <?php echo ( 'show' === $tab ) ? 'selected' : ''; ?>>Show</option>
                    </select>
                </td>
                <td>
                    To show the network dashboard, you must enable this feature. The background tasks of the network dashboard will run and facilitate connection to other sites, even with the "Network" tab hidden.
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <button type="submit" class="button">Update</button>
                </td>
            </tr>
            </tbody>
        </table>
        </form>
        <br>
        <?php
    }

    public function box_diagram(){
        $sites = DT_Network_Dashboard_Site_Post_Type::all_sites();
        ?>
        <style>
        .columns {
             width:25%;
             float: left;
             text-align: center;
        }
        .arrow-columns {
            width: 10%;
            float: left;
            text-align: center;
            font-size:4em;
            font-weight: bolder;
            padding-top: 65px;
        }
        .column-heading {
            font-size:1.2rem;
            padding:1rem;
        }
        .row {
            margin: 10px 0;
        }
        .nd-site-box {
            padding: 5px;
            border: 1px solid grey;
            margin: 2px auto;
            min-width: 150px;
            display: block !important;
        }
        .you-name {
            text-align: center;
        }
        </style>
        <p><strong>Network Map (Incoming and Outgoing Reports)</strong></p>
        <table class="widefat striped" id="network-map">
            <tbody>
                <tr>
                    <td>
                        <div class="columns">
                            <div class="column-heading">Incoming</div>
                            <div>
                                <?php
                                foreach ( $sites as $site ){
                                    if ( 0 === $site['id'] ){
                                        continue;
                                    }
                                    else if ( is_multisite() && 'multisite' === $site['type'] && dt_network_dashboard_multisite_is_approved() && 'reject' !== $site['receive_activity'] ) {
                                        echo '<div class="row"><span class="nd-site-box multisite">' . esc_html( $site['name'] ) . '</span></div>';
                                    }
                                    else if ('network_dashboard_receiving' === $site['connection_type'] || 'network_dashboard_both' === $site['connection_type'] && 'reject' !== $site['receive_activity'] ) {
                                        echo '<div class="row"><span class="nd-site-box remote">' . esc_html( $site['name'] ) . '</span></div>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <div class="arrow-columns">
                            &#8594;
                        </div>
                        <div class="columns ">
                            <div class="column-heading">You</div>
                            <div class="you-name">
                            <?php
                            $profile = dt_network_site_profile();
                            echo '<div class="row"><span class="nd-site-box"><strong>' . esc_html( $profile['partner_name'] ) . '</strong></span></div>';
                            ?>
                            </div>
                        </div>
                        <div class="arrow-columns">
                            &#8594;
                        </div>
                        <div class="columns">
                            <div class="column-heading">Outgoing</div>
                                <?php
                                $approved_sites = dt_dashboard_approved_sites();
                                $approved_sites_ids = array_keys( $approved_sites );
                                foreach ( $sites as $site ){
                                    if ( 0 === $site['id'] ){
                                        continue;
                                    }
                                    else if ( is_multisite() && 'multisite' === $site['type'] && dt_network_dashboard_multisite_is_approved() && 'none' !== $site['send_activity'] && in_array( $site['type_id'], $approved_sites_ids ) ) {
                                        echo '<div class="row"><span class="nd-site-box multisite">' . esc_html( $site['name'] ) . '</span></div>';
                                    }
                                    else if ('network_dashboard_sending' === $site['connection_type'] || 'network_dashboard_both' === $site['connection_type'] && 'none' !== $site['send_activity'] ) {
                                        echo '<div class="row"><span class="nd-site-box remote">' . esc_html( $site['name'] ) . '</span></div>';
                                    }
                                }
                                ?>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <?php
    }



}

/**
 * Class DT_Network_Dashboard_Tab_Multisite_Incoming
 */
class DT_Network_Dashboard_Tab_Multisite_Incoming
{
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-1">
                    <div id="post-body-content">
                        <!-- Main Column -->
                        <?php
                        if ( dt_is_current_multisite_dashboard_approved() ) {

                            $this->main_column();

                            $obj = new DT_Network_Dashboard_Tab_Profile();
                            $obj->box_diagram();
                        }
                        ?>
                        <style>#network-map .multisite { background-color: lightgray;}</style>
                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->

                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function main_column() {
        DT_Network_Dashboard_Site_Post_Type::sync_all_multisites_to_post_type();
        $message = false;
        if ( isset( $_POST['network_dashboard_nonce'] )
            && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['network_dashboard_nonce'] ) ), 'network_dashboard_' . get_current_user_id() )
             ) {

            if ( isset( $_POST['update-profile'] ) && isset( $_POST['partner'] ) && is_array( $_POST['partner'] ) ) {
                $partner = dt_recursive_sanitize_array( $_POST['partner'] ); // @phpcs:ignore

                foreach ( $partner as $key => $value ){
                    DT_Network_Dashboard_Site_Post_Type::update_multisite_profile( $key );
                    if ( isset( $value['nickname'] ) && ! empty( $value['nickname'] ) ) {
                        DT_Network_Dashboard_Site_Post_Type::update_site_name( $key, sanitize_text_field( wp_unslash( $value['nickname'] ) ) );
                    }
                    if ( isset( $value['visibility'] ) && ! empty( $value['visibility'] ) ) {
                        DT_Network_Dashboard_Site_Post_Type::update_visibility( $key, sanitize_text_field( wp_unslash( $value['visibility'] ) ) );
                    }
                    if ( isset( $value['receive_activity'] ) && ! empty( $value['receive_activity'] ) ) {
                        DT_Network_Dashboard_Site_Post_Type::update_receive_activity( $key, sanitize_text_field( wp_unslash( $value['receive_activity'] ) ) );
                    }
                }
            }
        }
        // Get list of sites
        $sites = DT_Network_Dashboard_Site_Post_Type::all_sites();

        /** Message */
        if ( $message ) {
            echo '<div class="notice '.esc_attr( $message[0] ).'">'.esc_html( $message[1] ).'</div>';
        }

        /** Box */
        ?>
        <form method="post">
            <?php wp_nonce_field( 'network_dashboard_' . get_current_user_id(), 'network_dashboard_nonce' ) ?>
            <p><strong>Manage Connected Sites</strong></p>
            <table class="widefat striped">
                <thead>
                <th>ID</th>
                <th>Site Name</th>
                <th>Nickname</th>
                <th>Domain</th>
                <th>Show in Metrics</th>
                <th>Receive Live Activity</th>
                <th>Profile</th>
                </thead>
                <tbody>
                <?php
                if ( ! empty( $sites ) ) {
                    foreach ( $sites as $partner_id => $site ) {
                        if ( $site['type'] !== 'multisite' ){
                            continue;
                        }
                        $profile = maybe_unserialize( $site['profile'] );
                        ?>
                        <tr>
                            <td>
                                <?php echo esc_html( $site['id'] ) ?>
                            </td>
                            <td>
                                <?php echo '<strong>' . esc_html( $profile['partner_name'] ) . '</strong>' ?>
                            </td>
                            <td>
                                <input type="text" name="partner[<?php echo esc_attr( $site['id'] ) ?>][nickname]" value="<?php echo esc_attr( $site['name'] ) ?>" />
                            </td>
                            <td>
                                <?php echo '<a href="'. esc_url( $profile['partner_url'] ) .'" target="_blank">' . esc_url( $profile['partner_url'] ) . '</a>' ?>
                            </td>
                            <td>
                                <input type="radio" name="partner[<?php echo esc_attr( $site['id'] ) ?>][visibility]" value="show" <?php echo ( $site['visibility'] === 'show' ) ? 'checked' : '' ?>/> Show |
                                <input type="radio" name="partner[<?php echo esc_attr( $site['id'] ) ?>][visibility]" value="hide" <?php echo ( isset( $site['visibility'] ) && $site['visibility'] === 'hide' ) ? 'checked' : '' ?>/> Hide
                            </td>
                            <td>
                                <input type="radio" name="partner[<?php echo esc_attr( $site['id'] ) ?>][receive_activity]" value="allow" <?php echo ( $site['receive_activity'] === 'allow' || empty( $site['receive_activity'] ) ) ? 'checked' : '' ?>/> Allow |
                                <input type="radio" name="partner[<?php echo esc_attr( $site['id'] ) ?>][receive_activity]" value="reject" <?php echo ( $site['receive_activity'] === 'reject' ) ? 'checked' : '' ?>/> Reject
                            </td>

                             <td>
                                <button name="update-profile"  value="<?php echo esc_attr( $site['id'] ) ?>" type="submit" class="button">Update Profile</button>
                            </td>
                        </tr>
                        <?php
                    } // end foreach
                }
                else {
                    ?>
                    <tr>
                        <td colspan="6">
                            No dashboard sites found.
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <div style="display:none;padding:2em;" id="fail-error"></div>
        </form>
        <br>
        <!-- End Box -->
        <?php
    }
}

/**
 * Class DT_Network_Dashboard_Tab_Remote_Incoming
 */
class DT_Network_Dashboard_Tab_Remote_Incoming
{
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-1">
                    <div id="post-body-content">

                        <!-- Main Column -->
                        <?php $this->main_column() ?>

                        <?php
                        $obj = new DT_Network_Dashboard_Tab_Profile();
                        $obj->box_diagram();
                        ?>
                        <style>#network-map .remote { background-color: lightgray;}</style>
                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->

                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function main_column() {

        $message = false;
        if ( isset( $_POST['network_dashboard_nonce'] )
            && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['network_dashboard_nonce'] ) ), 'network_dashboard_' . get_current_user_id() )
             ) {

            if ( isset( $_POST['update-profile'] ) && isset( $_POST['partner'] ) && is_array( $_POST['partner'] ) ) {
                $partner = dt_recursive_sanitize_array( $_POST['partner'] ); // @phpcs:ignore

                foreach ( $partner as $key => $value ){
                    if ( isset( $value['nickname'] ) && ! empty( $value['nickname'] ) ) {
                        DT_Network_Dashboard_Site_Post_Type::update_site_name( $key, sanitize_text_field( wp_unslash( $value['nickname'] ) ) );
                    }
                    if ( isset( $value['visibility'] ) && ! empty( $value['visibility'] ) ) {
                        DT_Network_Dashboard_Site_Post_Type::update_visibility( $key, sanitize_text_field( wp_unslash( $value['visibility'] ) ) );
                    }
                    if ( isset( $value['receive_activity'] ) && ! empty( $value['receive_activity'] ) ) {
                        DT_Network_Dashboard_Site_Post_Type::update_receive_activity( $key, sanitize_text_field( wp_unslash( $value['receive_activity'] ) ) );
                    }
                }

                // reset ui transient caches with new parameters
                require_once( plugin_dir_path( __DIR__ ) . 'metrics/base.php' );
                DT_Network_Dashboard_Metrics_Base::reset_ui_caches();
            }
        }
        // Get list of sites
        $sites = DT_Network_Dashboard_Site_Post_Type::all_sites();

        /** Message */
        if ( $message ) {
            echo '<div class="notice '.esc_attr( $message[0] ).'">'.esc_html( $message[1] ).'</div>';
        }

        /** Box */
        ?>
        <form method="post">
            <?php wp_nonce_field( 'network_dashboard_' . get_current_user_id(), 'network_dashboard_nonce' ) ?>
            <p><strong>Manage Connected Sites</strong></p>
            <table class="widefat striped">
                <thead>
                    <th>ID</th>
                    <th>Partner Name</th>
                    <th>Nickname</th>
                    <th>Domain</th>
                    <th>Show in Metrics</th>
                    <th>Receive Live Activity</th>
                    <th>Profile</th>
                </thead>
                <tbody>
                <?php
                if ( ! empty( $sites ) ) {
                    foreach ( $sites as $site ) {
                        if ( $site['type'] !== 'remote' ){
                            continue;
                        }
                        $non_wp = empty( $site['non_wp'] );
                        $profile = maybe_unserialize( $site['profile'] );

                        ?>
                        <tr>
                            <td style="width:10px;">
                                <?php echo esc_html( $site['id'] ) ?>
                            </td>
                            <td>
                                <strong><?php echo esc_html( $profile['partner_name'] ) ?></strong>
                            </td>
                            <td>
                                <input type="text" name="partner[<?php echo esc_attr( $site['id'] ) ?>][nickname]" value="<?php echo esc_html( $site['name'] ) ?>" />
                            </td>
                            <td>
                                <a href="<?php echo esc_url( $profile['partner_url'] ?? '' ) ?>"><?php echo esc_url( $profile['partner_url'] ?? '' ) ?></a>
                            </td>
                            <td>
                                <input type="radio" name="partner[<?php echo esc_attr( $site['id'] ) ?>][visibility]" value="show" <?php echo ( $site['visibility'] === 'show' ) ? 'checked' : '' ?>/> Show |
                                <input type="radio" name="partner[<?php echo esc_attr( $site['id'] ) ?>][visibility]" value="hide" <?php echo ( isset( $site['visibility'] ) && $site['visibility'] === 'hide' ) ? 'checked' : '' ?>/> Hide
                            </td>
                            <td>
                                <?php if ( $non_wp ) : ?>
                                <input type="radio" name="partner[<?php echo esc_attr( $site['id'] ) ?>][receive_activity]" value="allow" <?php echo ( $site['receive_activity'] === 'allow' || $site['receive_activity'] === '' ) ? 'checked' : '' ?>/> Allow |
                                <input type="radio" name="partner[<?php echo esc_attr( $site['id'] ) ?>][receive_activity]" value="reject" <?php echo ( $site['receive_activity'] === 'reject' ) ? 'checked' : '' ?>/> Reject
                                <?php endif; ?>
                            </td>
                            <td>
                                <button value="<?php echo esc_attr( $site['id'] ) ?>" name="update-profile" type="submit" class="button" >Update Profile</button>
                            </td>
                        </tr>
                        <?php
                    } // end foreach
                }
                else {
                    ?>
                    <tr>
                        <td>
                            No dashboard sites found.
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <div style="display:none;padding:2em;" id="fail-error"></div>
        </form>
        <br>
        <!-- End Box -->
        <?php
    }
}



class DT_Network_Dashboard_Tab_Outgoing
{
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-1">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->box_send_activity() ?>

                        <?php
                        $obj = new DT_Network_Dashboard_Tab_Profile();
                        $obj->box_diagram();
                        ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }


    public function box_send_activity() {
        if ( isset( $_POST['activity-nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['activity-nonce'] ) ), 'activity'. get_current_user_id() ) ) {

            if ( isset( $_POST['send_activity'] ) && ! empty( $_POST['send_activity'] ) && is_array( $_POST['send_activity'] ) ) {
                $send_activity = dt_recursive_sanitize_array( $_POST['send_activity'] ); // @phpcs:ignore
                foreach ($send_activity as $i => $v ) {
                    DT_Network_Dashboard_Site_Post_Type::update_send_activity( sanitize_text_field( wp_unslash( $i ) ), sanitize_text_field( wp_unslash( $v ) ) );
                }
            }

            if ( isset( $_POST['location_precision'] ) && ! empty( $_POST['location_precision'] ) && is_array( $_POST['location_precision'] ) ) {
                $location_precision = dt_recursive_sanitize_array( $_POST['location_precision'] ); // @phpcs:ignore
                foreach ( $location_precision as $i => $v ) {
                    DT_Network_Dashboard_Site_Post_Type::update_location_precision( sanitize_text_field( wp_unslash( $i ) ), sanitize_text_field( wp_unslash( $v ) ) );
                }
            }
        }

        /* REMOTE SITES*/
        $sites = DT_Network_Dashboard_Site_Post_Type::all_sites();
        $multisites = DT_Network_Dashboard_Site_Post_Type::all_multisite_blog_ids( true )

        ?>
        <form method="post">
        <table class="widefat striped">
        <thead>
        <tr>
            <td style="width:300px;">Outgoing Reports and Activity Log</td>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>
            <?php
            if ( !is_array( $sites )) :
                ?>
                No site links found. Go to <a href="<?php echo esc_url( admin_url() ) ?>edit.php?post_type=site_link_system">Site Links</a> and create a site link, and then select "Network Report" as the type.
                <?php
            else :
                ?>
                    <?php wp_nonce_field( 'activity'. get_current_user_id(), 'activity-nonce' ) ?>
                    <table class="widefat striped">
                        <thead>
                        <tr>
                            <td style="width:30%;">Remote Sites</td>
                            <td style="width:30%;text-align:center;"><!--Location Precision Level--></td>
                            <td style="width:30%;text-align:center;">Reports</td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $i = 0;
                        foreach ( $sites as $site ) {
                            if ('network_dashboard_sending' === $site['connection_type'] || 'network_dashboard_both' === $site['connection_type'] ) {
                                $i++;
                                ?>
                                <tr>
                                    <td><?php echo esc_html( $site['name'] ) ?></td>
                                    <td style="text-align: center;">
                                    <!--
                                        <select name="location_precision[<?php echo esc_attr( $site['id'] ) ?>]">
                                            <option value="none" <?php echo ( $site['location_precision'] === '' || $site['location_precision'] === 'none' ) ? 'selected' : ''; ?>>No Filter</option>
                                            <option value="admin2" <?php echo ( $site['location_precision'] === 'admin2' ) ? 'selected' : ''; ?>>Admin2 (County, District)</option>
                                            <option value="admin1" <?php echo ( $site['location_precision'] === 'admin1' ) ? 'selected' : ''; ?>>Admin1 (State)</option>
                                            <option value="admin0" <?php echo ( $site['location_precision'] === 'admin0' ) ? 'selected' : ''; ?>>Admin0 (Country)</option>
                                        </select>
                                        -->
                                    </td>
                                    <td style="text-align: center;">
                                        <input type="radio" name="send_activity[<?php echo esc_attr( $site['id'] ) ?>]" value="none" <?php echo ( $site['send_activity'] === 'none' ) ? 'checked' : '' ?>/> Send Nothing |
                                        <input type="radio" name="send_activity[<?php echo esc_attr( $site['id'] ) ?>]" value="daily" <?php echo ( $site['send_activity'] === 'daily' || $site['send_activity'] === '' ) ? 'checked' : '' ?>/> Send Daily
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        if (0 === $i ) {
                            ?>
                            <tr>
                                <td>No sites found</td>
                                 <td></td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>

                <?php if ( is_multisite() && dt_network_dashboard_multisite_is_approved() ) : ?>
                    <br>
                    <table class="widefat striped">
                        <thead>
                        <tr>
                            <td style="width:30%;">Multisite Sites</td>
                            <td style="width:30%;text-align:center;"><!--Location Precision Level--></td>
                            <td style="width:30%;text-align:center;">Reports</td>
                        </tr>
                        </thead>
                        <tbody>

                        <?php
                        $approved_sites = dt_dashboard_approved_sites();
                        $approved_sites_ids = array_keys( $approved_sites );

                        foreach ( $sites as $site ) {
                            if ( 0 === $site['id'] ){
                                continue;
                            }

                            if ('multisite' === $site['type'] && in_array( $site['type_id'], $multisites ) && in_array( $site['type_id'], $approved_sites_ids ) ) {
                                $i++;
                                ?>
                                <tr>
                                    <td><?php echo esc_html( $site['name'] ) ?></td>
                                    <td style="text-align: center;">
                                    <!--
                                        <select name="location_precision[<?php echo esc_attr( $site['id'] ) ?>]">
                                            <option value="none" <?php echo ( $site['location_precision'] === '' || $site['location_precision'] === 'none' ) ? 'selected' : ''; ?>>No Filter</option>
                                            <option value="admin2" <?php echo ( $site['location_precision'] === 'admin2' ) ? 'selected' : ''; ?>>Admin2 (County, District)</option>
                                            <option value="admin1" <?php echo ( $site['location_precision'] === 'admin1' ) ? 'selected' : ''; ?>>Admin1 (State)</option>
                                            <option value="admin0" <?php echo ( $site['location_precision'] === 'admin0' ) ? 'selected' : ''; ?>>Admin0 (Country)</option>
                                        </select>
                                     -->
                                    </td>
                                    <td style="text-align: center;">
                                        <input type="radio" name="send_activity[<?php echo esc_attr( $site['id'] ) ?>]" value="none" <?php echo ( $site['send_activity'] === 'none' ) ? 'checked' : '' ?>/> Send Nothing |
                                        <input type="radio" name="send_activity[<?php echo esc_attr( $site['id'] ) ?>]" value="daily" <?php echo ( $site['send_activity'] === 'daily' || $site['send_activity'] === '' ) ? 'checked' : '' ?>/> Send Daily
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                        </tbody>
                    </table>

                    <?php endif; // is multisite ?>


                    <p>
                        <br><button type="submit" style="float:right;" class="button">Update</button>
                    </p>

                    <!-- end inner table -->
                </td>
                </tr>
                </tbody>
                </table>
                <?php
            endif;
            ?>
        <br>
        <?php
    }
}

/**
 * Class DT_Network_Dashboard_Tab_Tutorial
 */
class DT_Network_Dashboard_Tab_System
{
    public function content() {

        DT_Network_Dashboard_Snapshot::snapshot_report();
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-1">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->box_local_site_data();?>
                        <?php $this->box_manage_sites_data() ?>
                        <?php $this->box_registered_key_list() ?>
                        <?php $this->box_site_project_details() ?>
                        <?php $this->box_utilities() ?>
                        <?php $this->box_cron_list() ?>
                        <?php
                        $obj = new DT_Network_Dashboard_Tab_Profile();
                        $obj->box_diagram();
                        ?>
                        <?php $this->box_txt_log_list() ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function box_local_site_data() {
        global $wpdb;

        $profile = dt_network_site_profile();
        $snapshot = DT_Network_Dashboard_Snapshot::snapshot_report();

        if ( isset( $_POST['local_site_data'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['local_site_data'] ) ), 'local_site_data'.get_current_user_id() ) ) {
            if ( isset( $_POST['new-local-snapshot'] ) ) {
                $snapshot = DT_Network_Dashboard_Snapshot::snapshot_report( true );
            }

            if ( isset( $_POST['delete-local-activity'] ) ) {
                DT_Network_Activity_Log::delete_activity( $profile['partner_id'] );
            }
        }

        ?>
        <p><strong>Manage Local Site Data</strong></p>
        <form method="POST">
            <?php wp_nonce_field( 'local_site_data'.esc_html( get_current_user_id() ), 'local_site_data' ) ?>
            <table class="widefat striped">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Domain</th>
                    <th>Snapshot</th>
                    <th></th>
                    <th>Activity</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <strong><?php echo esc_html( $profile['partner_name'] ) ?></strong><br>
                    </td>
                    <td>
                        <a href="<?php echo esc_html( $profile['partner_url'] ) ?>"><?php echo esc_html( $profile['partner_url'] ) ?></a>
                    </td>
                    <td style="width:150px; border-left: 1px solid lightgrey;">
                        <?php echo ( ! empty( $snapshot ) ) ? '&#9989;' : '&#x2718;' ?>
                        <?php echo ( ! empty( $snapshot['timestamp'] ) ) ? esc_html( gmdate( 'Y-m-d H:i:s', $snapshot['timestamp'] ) ) : '---' ?>
                    </td>
                    <td>
                        <button name="new-local-snapshot" type="submit" value="new-local-snapshot" class="button" >Refresh Snapshot</button>
                    </td>
                    <td style="width:100px; border-left: 1px solid lightgrey;">
                        Records:
                         <?php
                             echo esc_html( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM $wpdb->dt_movement_log WHERE site_id = %s", $profile['partner_id'] ) ) );
                            ?>
                    </td>
                    <td>
                        <button name="delete-local-activity" type="submit" value="delete-local-activity" class="button" >Delete All Local Activity</button>
                        <a href="<?php echo esc_url( admin_url() ) ?>/admin.php?page=dt_network_dashboard&tab=system&rebuild_activity=0" type="submit" value="new-local-activity" class="button" >Rebuild Local Activity</a>
                    </td>
                </tr>
                <?php

                    /* Start Loop 0 */
                if ( isset( $_GET['rebuild_activity'] ) && '0' === $_GET['rebuild_activity'] ) :
                    ?>
                        <tr>
                            <td colspan="6"><img src="<?php echo esc_url( get_theme_file_uri() ) ?>/spinner.svg" width="30px" alt="spinner" /></td>
                        </tr>
                        <script type="text/javascript">
                            <!--
                            function nextpage() {
                                location.href = "<?php echo esc_url( admin_url() ) ?>admin.php?page=dt_network_dashboard&tab=system&rebuild_activity=1&nonce=<?php echo esc_attr( wp_create_nonce( 'rebuild_activity'.get_current_user_id() ) ) ?>";
                            }
                            setTimeout( "nextpage()", 1500 );
                            //-->
                        </script>
                        <?php
                    endif; // 0

                    /* New Contact 1 */
                if ( isset( $_GET['rebuild_activity'] ) && '1' === $_GET['rebuild_activity'] ) :
                    $results = DT_Network_Activity_Log::query_new_contacts();
                    DT_Network_Activity_Log::local_bulk_insert( $results );
                    ?>
                        <tr>
                            <td colspan="6">New Contacts</td>
                        </tr>
                        <tr>
                            <td colspan="6"><img src="<?php echo esc_url( get_theme_file_uri() ) ?>/spinner.svg" width="30px" alt="spinner" /></td>
                        </tr>
                        <script type="text/javascript">
                            <!--
                            function nextpage() {
                                location.href = "<?php echo esc_attr( admin_url() ) ?>admin.php?page=dt_network_dashboard&tab=system&rebuild_activity=2&nonce=<?php echo esc_attr( wp_create_nonce( 'rebuild_activity'.get_current_user_id() ) ) ?>";
                            }
                            setTimeout( "nextpage()", 1500 );
                            //-->
                        </script>
                        <?php
                    endif; // 1

                    /* New Groups 2 */
                if ( isset( $_GET['rebuild_activity'] ) && '2' === $_GET['rebuild_activity'] ) :
                    $results = DT_Network_Activity_Log::query_new_groups();
                    DT_Network_Activity_Log::local_bulk_insert( $results );
                    ?>
                        <tr>
                            <td colspan="6">New Contacts</td>
                        </tr>
                        <tr>
                            <td colspan="6">New Groups (pre-group,group,church,team)</td>
                        </tr>
                        <tr>
                            <td colspan="6"><img src="<?php echo esc_url( get_theme_file_uri() ) ?>/spinner.svg" width="30px" alt="spinner" /></td>
                        </tr>
                        <script type="text/javascript">
                            <!--
                            function nextpage() {
                                location.href = "<?php echo esc_attr( admin_url() ) ?>admin.php?page=dt_network_dashboard&tab=system&rebuild_activity=3&nonce=<?php echo esc_attr( wp_create_nonce( 'rebuild_activity'.get_current_user_id() ) ) ?>";
                            }
                            setTimeout( "nextpage()", 1500 );
                            //-->
                        </script>
                        <?php
                    endif; // 2

                    /* New Groups 3 */
                if ( isset( $_GET['rebuild_activity'] ) && '3' === $_GET['rebuild_activity'] ) :
                    $results = DT_Network_Activity_Log::query_new_baptism();
                    DT_Network_Activity_Log::local_bulk_insert( $results );
                    ?>
                        <tr>
                            <td colspan="6">New Contacts</td>
                        </tr>
                        <tr>
                            <td colspan="6">New Groups (pre-group,group,church,team)</td>
                        </tr>
                        <tr>
                            <td colspan="6">New Baptisms</td>
                        </tr>
                        <tr>
                            <td colspan="6"><img src="<?php echo esc_url( get_theme_file_uri() ) ?>/spinner.svg" width="30px" alt="spinner" /></td>
                        </tr>
                        <script type="text/javascript">
                            <!--
                            function nextpage() {
                                location.href = "<?php echo esc_attr( admin_url() ) ?>admin.php?page=dt_network_dashboard&tab=system&rebuild_activity=4&nonce=<?php echo esc_attr( wp_create_nonce( 'rebuild_activity'.get_current_user_id() ) ) ?>";
                            }
                            setTimeout( "nextpage()", 1500 );
                            //-->
                        </script>
                        <?php
                    endif; // 3

                    /* New Groups 4 */
                if ( isset( $_GET['rebuild_activity'] ) && '4' === $_GET['rebuild_activity'] ) :
                    $results = DT_Network_Activity_Log::query_new_coaching();
                    DT_Network_Activity_Log::local_bulk_insert( $results );
                    ?>
                        <tr>
                            <td colspan="6">New Contacts</td>
                        </tr>
                        <tr>
                            <td colspan="6">New Groups (pre-group,group,church,team)</td>
                        </tr>
                        <tr>
                            <td colspan="6">New Baptisms</td>
                        </tr>
                        <tr>
                            <td colspan="6">New Coaching</td>
                        </tr>
                        <tr>
                            <td colspan="6"><img src="<?php echo esc_url( get_theme_file_uri() ) ?>/spinner.svg" width="30px" alt="spinner" /></td>
                        </tr>
                        <script type="text/javascript">
                            <!--
                            function nextpage() {
                                location.href = "<?php echo esc_attr( admin_url() ) ?>admin.php?page=dt_network_dashboard&tab=system&rebuild_activity=5&nonce=<?php echo esc_attr( wp_create_nonce( 'rebuild_activity'.get_current_user_id() ) ) ?>";
                            }
                            setTimeout( "nextpage()", 1500 );
                            //-->
                        </script>
                        <?php
                    endif; // 4

                    /* New Generations 5 */
                if ( isset( $_GET['rebuild_activity'] ) && '5' === $_GET['rebuild_activity'] ) :
                    $results = DT_Network_Activity_Log::query_new_group_generations();
                    DT_Network_Activity_Log::local_bulk_insert( $results );
                    ?>
                        <tr>
                            <td colspan="6">New Contacts</td>
                        </tr>
                        <tr>
                            <td colspan="6">New Groups (pre-group,group,church,team)</td>
                        </tr>
                        <tr>
                            <td colspan="6">New Baptisms</td>
                        </tr>
                        <tr>
                            <td colspan="6">New Coaching</td>
                        </tr>
                        <tr>
                            <td colspan="6">Reported Generations (pre-group, group, church, team)</td>
                        </tr>
                        <tr>
                            <td colspan="6"><img src="<?php echo esc_url( get_theme_file_uri() ) ?>/spinner.svg" width="30px" alt="spinner" /></td>
                        </tr>
                        <script type="text/javascript">
                            <!--
                            function nextpage() {
                                location.href = "<?php echo esc_attr( admin_url() ) ?>admin.php?page=dt_network_dashboard&tab=system&rebuild_activity=6&nonce=<?php echo esc_attr( wp_create_nonce( 'rebuild_activity'.get_current_user_id() ) ) ?>";
                            }
                            setTimeout( "nextpage()", 1500 );
                            //-->
                        </script>
                        <?php
                    endif; // 5

                    /* New Plugins 6 */
                if ( isset( $_GET['rebuild_activity'] ) && '6' === $_GET['rebuild_activity'] ) :
                    do_action( 'dt_network_dashboard_rebuild_activity' );
                    ?>
                        <tr>
                            <td colspan="6">New Contacts</td>
                        </tr>
                        <tr>
                            <td colspan="6">New Groups (pre-group,group,church,team)</td>
                        </tr>
                        <tr>
                            <td colspan="6">New Baptisms</td>
                        </tr>
                        <tr>
                            <td colspan="6">New Coaching</td>
                        </tr>
                        <tr>
                            <td colspan="6">Reported Generations (pre-group, group, church, team)</td>
                        </tr>
                        <tr>
                            <td colspan="6">Plugins</td>
                        </tr>
                        <tr>
                            <td colspan="6"><img src="<?php echo esc_url( get_theme_file_uri() ) ?>/spinner.svg" width="30px" alt="spinner" /></td>
                        </tr>
                        <script type="text/javascript">
                            <!--
                            function nextpage() {
                                location.href = "<?php echo esc_attr( admin_url() ) ?>admin.php?page=dt_network_dashboard&tab=system&rebuild_activity=7&nonce=<?php echo esc_attr( wp_create_nonce( 'rebuild_activity'.get_current_user_id() ) ) ?>";
                            }
                            setTimeout( "nextpage()", 1500 );
                            //-->
                        </script>
                        <?php
                    endif; // 6
                ?>
                </tbody>
            </table>
        </form>
        <br>
        <?php
    }

    public function box_manage_sites_data() {
        global $wpdb;

        $message = false;
        if ( isset( $_POST['network_dashboard_nonce'] )
            && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['network_dashboard_nonce'] ) ), 'network_dashboard_' . get_current_user_id() )
             ) {

            if ( is_multisite() ) :
                /* SNAPSHOT */
                if ( isset( $_POST['new-multisite-snapshot'] ) ){
                    dt_save_log( 'management', '', false );
                    dt_save_log( 'management', 'REFRESH MULTISITE SNAPSHOT', false );

                    if ( dt_network_dashboard_collect_multisite( intval( sanitize_key( wp_unslash( $_POST['new-multisite-snapshot'] ) ) ) ) ) {
                        $message = array( 'notice-success','Successful collection of new snapshot' );
                    }
                    else {
                        $message = array( 'notice-error', 'Failed collection' );
                    }
                }

                /* REBUILD ACTIVITY */
                if ( isset( $_POST['new-multisite-activity'] ) ){
                    dt_save_log( 'management', '', false );
                    dt_save_log( 'management', 'REBUILD MULTISITE ACTIVITY', false );

                    $id = sanitize_text_field( wp_unslash( $_POST['new-multisite-activity'] ) );

                    dt_save_log( 'management', 'Start ID: ' . $id, false );

                    $sites = DT_Network_Dashboard_Site_Post_Type::all_sites();
                    foreach ( $sites as $site ){
                        if ( $id === $site['id'] ){
                            dt_network_dashboard_collect_multisite_activity( $site );
                            dt_save_log( 'management', 'End ID: ' . $id, false );
                            break;
                        }
                    }
                }
                /* DELETE ACTIVITY */
                if ( isset( $_POST['delete-multisite-activity'] ) ){
                    dt_save_log( 'management', '', false );
                    dt_save_log( 'management', 'DELETE MULTISITE ACTIVITY', false );

                    $id = sanitize_text_field( wp_unslash( $_POST['delete-multisite-activity'] ) );

                    dt_save_log( 'management', 'Start ID: ' . $id, false );

                    $sites = DT_Network_Dashboard_Site_Post_Type::all_sites();
                    foreach ( $sites as $site ){
                        if ( $id === $site['id'] ){
                            DT_Network_Activity_Log::delete_activity( $site['partner_id'] );
                            dt_save_log( 'management', 'End ID: ' . $id, false );
                            break;
                        }
                    }
                }
            endif; // is multisite

            /* SNAPSHOT */
            if ( isset( $_POST['new-remote-snapshot'] ) ){
                dt_save_log( 'management', '', false );
                dt_save_log( 'management', 'REFRESH REMOTE SNAPSHOT', false );

                $result = dt_get_site_snapshot( intval( sanitize_key( wp_unslash( $_POST['new-remote-snapshot'] ) ) ) );
                if ( $result ) {
                    $message = array( 'notice-success','Successful collection of new snapshot.' );
                }
                else {
                    $message = array( 'notice-error', 'Failed collection' );
                }
            }

            /* DELETE ACTIVITY */

            if ( isset( $_POST['delete-remote-activity'] ) ){
                dt_save_log( 'management', '', false );
                dt_save_log( 'management', 'DELETE REMOTE SNAPSHOT', false );

                $id = sanitize_text_field( wp_unslash( $_POST['delete-remote-activity'] ) );

                dt_save_log( 'management', 'Start ID: ' . $id, false );

                $sites = DT_Network_Dashboard_Site_Post_Type::all_sites();
                foreach ( $sites as $site ){
                    if ( $id === $site['id'] ){
                        DT_Network_Activity_Log::delete_activity( $site['partner_id'] );
                        dt_save_log( 'management', 'End ID: ' . $id, false );
                        break;
                    }
                }
            }
            /* REBUILD ACTIVITY */
            if ( isset( $_POST['new-remote-activity'] ) ){
                dt_save_log( 'management', '', false );
                dt_save_log( 'management', 'REBUILD REMOTE ACTIVITY', false );

                $id = sanitize_text_field( wp_unslash( $_POST['new-remote-activity'] ) );

                dt_save_log( 'management', 'Start ID: ' . $id, false );

                $sites = DT_Network_Dashboard_Site_Post_Type::all_sites();
                foreach ( $sites as $site ){
                    if ( $id === $site['id'] ){
                        dt_network_dashboard_collect_remote_activity_single( $site );
                        dt_save_log( 'management', 'End ID: ' . $id, false );
                        break;
                    }
                }
            }
        }

        // Get list of sites
        $sites = DT_Network_Dashboard_Site_Post_Type::all_sites();

        /** Message */
        if ( $message ) {
            echo '<div class="notice '.esc_attr( $message[0] ).'">'.esc_html( $message[1] ).'</div>';
        }

        /** Box */
        ?>
        <form method="post">
            <?php wp_nonce_field( 'network_dashboard_' . get_current_user_id(), 'network_dashboard_nonce' ) ?>
            <p><strong>Manage Other Sites</strong></p>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Domain</th>
                        <th style="width:150px;">Snapshot</th>
                        <th></th>
                        <th>Activity</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ( ! empty( $sites ) ) {
                    foreach ( $sites as $partner_id => $site ) {
                        if ( $site['id'] === 0 ) {
                            continue; // if it is a local site, handle in the next section
                        }
                        if ( ! empty( $site['non_wp'] ) ) {
                            continue; // if it is a non-dt remote site it has no log or snapshot to transfer
                        }
                        $snapshot = maybe_unserialize( $site['snapshot'] );
                        $profile = maybe_unserialize( $site['profile'] );
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html( $site['name'] ) ?></strong><br>
                            </td>
                            <td>
                                <?php echo '<a href="'. esc_url( $profile['partner_url'] ) .'" target="_blank">' . esc_url( $profile['partner_url'] ) . '</a>' ?>
                            </td>
                            <td style="border-left: 1px solid lightgrey">
                                <?php echo ( ! empty( $snapshot ) ) ? '&#9989;' : '&#x2718;' ?>
                                <?php echo ( ! empty( $site['snapshot_timestamp'] ) ) ? esc_html( gmdate( 'Y-m-d H:i:s', $site['snapshot_timestamp'] ) ) : '---' ?>
                                <?php
                                if ( ! empty( $fail ) ){
                                    ?>
                                    <a href="javascript:void(0)" onclick="jQuery('#<?php echo esc_attr( $partner_id ) ?>').toggle()">Show error</a>
                                    <span id="fail-<?php echo esc_attr( $partner_id ) ?>" style="display:none;"><?php echo esc_html( $fail ) ?></span>
                                    <?php
                                }
                                ?>
                            </td>
                            <td>
                                <button name="new-<?php echo esc_attr( $site['type'] ) ?>-snapshot" type="submit" value="<?php echo esc_attr( $site['type_id'] ) ?>" class="button" >Refresh Snapshot</button>
                            </td>
                            <td style="width:100px; border-left: 1px solid lightgrey;">
                                Records:
                                 <?php
                                     echo esc_html( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM $wpdb->dt_movement_log WHERE site_id = %s", $site['site_id'] ) ) );
                                    ?>
                            </td>

                            <td>
                                <button name="delete-<?php echo esc_attr( $site['type'] ) ?>-activity" type="submit" value="<?php echo esc_attr( $site['id'] ) ?>" class="button" >Delete All Site Activity</button>
                                <button name="new-<?php echo esc_attr( $site['type'] ) ?>-activity" type="submit" value="<?php echo esc_attr( $site['id'] ) ?>" class="button" >Collect New Activity</button>
                            </td>
                        </tr>
                        <?php
                    } // end foreach
                }
                else {
                    ?>
                    <tr>
                        <td colspan="6">
                            No dashboard sites found.
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <div style="display:none;padding:2em;" id="fail-error"></div>
        </form>
        <br>
        <!-- End Box -->
        <?php
    }

    public function box_site_project_details() {
        if ( isset( $_POST['refresh_profiles'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['refresh_profiles'] ) ), 'refresh_profiles'.get_current_user_id() ) && isset( $_POST['refresh'] ) && ! empty( $_POST['refresh'] ) ) {
            if ( ! function_exists( 'dt_network_dashboard_profiles_update' ) ) {
                require_once( plugin_dir_path( __DIR__ ) . 'cron/cron-7-profile-update.php' );
            }
            dt_network_dashboard_profiles_update();

            if ( get_option( 'dt_network_dashboard_migration_lock' ) ){
                delete_option( 'dt_network_dashboard_migration_lock' );
                delete_option( 'dt_network_dashboard_migration_number' );
            }
        }
        $sites = DT_Network_Dashboard_Site_Post_Type::all_sites();
        $current_profile = dt_network_site_profile();
        ?>
        <!-- Box -->
        <p><strong>System Details</strong></p>
        <form method="POST">
        <?php wp_nonce_field( 'refresh_profiles'.get_current_user_id(), 'refresh_profiles' ) ?>

        <table class="widefat striped">
            <thead>
            <tr>
                <th>Site</th>
                <th>Last Collection</th>
                <th>Network Dashboard</th>
                <th>Disciple Tools</th>
                <th>Misc
                <span style="float:right;">
                    <button type="submit" name="refresh" value="true" class="button">Refresh</button>
                </span></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <?php echo esc_html( $current_profile['partner_name'] ) ?><br>
                    (local site)
                </td>
                <td>
                    <?php
                    $snapshot = DT_Network_Dashboard_Snapshot::snapshot_report();
                    echo esc_html( gmdate( 'Y-m-d H:i:s', $snapshot['timestamp'] ) );
                    ?>
                </td>
                <td>
                    <?php
                    if ( isset( $current_profile['system'] ) ) {
                        foreach ( $current_profile['system'] as $key => $label ){
                            if ( 'network' !== substr( $key, 0, 7 ) ){
                                continue;
                            }
                            echo esc_html( ucwords( str_replace( '_', ' ', $key ) ) ) . ': ' . esc_html( $label ) . '<br>';
                        }
                    }
                    ?>
                </td>
                <td>
                    <?php
                    if ( isset( $current_profile['system'] ) ) {
                        foreach ( $current_profile['system'] as $key => $label ){
                            if ( 'dt_' !== substr( $key, 0, 3 ) ){
                                continue;
                            }
                            echo esc_html( ucwords( str_replace( '_', ' ', $key ) ) ) . ': ' . esc_html( $label ) . '<br>';
                        }
                    }
                    ?>
                </td>
                <td>
                    <?php
                    if ( isset( $current_profile['system'] ) ) {
                        foreach ( $current_profile['system'] as $key => $label ){
                            if ( 'dt_' === substr( $key, 0, 3 ) || 'network' === substr( $key, 0, 7 ) ){
                                continue;
                            }
                            echo esc_html( ucwords( str_replace( '_', ' ', $key ) ) ) . ': ' . esc_html( $label ) . '<br>';
                        }
                    }
                    ?>
                </td>
            </tr>
            <?php
            foreach ( $sites as $site ){
                if ( 0 === $site['id'] ){
                    continue;
                }
                if ( 'multisite' === $site['type'] && ! dt_network_dashboard_multisite_is_approved() ){
                    continue;
                }
                $non_wp = empty( $site['non_wp'] );
                ?>
                    <tr>
                    <td>
                    <?php echo esc_html( $site['name'] ) ?>
                    </td>
                    <td>
                    <?php
                    if ( $non_wp ) {
                        echo esc_html( gmdate( 'Y-m-d H:i:s', $site['snapshot_timestamp'] ) );
                    }
                    ?>
                    </td>
                    <td>
                    <?php
                    if ( isset( $site['profile']['system'] ) ) {
                        foreach ( $site['profile']['system'] as $key => $label ){
                            if ( 'network' !== substr( $key, 0, 7 ) ){
                                continue;
                            }
                            echo esc_html( ucwords( str_replace( '_', ' ', $key ) ) ) . ': ' . esc_html( $label ) . '<br>';
                        }
                    }
                    ?>
                    </td>
                    <td>
                    <?php
                    if ( isset( $site['profile']['system'] ) ) {
                        foreach ( $site['profile']['system'] as $key => $label ){
                            if ( 'dt_' !== substr( $key, 0, 3 ) ){
                                continue;
                            }
                            echo esc_html( ucwords( str_replace( '_', ' ', $key ) ) ) . ': ' . esc_html( $label ) . '<br>';
                        }
                    }
                    ?>
                    </td>
                    <td>
                    <?php
                    if ( isset( $site['profile']['system'] ) ) {
                        foreach ( $site['profile']['system'] as $key => $label ){
                            if ( 'dt_' === substr( $key, 0, 3 ) || 'network' === substr( $key, 0, 7 ) ){
                                continue;
                            }
                            echo esc_html( ucwords( str_replace( '_', ' ', $key ) ) ) . ': ' . esc_html( $label ) . '<br>';
                        }
                    }
                    ?>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        </form>
        <br>
        <!-- End Box -->
        <?php
    }

    public function box_registered_key_list() {
        $actions = dt_network_dashboard_registered_actions();
        $sites = DT_Network_Dashboard_Site_Post_Type::all_sites();
        $current_site_id = dt_network_site_id();
        ?>
        <!-- Box -->
        <p><strong>Activity Keys and Counts</strong></p>
        <table class="widefat striped">
            <thead>
            <tr><th>Key</th><td>Labels</td><td>This Site</td><td>Other Sites</td></tr>
            </thead>
            <tbody>
            <?php
            foreach ( $actions as $value ){
                ?>
                <tr>
                    <td>
                        <?php echo esc_attr( $value['key'] ) ?>
                    </td>
                    <td>
                        <?php echo esc_attr( $value['label'] ) ?>
                    </td>
                    <td>
                        <?php
                         global $wpdb;
                         echo esc_html( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM $wpdb->dt_movement_log WHERE action = %s AND site_id = %s", $value['key'], $current_site_id ) ) );
                        ?>
                    </td>
                    <td>
                        <?php
                         global $wpdb;
                         echo esc_html( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM $wpdb->dt_movement_log WHERE action = %s AND site_id != %s", $value['key'], $current_site_id ) ) );
                        ?>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <table class="widefat striped">
            <thead>
            <tr><th>Site Name</th><td>Site ID</td><td>ND Post ID</td><td>Type ID</td></tr>
            </thead>
            <tbody>
            <?php
            foreach ( $sites as $site ){
                ?>
                <tr>
                    <td>
                        <?php echo esc_attr( $site['name'] ) ?>
                    </td>
                    <td>
                       <?php echo esc_attr( $site['partner_id'] ) ?>
                    </td>
                    <td>
                        <?php echo esc_attr( $site['id'] ) ?>
                    </td>
                    <td>
                        <?php echo esc_attr( $site['type_id'] ) ?>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }

    public function box_utilities() {
        $trigger_refresh = false;
        if ( isset( $_POST['utilities_nonce'] )
            && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['utilities_nonce'] ) ), 'utilities_' . get_current_user_id() )
             ) {

            if ( isset( $_POST['trigger'] ) ) {
                dt_network_dashboard_trigger_sites();
            }

            if ( isset( $_POST['migrations'] ) ) {
                delete_option( 'dt_network_dashboard_migration_lock' );
                delete_option( 'dt_network_dashboard_migration_number' );
                $trigger_refresh = true;
            }
        }
        ?>
        <!-- Box -->
        <p><strong>System Utilities</strong></p>
        <form method="POST">
        <?php wp_nonce_field( 'utilities_' . get_current_user_id(), 'utilities_nonce' ) ?>
        <table class="widefat striped">
            <tbody>
                <tr>
                    <td>
                        Trigger all remote sites to wake up and rebuild snapshot and activity collection. Sites that have not had traffic recently, may not have been triggered to rebuild their snapshot.
                    </td>
                    <td>
                        <button type="submit" class="button" style="float:right;" value="trigger" name="trigger">Trigger Sites</button>
                    </td>
                </tr>
                <tr>
                    <td>
                        Rebuild stuck migrations. Current migration number: <?php echo esc_html( get_option( 'dt_network_dashboard_migration_number' ) ) ?> <?php echo ( $trigger_refresh ) ? '<a href="" class="button">Refresh The Page For An Accurtate Migration Number</a>' : ''; ?>
                    </td>
                    <td>
                        <button type="submit" class="button" style="float:right;" value="migrations" name="migrations">Rebuild Migrations</button>
                    </td>
                </tr>
            </tbody>
        </table>
        </form>
        <br>
        <!-- End Box -->
        <?php
    }

    public function box_cron_list() {

        if ( isset( $_POST['cron_run_nonce'] )
            && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['cron_run_nonce'] ) ), 'cron_run_' . get_current_user_id() )
             ) {

            if ( isset( $_POST['run_now'] ) ){
                $hook = sanitize_text_field( wp_unslash( $_POST['run_now'] ) );
                $timestamp = wp_next_scheduled( $hook );
                wp_unschedule_event( $timestamp, $hook );
            }

            if ( isset( $_POST['trigger'] ) ) {
                dt_network_dashboard_trigger_sites();
            }
        }
        $cron_list = _get_cron_array();
        ?>
        <!-- Box -->
        <p><strong>Collection Schedule</strong></p>
        <table class="widefat striped">
            <thead>
            <tr>
                <th>Next Collection</th>
                <th>Collection Name</th>
                <th>Schedule Definition</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ( $cron_list as $time => $time_array ){
                foreach ( $time_array as $token => $token_array ){
                    if ( 'dt_' === substr( $token, 0, 3 ) ){
                        foreach ( $token_array as $key => $items ) {
                            ?>
                            <tr>
                                <td>
                                    <?php echo 'Next event in ' . esc_html( round( ( $time - time() ) / 60 / 60, 1 ) ) . ' hours' ?><br>
                                    <?php echo esc_html( gmdate( 'Y-m-d H:i:s', $time ) ) ?><br>
                                </td>
                                <td>
                                    <?php echo esc_attr( $token ) ?>
                                </td>
                                <td>
                                    <?php echo esc_html( $items['schedule'] ?? '' ) ?><br>
                                    Every <?php echo isset( $items['interval'] ) ? esc_html( $items['interval'] / 60 ) . ' minutes' : '' ?><br>
                                    <?php echo ! empty( $items['args'] ) ? esc_html( serialize( $items['args'] ) ) : '' ?><br>
                                </td>
                                <td>
                                    <form method="post">
                                        <?php wp_nonce_field( 'cron_run_' . get_current_user_id(), 'cron_run_nonce' ) ?>
                                        <button type="submit" name="run_now" style="float:right;" value="<?php echo esc_attr( $token ) ?>" class="button">Delete and Respawn</button>
                                    </form>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                }
            }
            ?>
            </tbody>
        </table>

        <br>
        <!-- End Box -->
        <?php
    }

    public function box_txt_log_list() {
        if ( isset( $_POST['reset-logs'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['reset-logs'] ) ), 'reset-logs'.get_current_user_id() ) ) {
            dt_reset_log( 'multisite' );
            dt_reset_log( 'activity-remote' );
            dt_reset_log( 'remote' );
            dt_reset_log( 'management' );
            dt_reset_log( 'activity-multisite' );
            dt_reset_log( 'profile-collection' );
        }
        ?>
        <p><strong>Cron Logs</strong></p>
        <form method="POST">
            <?php wp_nonce_field( 'reset-logs'.get_current_user_id(), 'reset-logs' ) ?>
            <table class="widefat striped">
                <tbody>
                <tr>
                    <td>
                        <?php if ( dt_network_dashboard_multisite_is_approved() ) : ?>
                        <a href="<?php echo esc_url( dt_get_log_location( 'multisite', 'url' ) ) ?>" target="_blank"><?php echo esc_url( dt_get_log_location( 'multisite', 'url' ) ) ?></a><br>
                        <a href="<?php echo esc_url( dt_get_log_location( 'activity-multisite', 'url' ) ) ?>" target="_blank"><?php echo esc_url( dt_get_log_location( 'activity-multisite', 'url' ) ) ?></a><br>
                        <?php endif; ?>

                        <a href="<?php echo esc_url( dt_get_log_location( 'management', 'url' ) ) ?>" target="_blank"><?php echo esc_url( dt_get_log_location( 'management', 'url' ) ) ?></a><br>
                        <a href="<?php echo esc_url( dt_get_log_location( 'remote', 'url' ) ) ?>" target="_blank"><?php echo esc_url( dt_get_log_location( 'remote', 'url' ) ) ?></a><br>
                        <a href="<?php echo esc_url( dt_get_log_location( 'activity-remote', 'url' ) ) ?>" target="_blank"><?php echo esc_url( dt_get_log_location( 'activity-remote', 'url' ) ) ?></a><br>
                        <a href="<?php echo esc_url( dt_get_log_location( 'profile-collection', 'url' ) ) ?>" target="_blank"><?php echo esc_url( dt_get_log_location( 'profile-collection', 'url' ) ) ?></a><br>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span style="float:right;">
                            <button type="submit" name="refresh" value="true" class="button">Refresh</button>
                        </span>
                    </td>
                    </tr>
                </tbody>
            </table>
        </form>
        <br>
        <?php
    }

}

/**
 * Class DT_Network_Dashboard_Tab_Tutorial
 */
class DT_Network_Dashboard_Tab_Upgrades
{
    public function content() {
        DT_Network_Dashboard_Snapshot::snapshot_report();
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->box_mapbox_status() ?>
                        <?php $this->box_ipstack_api_key() ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->

                        <?php $this->box_instructions() ?>

                        <!-- End Right Column -->
                    </div><!-- postbox-container 1 -->
                    <div id="postbox-container-2" class="postbox-container">
                    </div><!-- postbox-container 2 -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function box_mapbox_status() {
        DT_Mapbox_API::metabox_for_admin();
    }

    public function box_ipstack_api_key(){
        DT_Ipstack_API::metabox_for_admin();
    }

    public function box_instructions() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
            <th>Integrations</th>
            </thead>
            <tbody>
            <tr>
                <td>
                    These are a few third party enhancements you can add to improve your Disciple Tools and Network Dashboard system.
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Mapbox</strong><br>
                    Mapbox adds next level mapping and display to the Network Dashboard.
                </td>
            </tr>
            <tr>
                <td>
                    <strong>IPStack</strong><br>
                    Adds the capacity to translate IP Addresses of visitors into geolocation.
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
 * Class DT_Network_Dashboard_Tab_Tutorial
 */
class DT_Network_Dashboard_Tab_Tutorial
{
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->main() ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->

                        <?php $this->sidebar() ?>
                        <?php $this->overview_message() ?>

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
                        <dd>
                            Collecting reports across many systems is difficult and doing it automatically, even more so. Making sure
                            counts for certain location are counted only once you need a shared database of locations to post counts to.
                            This network mapping plugin attempts to set up a globally consistent mapping schema.
                        </dd>
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
            <tr>
                <td>
                    Cron is a time based task often scheduled to occur at regular times. You can have some tasks in a software run instantly, while others might build up and be run at a certain time.
                    The Network Dashboard collects snapshots from remote sites in this time based way. Because these are processor intensive tasks/queries we do not run them instantly, so as to protect the performance of the
                    Disciple.Tools system.
                </td>
            </tr>
            <tr>
                <td>
                    Although it is not required, if there are concerns about the timely distribution of posts/messages/emails or collection of snapshots, then it is very simple
                    to add an external cron service.
                </td>
            </tr>
            <tr>
                <td>
                    <p><strong>EXPLANATION</strong><br></p>
                    Wordpress/Disciple.Tools Cron System depends on visits to trigger background processes. If the site is not visited regularly
                    like a normal website would be, it is possible to use an external cron service to call the site regularly and trigger these
                    background tasks. If the Network Dashboard is configured for frequent collections in the section above and you notice
                    these services not running when expected, you can schedule an external cron service to connect to the site on a regular basis.
                </td>
            </tr>
            <tr>
                <td>
                    <p><strong>SERVICES</strong></p>
                    <ul>
                        <li><a href="https://cron-job.org/en/">Cron-Job.org</a></li>
                        <li><a href="https://www.easycron.com/">EasyCron</a></li>
                        <li><a href="https://cronless.com/">Cronless</a></li>
                        <li>Or Google "free cron services"</li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td>
                    <p><strong>CRON URL</strong><br></p>
                    <code><?php echo esc_url( site_url() ) . '/wp-cron.php' ?></code>
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }

    public function main() {
        ?>
        <style>dt { font-weight:bold;}</style>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
            <th>Tutorials</th>
            </thead>
            <tbody>
            <tr>
                <td>
                    <dl>
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

    public function sidebar() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
            <th>Outline</th>
            </thead>
            <tbody>
            <tr>
                <td>

                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }
}
