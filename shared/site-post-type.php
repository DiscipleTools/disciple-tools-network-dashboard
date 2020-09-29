<?php

class DT_Network_Dashboard_Site_Post_Type {
    public $token;

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()


    public function __construct() {
        $this->token = self::get_token();
        add_action( 'init', [ $this, 'register_network_dashboard_post_type' ] );
    } // End __construct()

    public static function get_token(){
        return 'dt_network_dashboard';
    }

    public function register_network_dashboard_post_type() {
        $args = array(
            'public'    => false
        );
        register_post_type( $this->token, $args );
    }

    /**
     * @param $id (blog_id for the multisite)
     * @return int|string|WP_Error
     */
    public static function create_multisite_by_id( $id ){
        switch_to_blog( $id );
        $profile = dt_network_site_profile();
        restore_current_blog();

        return self::create( $profile, 'multisite', $id );
    }

    /**
     * @param $id  (site to site post id)
     * @return array|bool|int|string|WP_Error
     */
    public static function create_remote_by_id( $id ){
        $site = Site_Link_System::get_site_connection_vars( $id, 'post_id');
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
        if ( ! isset( $result['body'] ) || empty( $result['body'] ) ) {
            return new WP_Error(__METHOD__, 'Remote API did not return properly configured body response.');
        }

        /* site profile returned */
        $site_profile = json_decode( $result['body'], true );
        if ( ! isset( $site_profile['partner_id'] ) || empty( $site_profile['partner_id'] ) ){
            return new WP_Error(__METHOD__, 'Remote API did not return a proper partner id.');
        }

        recursive_sanitize_text_field( $site_profile );

        $dt_network_dashboard_id = get_post_meta( $id, 'dt_network_dashboard', true );
        if ( empty( $dt_network_dashboard_id ) ) {
            return self::create( $site_profile, 'multisite', $id );
        } else {
            return update_post_meta( $dt_network_dashboard_id, 'profile', $site_profile );
        }
    }

