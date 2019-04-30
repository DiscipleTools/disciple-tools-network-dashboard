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
        add_action( "admin_head", [ $this, 'header_script' ] );
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
            [ $this, 'extensions_menu' ],
            'dashicons-admin-generic',
        59 );
        add_submenu_page( 'dt_extensions',
            __( 'Network Dashboard', 'dt_network_dashboard' ),
            __( 'Network Dashboard', 'dt_network_dashboard' ),
            'manage_dt',
            $this->token,
        [ $this, 'content' ] );
    }

    public function header_script() {
        ?>
        <style>
            a.pointer { cursor: pointer; }
        </style>
        <script>
            jQuery(document).ready(function(){
                // add javascript here
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

        if ( dt_multisite_network_dashboard_is_approved() ) {
            $snapshot_tab = 'Remote Snapshots';
            $approved_multisite = true;
        } else {
            $snapshot_tab = 'Snapshots';
            $approved_multisite = false;
        }

        ?>
        <div class="wrap">
            <h2><?php esc_attr_e( 'Network Dashboard', 'dt_network_dashboard' ) ?></h2>
            <h2 class="nav-tab-wrapper">
                <a href="<?php echo esc_attr( $link ) . 'general' ?>" class="nav-tab
                <?php ( $tab == 'general' || ! isset( $tab ) ) ? esc_attr_e( 'nav-tab-active', 'dt_network_dashboard' ) : print ''; ?>">
                    <?php esc_attr_e( 'Overview', 'dt_network_dashboard' ) ?></a>

                <a href="<?php echo esc_attr( $link ) . 'remote-snapshots' ?>" class="nav-tab
                <?php echo ( $tab == 'remote-snapshots' ) ? 'nav-tab-active' : ''; ?>">
                    <?php echo $snapshot_tab ?>
                </a>

                <?php if ( $approved_multisite ) :  // check if approved multisite dashboard?>

                <a href="<?php echo esc_attr( $link ) . 'multisite-snapshots' ?>" class="nav-tab
                    <?php echo ( $tab == 'multisite-snapshots' ) ? 'nav-tab-active' : ''; ?>">
                    <?php echo 'Multisite/Local Snapshots' ?>
                </a>

                <?php endif; ?>

            </h2>

            <?php
            switch ($tab) {
                case "general":
                    $object = new DT_Network_Dashboard_Tab_General();
                    $object->content();
                    break;
                case "remote-snapshots":
                    $object = new DT_Network_Dashboard_Tab_Remote_Snapshots();
                    $object->content();
                    break;
                case "multisite-snapshots":
                    $object = new DT_Network_Dashboard_Tab_Multisite_Snapshots();
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

}


class DT_Network_Dashboard_Tab_Remote_Snapshots
{
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-1">
                    <div id="post-body-content">
                        <!-- Main Column -->
                        <?php $this->process_full_list() ?>
                        <?php $this->main_column() ?>
                        <?php $this->logging_viewer() ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->

                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function process_full_list() {
        ?>
        <p><strong>Snapshot Collection</strong></strong></p>
        <table class="widefat striped">
            <tbody>
                <tr>
                    <td>
                        <button class="button" id="rerun-collection" type="button">Refresh All Snapshots</button>
                        &nbsp;<span id="rerun-collection-spinner" style="display: none;"><img src="<?php echo plugin_dir_url( __DIR__ ) ?>spinner.svg" width="25px" /></span>
                        &nbsp;<span id="result-message"></span>

                        <span style="float:right;"><a href="#logs">View logs</a></span>
                    </td>
                </tr>
            </tbody>
        </table>
        <script>
        jQuery(document).ready(function() {
            jQuery('#rerun-collection').click( function() {
                let spinner = jQuery('#rerun-collection-spinner')
                spinner.show()
                jQuery.ajax({
                    type: "POST",
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    url: '<?php echo esc_url( rest_url() ) ?>dt/v1/network/admin/trigger_snapshot_collection',
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>');
                    },
                })
                    .done(function (data) {
                        console.log(data)
                        if ( data ) {
                            spinner.hide()
                            jQuery('#result-message').html('Snapshot refresh began successfully. Check logs below for updates.')
                        }
                    })
                    .fail(function (err) {
                        console.log(err)
                        jQuery('#result-message').html('Snapshot refresh unsuccessful. View console for errors.')
                    })
            })
        })
        </script>
        <?php
    }

    public function main_column() {
        $message = false;
        if ( isset( $_POST['network_dashboard_nonce'] )
            && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['network_dashboard_nonce'] ) ), 'network_dashboard_' . get_current_user_id() )
            && $_POST['new-snapshot'] ) {

            dt_save_log( 'remote', '', false );
            dt_save_log( 'remote', 'REFRESH SNAPSHOT', false );
            $result = dt_get_site_snapshot( intval( sanitize_key( wp_unslash( $_POST['new-snapshot'] ) ) ) );
            if ( $result ) {
                $message = [ 'notice-success','Successful collection of new snapshot.' ];
            }
            else {
                $message = [ 'notice-error', 'Failed collection' ];
            }
        }
        // Get list of sites
        $sites = DT_Network_Dashboard_Queries::site_link_list();

        /** Message */
        if ( $message ) {
            echo '<div class="notice '.esc_attr( $message[0] ).'">'.esc_html( $message[1] ).'</div>';
        }

        /** Box */
        ?>
        <form method="post">
            <?php wp_nonce_field( 'network_dashboard_' . get_current_user_id(), 'network_dashboard_nonce' ) ?>
            <p><strong>Network Dashboard Snapshots of Connected Sites</strong></p>
            <table class="widefat striped">
                <thead>
                    <th></th>
                    <th>Site Name</th>
                    <th>ID</th>
                    <th>Snapshot</th>
                    <th>Last Snapshot</th>
                    <th>Success</th>
                    <th></th>
                </thead>
                <tbody>
                <?php
                if ( ! empty( $sites ) ) {
                    $i = 1;
                    foreach ( $sites as $site ) {
                        $site_meta = get_post_meta( $site['id'] );
                        if ( isset( $site_meta['snapshot_fail'][0] ) && ! empty( $site_meta['snapshot_fail'][0] ) ) {
                            $fail = maybe_serialize( $site_meta['snapshot_fail'][0] );
                        } else {
                            $fail = '';
                        }
                        ?>
                        <tr>
                            <td style="width:10px;">
                                <?php echo $i;
                                $i++; ?>
                            </td>
                            <td>
                               <?php echo $site['name'] ?>
                            </td>
                            <td>
                               <?php echo $site['id'] ?>
                            </td>
                            <td>
                               <?php echo ( ! empty( $site_meta['snapshot'][0] ) ) ? '&#x2714;' : '&#x2718;' ?>
                            </td>
                            <td>
                               <?php echo ( ! empty( $site_meta['snapshot_date'][0] ) ) ? date( 'Y-m-d H:i:s', $site_meta['snapshot_date'][0] ) : '&#x2718;' ?>
                            </td>
                            <td>
                                <?php echo ( empty( $site_meta['snapshot_fail'][0] )
                                        && ! empty( $site_meta['snapshot'][0] ) ) ? '&#x2714;' :
                                    '<span style="color:red;" onclick="jQuery(\'#fail-error\').show().append(jQuery(\'#fail-'.$site['id'].'\').html())">&#x2718; view error below</span>
                                     <span style="display:none;" id="fail-'.$site['id'].'">'. $fail .'</span>'
                                ?>
                            </td>
                            <td>
                                <button value="<?php echo esc_attr( $site['id'] ) ?>" name="new-snapshot" type="submit" class="button" >Refresh Snapshot</button>
                            </td>
                        </tr>
                        <?php
                    }
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

    public function logging_viewer() {
        if ( ! file_exists( dt_get_log_location( 'remote' ) ) ) {
            dt_save_log( 'remote', 'New log file' );
            dt_reset_log( 'remote' );
        }
        ?>
        <a id="logs"></a>
        <div style="padding: 1.2em;"><strong>Recent Cron Log</strong> <span style="float:right;"> <a href="javascript:void(0)" onclick="document.getElementById('log_viewer').contentWindow.location.reload();">reload</a></span></div>
        <table class="widefat striped">
            <tr>
                <td>
                    <iframe id="log_viewer" src="<?php echo dt_get_log_location( 'remote', 'url' ) ?>" width="100%" height="800px" scrolling="1"></iframe>
                </td>
            </tr>
        </table>

        <?php
    }
}

class DT_Network_Dashboard_Tab_Multisite_Snapshots
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
                            $this->process_full_list();
                            $this->main_column();
                            $this->logging_viewer();
                        } else {
                            $this->not_approved_content();
                        }

                        ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->

                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function not_approved_content() {
        ?>
        <table class="widefat striped">
            <thead>
                <th>Not Yet Approved to Collect Reports from the Local Network</th>
            </thead>
            <tbody>
            <tr>
                <td>

                    <p>You are not yet approved to collect reports from the local network. Your network administrator must enable your dashboard to collect reports from this network.</p>
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <table class="widefat striped">
            <thead>
                <th>How does this work?</th>
            </thead>
            <tbody>
            <tr>
                <td>
                    <ol>
                        <li>
                            The network dashboard can collect reports from both "Remote Sites" which you setup through the Site Links system, or in a multisite installation, where many sites are hosted on one server
                            you can enable the network dashboard to collect reports from the "local" or multisite server installations of Disciple Tools.
                        </li>
                        <li>
                            Both remote and local sites can be collected from and are aggregated into dashboard totals.
                        </li>
                    </ol>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
    }

    public function process_full_list() {
        ?>
        <p><strong>Snapshot Collection</strong></strong></p>
        <table class="widefat striped">
            <tbody>
            <tr>
                <td>
                    <button class="button" id="rerun-collection" type="button">Refresh All Snapshots</button>
                    &nbsp;<span id="rerun-collection-spinner" style="display: none;"><img src="<?php echo plugin_dir_url( __DIR__ ) ?>spinner.svg" width="25px" /></span>
                    &nbsp;<span id="result-message"></span>

                    <span style="float:right;"><a href="#logs">View logs</a></span>
                </td>
            </tr>
            </tbody>
        </table>
        <script>
            jQuery(document).ready(function() {
                jQuery('#rerun-collection').click( function() {
                    let spinner = jQuery('#rerun-collection-spinner')
                    spinner.show()
                    jQuery.ajax({
                        type: "POST",
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        url: '<?php echo esc_url( rest_url() ) ?>dt/v1/network/admin/trigger_multisite_snapshot_collection',
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>');
                        },
                    })
                        .done(function (data) {
                            console.log(data)
                            if ( data ) {
                                spinner.hide()
                                jQuery('#result-message').html('Snapshot refresh began successfully. Check logs below for updates.')
                            }
                        })
                        .fail(function (err) {
                            console.log(err)
                            jQuery('#result-message').html('Snapshot refresh unsuccessful. View console for errors.')
                        })
                })
            })
        </script>
        <?php
    }

    public function main_column() {
        $message = false;
        if ( isset( $_POST['network_dashboard_nonce'] )
            && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['network_dashboard_nonce'] ) ), 'network_dashboard_' . get_current_user_id() )
            && $_POST['new-snapshot'] ) {

            dt_save_log( 'multisite', '', false );
            dt_save_log( 'multisite', 'REFRESH SNAPSHOT', false );
            $result = dt_get_multisite_snapshot( intval( sanitize_key( wp_unslash( $_POST['new-snapshot'] ) ) ) );
            if ( $result ) {
                $message = [ 'notice-success','Successful collection of new snapshot' ];
            }
            else {
                $message = [ 'notice-error', 'Failed collection' ];
            }
        }
        // Get list of sites


        $snapshots = dt_multisite_dashboard_snapshots();
        $number_of_snapshots = count( $snapshots );

        /** Message */
        if ( $message ) {
            echo '<div class="notice '.esc_attr( $message[0] ).'">'.esc_html( $message[1] ).'</div>';
        }

        /** Box */
        ?>
        <form method="post">
            <?php wp_nonce_field( 'network_dashboard_' . get_current_user_id(), 'network_dashboard_nonce' ) ?>
            <p><strong>Network Dashboard Snapshots of Connected Sites (<?php echo $number_of_snapshots; ?>)</strong></p>
            <table class="widefat striped">
                <thead>
                <th>ID</th>
                <th>Site Name</th>
                <th>Domain</th>
                <th>Snapshot</th>
                <th>Last Snapshot</th>
                <th></th>
                </thead>
                <tbody>
                <?php
                if ( ! empty( $snapshots ) ) {
                    foreach ( $snapshots as $blog_id => $snapshot ) {

                        ?>
                        <tr>
                            <td>
                                <?php echo $blog_id ?>
                            </td>
                            <td>
                                <?php echo '<strong>' . $snapshot['profile']['partner_name'] . '</strong>' ?>
                            </td>
                            <td>
                                <?php echo '<a href="'.get_site_url( $blog_id ) .'" target="_blank">' . get_site_url( $blog_id ) . '</a>' ?>
                            </td>
                            <td>
                                <?php echo ( ! empty( $snapshot ) ) ? '&#9989;' : '&#x2718;' ?>
                            </td>
                            <td>
                                <?php echo ( ! empty( $snapshot['date'] ) ) ? date( 'Y-m-d H:i:s', $snapshot['date'] ) : '&#x2718;' ?>
                            </td>
                            <td>
                                <button value="<?php echo esc_attr( $blog_id ) ?>" name="new-snapshot" type="submit" class="button" >Refresh Snapshot</button>
                            </td>
                        </tr>
                        <?php
                    }
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

    public function logging_viewer() {
        if ( ! file_exists( dt_get_log_location( 'multisite' ) ) ) {
            dt_save_log( 'multisite', 'New log file' );
            dt_reset_log( 'multisite' );
        }
        ?>
        <a id="logs"></a>
        <div style="padding: 1.2em;"><strong>Recent Cron Log</strong> <span style="float:right;"> <a href="javascript:void(0)" onclick="document.getElementById('log_viewer').contentWindow.location.reload();">reload</a></span></div>
        <table class="widefat striped">
            <tr>
                <td>
                    <iframe id="log_viewer" src="<?php echo dt_get_log_location( 'multisite', 'url' ) ?>" width="100%" height="800px" scrolling="yes"></iframe>
                </td>
            </tr>
        </table>
        <?php
    }
}

