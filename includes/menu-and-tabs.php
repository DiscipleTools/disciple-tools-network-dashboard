<?php
/**
 * DT_Saturation_Mapping_Menu class for the admin page
 *
 * @class       DT_Saturation_Mapping_Menu
 * @version     0.1.0
 * @since       0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}

/**
 * Initialize menu class
 */
DT_Saturation_Mapping_Menu::instance();

/**
 * Class DT_Saturation_Mapping_Menu
 */
class DT_Saturation_Mapping_Menu {

    public $token = 'dt_saturation_mapping';

    private static $_instance = null;

    /**
     * DT_Saturation_Mapping_Menu Instance
     *
     * Ensures only one instance of DT_Saturation_Mapping_Menu is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return DT_Saturation_Mapping_Menu instance
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

    } // End __construct()


    /**
     * Loads the subnav page
     * @since 0.1
     */
    public function register_menu() {
        add_menu_page( __( 'Extensions (DT)', 'disciple_tools' ), __( 'Extensions (DT)', 'disciple_tools' ), 'manage_dt', 'dt_extensions', [ $this, 'extensions_menu' ], 'dashicons-admin-generic', 59 );
        add_submenu_page( 'dt_extensions', __( 'Saturation Mapping', 'dt_saturation_mapping' ), __( 'Saturation Mapping', 'dt_saturation_mapping' ), 'manage_dt', $this->token, [ $this, 'content' ] );
//        add_submenu_page( 'edit.php?post_type=locations', __( 'Saturation Import', 'disciple_tools' ), __( 'Saturation Import', 'disciple_tools' ), 'manage_dt', 'dt_saturation_mapping&tab=second', [ $this, 'content' ] );
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
            <h2><?php esc_attr_e( 'Saturation Mapping', 'dt_saturation_mapping' ) ?></h2>
            <h2 class="nav-tab-wrapper">
                <a href="<?php echo esc_attr( $link ) . 'general' ?>" class="nav-tab <?php ( $tab == 'general' || ! isset( $tab ) ) ? esc_attr_e( 'nav-tab-active', 'dt_saturation_mapping' ) : print ''; ?>"><?php esc_attr_e( 'Configure', 'dt_saturation_mapping' ) ?></a>
                <a href="<?php echo esc_attr( $link ) . 'second' ?>" class="nav-tab <?php ( $tab == 'second' ) ? esc_attr_e( 'nav-tab-active', 'dt_saturation_mapping' ) : print ''; ?>"><?php esc_attr_e( 'Install', 'dt_saturation_mapping' ) ?></a>
            </h2>

            <?php
            switch ($tab) {
                case "general":
                    $this->general_content();
                    break;
                case "second":
                    $object = new DT_Saturation_Mapping_Tab_Install();
                    $object->content();
                    break;
                default:
                    break;
            }
            ?>

        </div><!-- End wrap -->

        <?php
    }

    public function general_content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->general_content_main() ?>

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

    public function general_content_main() {
        // process post action
        if ( isset( $_POST['population_division'] ) && ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'population_division'.get_current_user_id() ) ) ) {
            $new = (int) sanitize_text_field( wp_unslash( $_POST['population_division'] ) );
            update_option( 'dt_saturation_mapping_pd', $new, false );
        }
        $population_division = get_option( 'dt_saturation_mapping_pd' );
        if ( empty( $population_division ) ) {
            update_option( 'dt_saturation_mapping_pd', 5000, false );
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
}


/**
 * Class DT_Starter_Tab_Second
 */
class DT_Saturation_Mapping_Tab_Install
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
        $available_locations = DT_Saturation_Mapping_Installer::get_list_of_available_locations();
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
                        foreach ( $available_locations as $country_code => $name ) {
                            echo '<option value="' . $country_code . '">'.$name.'</option>';
                        }
                        ?>

                    </select>
                    <a href="javascript:void(0);" onclick="import_by_name()" class="button" id="import_button">Load</a>
                    <script>
                        function import_by_name() {
                            let button = jQuery('#import_button')
                            button.append(' <span><img src="<?php echo plugin_dir_url( __FILE__ ). '/'; ?>spinner.svg" width="12px" /></span>')

                            let country_code = jQuery('#selected_country').val()
                            let data = { "country_code": country_code }
                            jQuery.ajax({
                                type: "POST",
                                data: JSON.stringify(data),
                                contentType: "application/json; charset=utf-8",
                                dataType: "json",
                                url: '<?php echo esc_url_raw( rest_url() ); ?>dt/v1/saturation/load_by_country',
                                beforeSend: function(xhr) {
                                    xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce( 'wp_rest' ) ?>');
                                },
                            })
                                .done(function (data) {
                                    button.empty().append('Load')
                                    let result_div = jQuery('#results')
                                    result_div.empty()
                                    jQuery.each(data, function(i,v) {
                                        result_div.append( '<hr><dt><strong style="font-size:1.4em">' + v.name + '</strong> <a class="page-title-action"  onclick="install_admin1(\''+v.geonameid+'\')">Install</a> ' +
                                            '<span id="install-'+v.geonameid+'"></span> </dt>')

                                        jQuery.each(v.adm2, function(ii, vv) {
                                            result_div.append('<dd><strong>' + vv.name + '</strong> <a class="page-title-action" onclick="install_admin2(\''+vv.geonameid+'\')">Install</a> ' +
                                                '<span id="install-'+vv.geonameid+'"></span> <a class="page-title-action" onclick="install_cities(\''+vv.geonameid+'\')">Install All Cities</a> <span id="cities-'+vv.geonameid+'"></span></dd>')
                                        })
                                    })

                                    console.log( 'success ')
                                    console.log( data )
                                })
                                .fail(function (err) {
                                    console.log("error");
                                    console.log(err);
                                })
                        }
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
        return;
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
            <th>Information</th>
            </thead>
            <tbody>
            <tr>
                <td>
                    Content
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }
}

