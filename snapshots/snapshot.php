<?php

class DT_Network_Dashboard_Snapshot
{

    public static function save_remote_snapshot( $snapshot, $site_post_id ){
        if ( ! isset( $snapshot['partner_id'] ) ) {
            return new WP_Error(__METHOD__, 'No partner id' );
        }

        // store to local multisite post id
        $partner_post_id = get_post_meta( $site_post_id, 'dt_network_dashboard', true );
        if ( empty( $partner_post_id ) ) {
            $partner_post_id = DT_Network_Dashboard_Site_Post_Type::get_post_id( $snapshot['partner_id'] );
            if ( is_wp_error( $partner_post_id ) ) {
                if ( isset( $snapshot['profile'] ) ){
                    $partner_post_id = DT_Network_Dashboard_Site_Post_Type::create( $snapshot['profile'], 'remote', $site_post_id );
                    if ( is_wp_error( $partner_post_id ) ) {
                        dt_write_log( __METHOD__, 'FAIL ID: ' . $site_post_id . ' (Unable to create '.$site_post_id.')' );
                        dt_write_log( __METHOD__, maybe_serialize( $partner_post_id ) );
                        return $partner_post_id;
                    }
                }
            }
        }

        update_post_meta( $partner_post_id, 'snapshot', $snapshot );
        update_post_meta( $partner_post_id, 'snapshot_timestamp', $snapshot['timestamp'] );

//        $site_post_id = DT_Network_Dashboard_Queries::get_site_id_from_partner_id( $snapshot['partner_id'] );
//        if ( empty( $site_post_id ) ){
//            return new WP_Error(__METHOD__, 'No matching site link to this partner id' );
//        }
//
//        if ( isset( $snapshot['timestamp'] ) ) {
//            $timestamp = $snapshot['timestamp'];
//        } else {
//            $timestamp = current_time( 'timestamp' );
//        }
//
//        if ( isset( $snapshot['profile']['partner_name'] )
//            && ! empty( $snapshot['profile']['partner_name'] )
//            && ( get_post_meta( $site_post_id, 'partner_name', true ) !== $snapshot['profile']['partner_name'] )  ) {
//            $name = sanitize_text_field( wp_unslash( $snapshot['profile']['partner_name'] ) );
//            update_post_meta( $site_post_id, 'partner_name', $name );
//        }
//        if ( isset( $snapshot['profile']['partner_description'] )
//            && ! empty( $snapshot['profile']['partner_description'] )
//            && ( get_post_meta( $site_post_id, 'partner_description', true ) !== $snapshot['profile']['partner_description'] ) ) {
//            $desc = sanitize_text_field( wp_unslash( $snapshot['profile']['partner_description'] ) );
//            update_post_meta( $site_post_id, 'partner_description', $desc );
//        }
//        if ( isset( $snapshot['profile']['partner_url'] )
//            && empty( $snapshot['profile']['partner_url'] )
//            && ( get_post_meta( $site_post_id, 'partner_url', true ) !== $snapshot['profile']['partner_url'] ) ) {
//            update_post_meta( $site_post_id, 'partner_url', $snapshot['profile']['partner_url'] );
//        }
//
//        update_post_meta( $site_post_id, 'snapshot', $snapshot );
//        update_post_meta( $site_post_id, 'snapshot_date', $timestamp );
//        update_post_meta( $site_post_id, 'snapshot_fail', false );

        return true;
    }
}