<?php

/**
 * Gets/Creates a Permanent ID for the Disciple Tools site. This allows for network duplicate checking etc.
 * @return string
 * @throws Exception
 */
if ( ! function_exists('dt_network_site_id') ) {
    function dt_network_site_id() {
        $site_id = get_option('dt_site_id');
        if ( empty( $site_id ) ) {
            $site_id =  hash( 'sha256', bin2hex( random_bytes(40) ) );
            add_option('dt_site_id', $site_id, '', 'yes');
        }
        return $site_id;
    }
}

/**
 * Gets or Creates Network Site Profile
 * @return array
 */
if ( ! function_exists('dt_network_site_profile') ) {
    function dt_network_site_profile() {
        $profile = get_option('dt_site_profile');

        if ( empty( $profile ) ) {
            $profile = [
                'partner_id' => dt_network_site_id(),
                'partner_name' => get_option('blogname'),
                'partner_description' => get_option('blogdescription'),
                'partner_url' => site_url()
            ];
            update_option('dt_site_profile', $profile, true);
        }

        $profile['system'] = dt_network_site_system();

        return $profile;
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
            'theme_version' => Disciple_Tools::instance()->version ?? 0,
            'dashboard_version' => DT_Network_Dashboard::get_instance()->version ?? 0,
            'php_version' => phpversion(),
            'wp_version' => $wp_version,
            'wp_db_version' => $wp_db_version,
        ];

        return $system;
    }
}
