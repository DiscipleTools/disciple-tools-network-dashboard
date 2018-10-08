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

        $this->get_sync_status( $post->ID );
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

    public function get_sync_status( $site_post_id ) {
        // get local count
        $data = dt_network_dashboard_queries( 'check_sum_list', [ 'site_post_id' => $site_post_id ] );

        // get remote count
        $remote_data = DT_Network_Dashboard_Reports::live_stats( $site_post_id, 'locations_list' );
        if ( ! empty( $remote_data ) ) {
            $remote_data = json_decode( $remote_data );
            $remote_data_count = count($remote_data);
        } else {
            $remote_data_count = 0;
        }


        echo '<hr>';
        echo 'Network Dashboard: ' .  esc_attr( count( $data ) ) . '<br>';
        echo 'This site at the other location: ' . esc_attr( $remote_data_count ) . '<br>';

        ?>
        <p><a onclick="trigger_outstanding_locations()" class="button pointer">Sync Records</a><span id="spinner_sync"></span></p>
        <script>
            function trigger_outstanding_locations() {
                let spinner_span = jQuery('#spinner_sync')
                spinner_span.append(' <img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ) . 'ajax-loader.gif' ?>" width="15px" />')

                let data3 = { "id": "<?php echo esc_attr( $site_post_id ); ?>", "type": "outstanding_site_locations" }
                jQuery.ajax({
                    type: "POST",
                    data: JSON.stringify(data3),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    url: '<?php echo esc_url( rest_url() ) ?>dt/v1/network/ui/trigger_transfer',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ) ?>' );
                    },
                })
                    .done(function (data) {
                        spinner_span.empty()
                        location.reload();
                        console.log(data)
                    })
                    .fail(function (err) {
                        spinner_span.empty()
                        console.log(err);
                    })
            }

        </script>

        <?php

    }
}
new DT_Network_Dashboard_Site_Profile_Metabox();