    public static function create( $site_profile, $connection_type, $id ) {
        global $wpdb;

        $partner_id = $site_profile['partner_id'] ?? 0;

        // duplicate check
        $multisite_post_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT ID 
                    FROM $wpdb->posts 
                    WHERE post_type = %s 
                      AND post_title = %s"
            , self::get_token(), $partner_id ) );
        if ( ! empty( $multisite_post_id  ) ) {
            return $multisite_post_id;
        }

        if ( ! ( 'multisite' === $connection_type || 'remote' === $connection_type ) ) {
            return new WP_Error( __METHOD__, 'Type must be either multisite or remote' );
        }

        if ( ! is_numeric( $id ) ){
            return new WP_Error( __METHOD__, 'Id must be a number' );
        }

        $multisite_post_id = wp_insert_post([
            'post_title' => $partner_id,
            'guid' => $partner_id,
            'post_content' => 'Network Dashboard site',
            'post_type' => self::get_token(),
            'post_status' => 'show',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'meta_input' => [
                'partner_id' => $partner_id,
                'name' => $site_profile['partner_name'] ?? get_bloginfo('name'),
                'visibility' => 'show',
                'send_live_activity' => 'yes',
                'profile' => $site_profile,
                'type' => $connection_type,
                'type_id' => $id
            ]
        ]);

        if ( 'remote' === $connection_type ) {
            update_post_meta( $id, 'dt_network_dashboard', $multisite_post_id );
        }

        do_action( 'dt_network_dashboard_create_record', $multisite_post_id );

        return $multisite_post_id;
    }

    public static function get_post_id( $partner_id ) {
        global $wpdb;
        $partner_post_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT ID 
                    FROM $wpdb->posts 
                    WHERE post_type = %s 
                      AND post_title = %s", self::get_token(), $partner_id ) );
        if ( empty( $partner_post_id ) ) {
            return new WP_Error( __METHOD__, 'No partner found with this id.' );
        }
        return $partner_post_id;
    }

    public static function get_snapshot( $partner_post_id ){
        return get_post_meta( $partner_post_id, 'snapshot', true );
    }

    public static function update_snapshot( array $snapshot, $partner_post_id ) {
        if ( ! isset( $snapshot['profile']['partner_id'] ) ){
            return new WP_Error( __METHOD__, 'Could not find snapshot partner_id. Malformed snapshot array.' );
        }
        if ( ! isset( $snapshot['timestamp'] ) ){
            return new WP_Error( __METHOD__, 'Could not find timestamp. Malformed snapshot array.' );
        }

        update_post_meta( $partner_post_id, 'snapshot_timestamp', $snapshot['timestamp'] ?? time() );
        return update_post_meta( $partner_post_id, 'snapshot', $snapshot );
    }

    public static function delete_snapshot( $partner_post_id ){
        delete_post_meta( $partner_post_id, 'snapshot_timestamp' );
        return delete_post_meta( $partner_post_id, 'snapshot' );
    }

    public static function get_profile( $partner_post_id ){
        $profile = get_post_meta( $partner_post_id, 'profile', true );
        if ( empty( $profile ) ){
            $snapshot = self::get_snapshot($partner_post_id);
            if ( isset( $snapshot['profile'] ) ) {
                self::update_profile( $partner_post_id, $profile );
                $profile = $snapshot['profile'];
            } else {
                return new WP_Error(__METHOD__, 'No profile found' );
            }
        }
        return $profile;
    }

    public static function update_profile( $partner_post_id, array $profile  ){
        return update_post_meta( $partner_post_id, 'profile', $profile );
    }

    public static function delete_profile( $partner_post_id ){
        return delete_post_meta( $partner_post_id, 'profile' );
    }

    public static function get_site_name( $partner_post_id ){
        $name = get_post_meta( $partner_post_id, 'name', true );
        if ( empty( $name ) ) {
            $snapshot = self::get_snapshot($partner_post_id);
            if ( isset( $snapshot['profile']['partner_name'] ) ) {
                self::update_site_name( $partner_post_id, $snapshot['profile']['partner_name'] );
                $name = $snapshot['profile']['partner_name'];
            } else {
                $name = get_option( 'blogname' );
                self::update_site_name( $partner_post_id, $name );
            }
        }
        return $name;
    }

    public static function update_site_name( $partner_post_id, string $name ){
        return update_post_meta( $partner_post_id, 'name', $name );
    }

    public static function update_send_live_activity( $partner_post_id, $send_live_activity ){
        if ( $send_live_activity === 'no' ){
            $value = 'no';
        } else {
            $value = 'yes';
        }
        return update_post_meta( $partner_post_id, 'send_live_activity', $value );
    }

    public static function update_visibility( $partner_post_id, $status ){
        if ( $status === 'hide' ){
            $value = 'hide';
        } else {
            $value = 'show';
        }
        return update_post_meta( $partner_post_id, 'visibility', $value );
    }

    public static function get_type( $partner_post_id ){
        return [
            'type' => get_post_meta( $partner_post_id, 'type', true ),
            'id' => get_post_meta( $partner_post_id, 'type_id', true ),
        ];
    }

    public static function update_type( $partner_post_id, $type, $id ){
        if ( 'multisite' === $type ){
            update_post_meta( $partner_post_id, 'type', 'multisite' );
            return update_post_meta( $partner_post_id, 'type_id', $id );
        } else if ( 'remote' === $type ) {
            update_post_meta( $partner_post_id, 'type', 'remote' );
            return update_post_meta( $partner_post_id, 'type_id', $id );
        } else {
            return false;
        }
    }

    public static function delete( $partner_post_id ) {
        return wp_delete_post( $partner_post_id );
    }

    public static function all_sites() : array {
        global $wpdb;

        $results = $wpdb->get_results("
                 SELECT 
                  a.ID as id,
                 CASE
                    WHEN e.meta_value IS NOT NULL THEN e.meta_value
                    ELSE a.post_title
                 END as name,
                  b.meta_value as type,
                  f.meta_value as type_id,    
                  d.meta_value as partner_id,
                  c.meta_value as snapshot,
                  g.meta_value as snapshot_timestamp,
                  h.meta_value as profile,
                  i.meta_value as send_live_activity,
                  j.meta_value as visibility
                FROM $wpdb->posts as a
                LEFT JOIN $wpdb->postmeta as c
                  ON a.ID=c.post_id
                  AND c.meta_key = 'snapshot'
                JOIN $wpdb->postmeta as d
                  ON a.ID=d.post_id
                  AND d.meta_key = 'partner_id'
                LEFT JOIN $wpdb->postmeta as e
                  ON a.ID=e.post_id
                  AND e.meta_key = 'name'
                LEFT JOIN $wpdb->postmeta as b
                  ON a.ID=b.post_id
                  AND b.meta_key = 'type'
                LEFT JOIN $wpdb->postmeta as f
                  ON a.ID=f.post_id
                  AND f.meta_key = 'type_id'
                LEFT JOIN $wpdb->postmeta as g
                  ON a.ID=g.post_id
                  AND g.meta_key = 'snapshot_timestamp'
                 LEFT JOIN $wpdb->postmeta as h
                  ON a.ID=h.post_id
                  AND h.meta_key = 'profile'
                 LEFT JOIN $wpdb->postmeta as i
                  ON a.ID=i.post_id
                  AND i.meta_key = 'send_live_activity'
                 LEFT JOIN $wpdb->postmeta as j
                  ON a.ID=j.post_id
                  AND j.meta_key = 'visibility'
                WHERE a.post_type = 'dt_network_dashboard'
                ORDER BY name;
            ",
            ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        $sites = [];
        foreach( $results as $result ){
            $result['snapshot'] = maybe_unserialize( $result['snapshot'] );
            $result['profile'] = maybe_unserialize( $result['profile'] );
            $sites[$result['partner_id']] = $result;
        }

        return $sites;
    }

    public static function all_connections() : array {
        global $wpdb;

        $results = $wpdb->get_results("
                 SELECT 
                  a.ID as id,
                  b.meta_value as type,
                  c.meta_value as type_id   
                FROM $wpdb->posts as a
                LEFT JOIN $wpdb->postmeta as b
                  ON a.ID=b.post_id
                  AND b.meta_key = 'type'
                LEFT JOIN $wpdb->postmeta as c
                  ON a.ID=c.post_id
                  AND c.meta_key = 'type_id'
                WHERE a.post_type = 'dt_network_dashboard';
            ",
            ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function all_multisite_ids() : array {
        global $wpdb;
        $table = $wpdb->base_prefix . 'blogs';
        $results = $wpdb->get_col( "SELECT blog_id FROM $table" );

        if ( empty( $results ) ) {
            $results = [];
        }

        $dt_sites = [];
        foreach ( $results as $id ) {
            if ( get_blog_option( $id, 'current_theme' ) !== 'Disciple Tools' ) {
                continue;
            }
            $dt_sites[$id] = $id;
        }

        return $dt_sites;
    }

    public static function all_remote_ids() : array {
        global $wpdb;

        $results = $wpdb->get_col("
                SELECT 
                  ID as id
                FROM $wpdb->posts as p
                JOIN $wpdb->postmeta as pm
                  ON p.ID=pm.post_id
					AND pm.meta_key = 'type'
				LEFT JOIN $wpdb->postmeta as pm2
                  ON p.ID=pm2.post_id
					AND pm2.meta_key = 'non_wp' 
                  WHERE p.post_type = 'site_link_system'
                  AND p.post_status = 'publish'
                  AND ( pm.meta_value = 'network_dashboard_both'
                  OR pm.meta_value = 'network_dashboard_receiving' )
                    AND pm2.meta_value != '1'
            " );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function sync_all_multisites_to_post_type() : array {
        $result = [
            'delete' => [],
            'create' => [],
        ];
        $multisites = self::all_multisite_ids();
        $connections = self::all_connections();

        // delete all multisites not permitted or removed
        foreach( $connections as $connection ){
            if ( 'multisite' !== $connection['type'] ){
                continue;
            }

            if ( in_array( $connection['type_id'], $multisites ) ) {
                continue;
            }

            $result['delete'][] = self::delete( $connection['id'] );
        }

        // add all multisites not previously added
        $type_ids = [];
        foreach( $connections as $connection ){
            if ( 'multisite' !== $connection['type'] ){
                continue;
            }
            $type_ids[] = $connection['type_id'];
        }
        foreach( $multisites as $multisite ){
            if ( in_array( $multisite, $type_ids ) ) {
                continue;
            }

            $result['create'][$multisite] = self::create_multisite_by_id( $multisite );
        }

        return $result;
    }

    public static function sync_all_remotes_to_post_type() : array {
        $result = [
            'delete' => [],
            'create' => [],
        ];
        $remotes = self::all_remote_ids();
        $connections = self::all_connections();

        // delete all remotes not permitted or removed
        foreach( $connections as $connection ){
            if ( 'remote' !== $connection['type'] ){
                continue;
            }

            if ( in_array( $connection['type_id'], $remotes ) ) {
                continue;
            }

            $result['delete'][] = self::delete( $connection['id'] );
        }

        // add all remotes not previously added
        $type_ids = [];
        foreach( $connections as $connection ){
            if ( 'remote' !== $connection['type'] ){
                continue;
            }
            $type_ids[] = $connection['type_id'];
        }
        foreach( $remotes as $remote ){
            if ( in_array( $remote, $type_ids ) ) {
                continue;
            }

            $result['create'][$remote] = self::create_remote_by_id( $remote );
        }

        return $result;
    }

    public static function multisite_sites_needing_snapshot_refreshed() {
        if ( ! dt_is_current_multisite_dashboard_approved() ) {
            return [];
        }

        $multisites = self::all_multisite_ids();
        $l = self::all_sites();

        $list = [];
        foreach( $l as $item ){
            if ( $item['type'] !== 'multisite' ){
                continue;
            }

            $list[$item['type_id']] = $item['snapshot_timestamp'];
        }

        $needs_update = [];
        foreach ( $multisites as $multisite ){
            if ( isset( $list[$multisite] ) ) {
                if ( $list[$multisite]  >= strtotime( 'today' ) ){
                    continue;
                } else {
                    $needs_update[] = $multisite;
                }
            } else {
                $network_dashboard_id = self::create_multisite_by_id( $multisite );
                if ( is_wp_error( $network_dashboard_id ) ){
                    dt_write_log($network_dashboard_id);
                    continue;
                }
                $needs_update[] = $multisite;
            }
        }

        return $needs_update;
    }

}
DT_Network_Dashboard_Site_Post_Type::instance();