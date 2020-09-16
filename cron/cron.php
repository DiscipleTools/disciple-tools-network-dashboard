<?php

class DT_Network_Dashboard_Cron
{
    public static $hooks = [ 'dt_network_dashboard_send_updates', 'dt_network_dashboard_collect_multisite', 'dt_network_dashboard_collect_remote' ];
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        add_filter( 'cron_schedules', 'add_cron_intervals' );

        foreach ( self::$hooks as $hook ){
            if ( ! wp_next_scheduled( $hook ) ) {

                wp_schedule_event( strtotime( 'tomorrow 2am' ), 'daily', $hook );
            }
            add_action( $hook, [ $this, $hook ] );
        }



    } // End __construct()

    public function add_cron_intervals( $schedules ) {
        $schedules['one_minute'] = array(
            'interval' => 60,
            'display'  => esc_html__( 'Every Minute' ), );
        $schedules['five_minutes'] = array(
            'interval' => 300,
            'display'  => esc_html__( 'Every 5 Minutes' ), );
        $schedules['ten_minutes'] = array(
            'interval' => 600,
            'display'  => esc_html__( 'Every 10 Minutes' ), );
        $schedules['twenty_minutes'] = array(
            'interval' => 1200,
            'display'  => esc_html__( 'Every 20 Minutes' ), );
        return $schedules;
    }
}
DT_Network_Dashboard_Cron::instance();