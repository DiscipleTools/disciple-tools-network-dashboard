<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Network_Dashboard_Migration_0001
 */
class DT_Network_Dashboard_Migration_0001 extends DT_Network_Dashboard_Migration {
    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {
        $this->dt_network_site_profile();
    }

    public function dt_network_site_profile() {
        $profile = get_option( 'dt_site_profile' );

        $site_id = get_option( 'dt_site_id' ); // fix for previous non-sha256 keys
        if ( 65 < strlen( $site_id ) ){
            delete_option( 'dt_site_profile' );
            delete_option( 'dt_site_id' );
            $profile = get_option( 'dt_site_profile' );
        }

        if ( empty( $profile ) || empty( $profile['partner_id'] || ! isset( $profile['partner_id'] ) ) ) {
            $profile = array(
                'partner_id' => $this->dt_network_site_id(),
                'partner_name' => get_option( 'blogname' ),
                'partner_description' => get_option( 'blogdescription' ),
                'partner_url' => site_url()
            );
            update_option( 'dt_site_profile', $profile, true );
        }

        return $profile;
    }

    public function dt_network_site_id() {
        $site_id = get_option( 'dt_site_id' );

        if ( empty( $site_id )) {
            $site_id = hash( 'sha256', bin2hex( random_bytes( 40 ) ) );
            add_option( 'dt_site_id', $site_id, '', 'yes' );
        }
        return $site_id;
    }

    /**
     * @throws \Exception  Got error when dropping table $name.
     */
    public function down() {
    }

    /**
     * Test function
     */
    public function test() {
    }

}
