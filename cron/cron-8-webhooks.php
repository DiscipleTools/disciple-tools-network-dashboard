<?php
/**
 * Scheduled Cron Service
 */

$hook_name = 'dt_network_dashboard_webhook_schedule_daily';
$callback = 'dt_network_dashboard_push_webhooks';
if ( ! has_action( $hook_name ) ) {
    add_action( $hook_name, $callback );
}

function dt_network_dashboard_push_webhooks() {

    $webhooks = get_option( 'dt_network_dashboard_webhook_config' );
    if ( empty( $webhooks ) ) {
        return false;
    }

    $snapshot = get_option( 'dt_snapshot_report' );
    if ( empty( $snapshot ) ) {
        $snapshot = DT_Network_Dashboard_Snapshot::snapshot_report();
    }
    if ( is_wp_error( $snapshot ) ){
        dt_write_log( __METHOD__, 'Failed to send report because snapshot collection failed' );
        return false;
    }

    foreach ( $webhooks as $webhook ) {
        try {
            $enabled = $webhook['enabled'];
            if ( ! $enabled ) {
                continue;
            }
            $url = $webhook['url'];
            $token = $webhook['token'];
            $headers = array(
                'Content-Type' => 'application/json; charset=utf-8',
                'User-Agent' => 'custom/disciple-tools-network-dashboard-plugin/1.0'
            );
            if ( $token ) {
                $headers['Authorization'] = 'Bearer ' . $token;
            }
            $args = array(
                'method' => 'POST',
            'headers' => $headers,
                'body' => json_encode(
                    array(
                        'snapshot' => $snapshot
                    )
                )
            );
            $result = wp_remote_post( $url, $args );
            if ( is_wp_error( $result ) ) {
                dt_write_log( __METHOD__, maybe_serialize( $result ) );
                continue;
            }
            dt_write_log( __METHOD__, 'SUCCESS: ' . $maybe_serialize( $result ) );
        } catch ( Exception $e ) {
            dt_write_log( $e );
        }
    }
    return true;
}
