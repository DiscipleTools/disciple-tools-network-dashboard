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
         * Create partner details if not present.
         */
        if ( ! get_post_meta( $post->ID, 'partner_id', true ) ) {
            $new_profile = $this->create_partner_profile( $post->ID );
            if( is_wp_error( $new_profile ) ){
                ?>
                Failed to connect to Network Dashboard site and collect profile details.
                <?php
                return;
            }
        }

        /**
         * Site Profile Info
         */
        $site_link_system = Site_Link_System::instance();
        $site_link_system->meta_box_content( 'network_dashboard' );

    }

    public function create_partner_profile( $site_post_id ) {

        $site = Site_Link_System::get_site_connection_vars( $site_post_id, 'post_id');
        if ( is_wp_error($site) ) {
            return $site;
        }

        // Send remote request
        $args = [
            'method' => 'POST',
            'body' => [
                'transfer_token' => $site['transfer_token'],
            ]
        ];
        $result = wp_remote_post( 'https://' . $site['url'] . '/wp-json/dt-public/v1/network_dashboard/profile', $args );
        if ( is_wp_error($result)) {
            return $result;
        }

        if ( isset( $snapshot['profile']['partner_id'] ) && ! empty( $snapshot['profile']['partner_id'] ) ) {
            $name = sanitize_text_field( wp_unslash( $snapshot['profile']['partner_id'] ) );
            update_post_meta( $site_post_id, 'partner_id', $name );
        } else {
            return new WP_Error(__METHOD__, 'Failed to get a properly configured partner_id.');
        }
        if ( isset( $snapshot['profile']['partner_name'] ) ) {
            $name = sanitize_text_field( wp_unslash( $snapshot['profile']['partner_name'] ) );
            if ( empty( $name ) ){
                $name = 'No Name Site';
            }
            update_post_meta( $site_post_id, 'partner_name', $name );
        } else {
            return new WP_Error(__METHOD__, 'Failed to get a properly configured partner_name.');
        }
        if ( isset( $snapshot['profile']['partner_description'] ) ) {
            $desc = sanitize_text_field( wp_unslash( $snapshot['profile']['partner_description'] ) );
            update_post_meta( $site_post_id, 'partner_description', $desc );
        } else {
            return new WP_Error(__METHOD__, 'Failed to get a properly configured partner_description.');
        }
        if ( isset( $snapshot['profile']['partner_url'] ) ) {
            update_post_meta( $site_post_id, 'partner_url', $snapshot['profile']['partner_url'] );
        } else {
            return new WP_Error(__METHOD__, 'Failed to get a properly configured partner_url.');
        }

        return true;
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
