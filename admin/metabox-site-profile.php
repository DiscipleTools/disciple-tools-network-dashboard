<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Network_Dashboard_Site_Profile_Metabox
 */
class DT_Network_Dashboard_Site_Profile_Metabox {
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
        add_filter( "site_link_fields_settings", [ $this, 'network_field_filter' ], 1, 1 );

    }

    public function meta_box_setup() {
        add_meta_box( 'site_link_network_dashboard_box', __( 'Dashboard Site Profile', 'disciple_tools' ), [ $this, 'load_site_profile_meta_box' ], 'site_link_system', 'normal', 'low' );
    }

    public function load_site_profile_meta_box( $post ) {
        /**
         * Site Profile Info
         */
        $site_link_system = Site_Link_System::instance();
        $site_link_system->meta_box_content( 'network_dashboard' );

    }

    public function network_field_filter( $fields ) {

        $fields['partner_name'] = [
            'name'        => 'Partner Name',
            'description' => '',
            'type'        => 'readonly',
            'default'     => '',
            'section'     => 'network_dashboard',
        ];
        $fields['partner_description'] = [
            'name'        => 'Partner Description',
            'description' => '',
            'type'        => 'readonly',
            'default'     => '',
            'section'     => 'network_dashboard',
        ];
        $fields['partner_id'] = [
            'name'        => 'Partner ID',
            'description' => '',
            'type'        => 'readonly',
            'default'     => '',
            'section'     => 'network_dashboard',
        ];
        $fields['send_activity_log'] = [
            'name'        => 'Send Activity Log',
            'description' => '',
            'type'        => 'key_select',
            'default'     => [
                'no' => 'No',
                'yes' => 'Yes',
            ],
            'section'     => 'network_dashboard',
        ];

        return $fields;
    }

}
DT_Network_Dashboard_Site_Profile_Metabox::instance();
