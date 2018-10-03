<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Network_Dashboard_Site_Profile_Metabox
 */
class DT_Network_Dashboard_Site_Profile_Metabox {
    public function __construct() {
        global $pagenow;
        if ( isset( $_GET['post'] ) && 'post.php' === $pagenow ) {
            $post_id = sanitize_key( wp_unslash( $_GET['post'] ) );

            if ( 'network_dashboard' === get_post_meta( $post_id, 'type', true )
                ) {
                add_action( 'admin_menu', [ $this, 'meta_box_setup' ], 20 );
                add_filter( "site_link_fields_settings", [ $this, 'network_field_filter' ], 1, 1 );
            }
        }
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

        /**
         * Site Locations Info
         */
        $site_locations = get_post_meta( $post->ID, 'partner_locations', true );
        if ( ! empty( $site_locations ) ) {

            echo '<hr>';

            $site_locations = maybe_unserialize( $site_locations );
            foreach ( $site_locations as $site_location ) {
                echo '<dd>'. $site_location['name'].'</dd>';
            }
        }
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

        return $fields;
    }
}
new DT_Network_Dashboard_Site_Profile_Metabox();
