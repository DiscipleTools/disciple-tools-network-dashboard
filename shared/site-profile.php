<?php

/**
 * Class DT_Network_Dashboard_Site_Link_Metabox
 */
class DT_Network_Dashboard_Site_Link_Metabox {
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()
    public function __construct() {

        global $pagenow;
        if ( isset( $_GET['post'] ) && 'post.php' === $pagenow ) {
            $post_id = sanitize_key( wp_unslash( $_GET['post'] ) );

            if ( 'network_dashboard' === substr( get_post_meta( $post_id, 'type', true ), 0, 17 )
            ) {
                add_action( 'admin_menu', [ $this, 'meta_box_setup' ], 20 );
            }
        }
    }

    public function meta_box_setup() {
        add_meta_box( 'site_link_network_dashboard_box', __( 'Network Dashboard Site Profile', 'disciple_tools' ), [ $this, 'load_site_profile_meta_box' ], 'site_link_system', 'normal', 'low' );
    }

    public function load_site_profile_meta_box( $post = null ) {
        if ( ! isset( $post->ID ) ) {
            global $pagenow;
            if ( isset( $_GET['post'] ) && 'post.php' === $pagenow ) {
                $post_id = sanitize_key( wp_unslash( $_GET['post'] ) );
            } else {
                ?>
                Failed to get post id. Check connection. Error has been logged.
                <?php
                return;
            }
        } else {
            $post_id = $post->ID;
        }

        $new_profile = DT_Network_Dashboard_Site_Post_Type::create_remote_by_id( $post_id );
        if( is_wp_error( $new_profile ) ){
            dt_write_log($new_profile);
            ?>
            Failed to refresh remote site profile. Check connection. Error has been logged.
            <span style="float:right">Status: <strong><span id="fail-profile-status" class="fail-read" style="color:red;">Failed connection to remote Network Dashboard.</span></strong></span>
            <?php
        }

        $dt_network_dashboard_id = get_post_meta( $post_id, 'dt_network_dashboard', true );
        $site_profile = DT_Network_Dashboard_Site_Post_Type::get_profile( $dt_network_dashboard_id );
        if( is_wp_error( $site_profile ) ){
            dt_write_log($site_profile);
            ?>
            Failed to refresh remote site profile. Check connection. Error has been logged.
            <span style="float:right">Status: <strong><span id="fail-profile-status" class="fail-read" style="color:red;">Failed connection to remote Network Dashboard.</span></strong></span>
            <?php
            return;
        }
        ?>
        <span style="float:right">Status: <strong><span id="site-profile-status" class="success-green">Linked</span></strong></span>
        <table>
            <tr>
                <td>
                    <?php echo  $site_profile['partner_name'] ?? 'Partner Name' ?>
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo  $site_profile['partner_url'] ?? 'Partner URL' ?>
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo  $site_profile['partner_id'] ?? 'Partner ID' ?>
                </td>
            </tr>
        </table>
        <?php
    }

    public static function admin_box_local_site_profile()
    {
        $partner_profile = dt_network_site_profile();

        // process post action
        if (isset($_POST['partner_profile_form'])
            && isset($_POST['_wpnonce'])
            && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'partner_profile' . get_current_user_id())
            && isset($_POST['partner_name'])
            && isset($_POST['partner_description'])
        ) {

            $partner_name = sanitize_text_field( wp_unslash( $_POST['partner_name'] ) );
            $partner_profile['partner_name'] = $partner_name;

            $partner_description = sanitize_text_field( wp_unslash( $_POST['partner_description'] ) );
            $partner_profile['partner_description'] = $partner_description;

            update_option('dt_site_profile', $partner_profile, true);
            $partner_profile = dt_network_site_profile();
        }

        ?>
        <!-- Box -->
        <form method="post">
            <?php wp_nonce_field('partner_profile' . get_current_user_id()); ?>
            <table class="widefat striped">
                <thead>
                <tr><th>Network Profile</th></tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <table class="widefat">
                            <tbody>
                            <tr>
                                <td><label for="partner_name">Your Site Name</label></td>
                                <td><input type="text" class="regular-text" name="partner_name"
                                           id="partner_name"
                                           value="<?php echo esc_html($partner_profile['partner_name']) ?>"/></td>
                            </tr>
                            <tr>
                                <td><label for="partner_description">Your Site Description</label></td>
                                <td><input type="text" class="regular-text" name="partner_description"
                                           id="partner_description"
                                           value="<?php echo esc_html($partner_profile['partner_description']) ?>"/>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="partner_id">Your Site ID</label></td>
                                <td><?php echo esc_attr($partner_profile['partner_id']) ?></td>
                            </tr>
                            <tr>
                                <td><label for="partner_id"><a href="/wp-admin/admin.php?page=dt_options&tab=custom-lists#languages">Languages</a></label></td>
                                <td><?php
                                    if ( isset( $partner_profile['languages'] ) ) {
                                        $i = 0;
                                        foreach ( $partner_profile['languages'] as $key => $label ){
                                            if ( 0 !== $i ){
                                                echo ', ';
                                            }
                                            echo $label['label'];
                                            $i++;
                                        }
                                    }
                                    ?></td>
                            </tr>
                            </tbody>
                        </table>

                        <p><br>
                            <button type="submit" id="partner_profile_form" name="partner_profile_form"
                                    class="button">Update
                            </button>
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
DT_Network_Dashboard_Site_Link_Metabox::instance();

class DT_Network_Dashboard_Site_Links_Endpoint extends DT_Network_Dashboard_Endpoints_Base
{
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        parent::__construct();
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    } // End __construct()

    public function add_api_routes() {
        register_rest_route(
            $this->public_namespace, '/network_dashboard/profile', [
                'methods'  => 'POST',
                'callback' => [ $this, 'profile' ],
            ]
        );
    }
    public function profile( WP_REST_Request $request ) {
        $params = $this->process_token( $request );
        if ( is_wp_error( $params ) ) {
            return [
                'status' => 'FAIL',
                'error' => $params,
            ];
        }
        return dt_network_site_profile();
    }
}
DT_Network_Dashboard_Site_Links_Endpoint::instance();

/**
 * Gets or Creates Network Site Profile
 * @return array
 */
if ( ! function_exists('dt_network_site_profile') ) {
    function dt_network_site_profile() {
        $profile = get_option('dt_site_profile');

        if ( empty( $profile ) || empty( $profile['partner_id'] || ! isset( $profile['partner_id'] ) ) ) {
            $profile = [
                'partner_id' => dt_network_site_id(),
                'partner_name' => get_option('blogname'),
                'partner_description' => get_option('blogdescription'),
                'partner_url' => site_url()
            ];
            update_option('dt_site_profile', $profile, true);
        }

        $profile['system'] = dt_network_site_system();

        $profile['languages'] = dt_get_option( "dt_working_languages" );

        return $profile;
    }
}


/**
 * Gets/Creates a Permanent ID for the Disciple Tools site. This allows for network duplicate checking etc.
 * @return string
 * @throws Exception
 */
if ( ! function_exists('dt_network_site_id') ) {
    function dt_network_site_id()
    {
        $site_id = get_option('dt_site_id');
        if (empty($site_id)) {
            $site_id = hash('sha256', bin2hex(random_bytes(40)));
            add_option('dt_site_id', $site_id, '', 'yes');
        }
        return $site_id;
    }
}
/**
 * @return array
 * @throws Exception
 */
if ( ! function_exists('dt_network_site_system') ) {
    function dt_network_site_system() : array {
        global $wp_version, $wp_db_version;

        $system = [
            'network_dashboard_version' => DT_Network_Dashboard::get_instance()->version ?? 0,
            'network_dashboard_migration' => get_option('dt_network_dashboard_migration_number'),
            'network_dashboard_migration_lock' => get_option('dt_network_dashboard_migration_lock'),
            'dt_theme_version' => Disciple_Tools::instance()->version ?? 0,
            'dt_theme_migration' => get_option('dt_migration_number'),
            'dt_theme_migration_lock' => get_option('dt_migration_lock'),
            'dt_mapping_migration' => get_option('dt_mapping_module_migration_number'),
            'dt_mapping_migration_lock' => get_option('dt_mapping_module_migration_lock'),
            'has_mapbox_key' => ( DT_Mapbox_API::get_key() ) ? 'yes' : 'no',
            'php_version' => phpversion(),
            'wp_version' => $wp_version,
            'wp_db_version' => $wp_db_version,
        ];

        return $system;
    }
}
