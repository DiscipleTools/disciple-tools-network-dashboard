<?php

class DT_Network_Dashboard_Cron
{

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {

//        add_filter( 'cron_schedules', [ $this, 'add_cron_intervals' ] );


    } // End __construct()

    public static function get_crons( $reset = false ){
        $crons = get_option( 'dt_network_dashboard_crons' );
        if ( empty( $crons ) || $reset ) {
            self::update_crons( $crons );
            $crons = get_option( 'dt_network_dashboard_crons' );
        }

        $crons = apply_filters( 'dt_network_dashboard_custom_crons', $crons );

        return $crons;
    }

    public static function update_crons( $crons ){

        if ( empty( $crons ) ) {
            $crons = [
                'dt_network_dashboard_collect_activity' => [
                    'recurrence' => 'daily',
                    'display' => 'Collect Activity',
                    'description' => 'Collects activity from connected sites',
                ],
                'dt_network_dashboard_collect_multisite'=> [
                    'recurrence' => 'daily',
                    'display' => 'Collect Multisite Sites',
                    'description' => 'Collects current server multisite snapshot of statistics',
                ],
                'dt_network_dashboard_collect_remote' => [
                    'recurrence' => 'daily',
                    'display' => 'Collect Remote Sites',
                    'description' => 'Collects remote snapshot of statistics',
                ]
            ];
        }

        return update_option( 'dt_network_dashboard_crons', $crons, false );
    }

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

    public function get_frequency_settings(){
        $frequency = get_option( 'dt_network_dashboard_cron_frequency' );
        if ( ! empty( $frequency ) ) {
            $recurrence = $frequency;
        } else {
            $recurrence = 'daily';
        }

        $schedules = wp_get_schedules();
        if ( isset( $schedules[$frequency]['interval'] ) ) {
            $time = time() + $schedules[$frequency]['interval'];
        } else {
            $time = strtotime( 'tomorrow 2am' );
        }

        return [
            'time' => $time,
            'recurrence' => $recurrence
        ];
    }

    public static function admin_metabox_cron_settings() {
        $crons = self::get_crons();

        if ( isset( $_POST['cron_settings_nonce'] )
            && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['cron_settings_nonce'] ) ), 'cron_settings_' . get_current_user_id() )
            ) {

            dt_write_log($_POST );

            if ( isset( $_POST['frequency'] ) && is_array( $_POST['frequency'] ) ) {
                foreach( $_POST['frequency'] as $key => $value ){
                    if ( isset( $crons[$key]['recurrence'] ) ) {
                        $crons[$key]['recurrence'] = $value;
                    }
                }
                self::update_crons($crons);
                $crons = self::get_crons();
            }

            if ( isset( $_POST['reset'] ) ) {
                delete_option( 'dt_network_dashboard_crons' );
                $crons = self::get_crons();
            }

        }
        ?>
        <!-- Box -->
        <form method="post">
            <?php wp_nonce_field( 'cron_settings_' . get_current_user_id(), 'cron_settings_nonce' ) ?>
            <table class="widefat striped">
                <thead>
                <tr>
                    <th>Collect</th>
                    <th></th>
                    <th></th>
                </tr>

                </thead>
                <tbody>
                <?php
                foreach( $crons as $hook => $settings ) {
                    ?>
                    <tr>
                        <td>
                            <?php echo $settings['display'] ?>
                        </td>
                        <td>
                            <?php echo $hook ?>
                        </td>
                        <td>
                            <?php echo $settings['description'] ?? '' ?>
                        </td>
                        <td>
                            <select name="frequency[<?php echo $hook ?>]">
                                <option value=""></option>
                                <?php
                                $schedules = wp_get_schedules();
                                foreach( $schedules as $key => $schedule ){
                                    echo '<option value="'.esc_attr( $key ).'" ';
                                    if ( $settings['recurrence'] === $key ) {
                                        echo ' selected';
                                    };
                                    echo '>' . esc_html( $schedule['display'] ) .'</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <td>
                        <button type="submit" class="button">Update</button>
                        <button type="submit" class="button" name="reset" value="reset">Reset</button>
                    </td>
                    <td></td>
                </tr>

                </tbody>
            </table>
        </form>
        <br>
        <!-- End Box -->
        <?php
    }

    public static function admin_metabox_cron_list() {
        $crons = self::get_crons();

        if ( isset( $_POST['cron_run_nonce'] )
            && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['cron_run_nonce'] ) ), 'cron_run_' . get_current_user_id() )
            && isset( $_POST['run_now'] ) ) {

            dt_write_log($_POST);

            $hook = sanitize_text_field( wp_unslash( $_POST['run_now'] ) );
            $timestamp = wp_next_scheduled( $hook );
            wp_unschedule_event( $timestamp, $hook );

            // @todo push a run

        }
        $cron_list = _get_cron_array();
        ?>
        <!-- Box -->

        <table class="widefat striped">
            <thead>
            <tr>
                <th>External Cron Setup</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach( $cron_list as $time => $time_array ){
                foreach( $time_array as $token => $token_array ){
                    if ( 'dt_' === substr( $token, 0, 3 ) ){
                        foreach( $token_array as $key => $items ) {
                            ?>
                            <tr>
                                <td>
                                    <?php echo 'Next event in ' . round( ( $time - time() ) / 60 / 60 , 1) . ' hours' ?><br>
                                    <?php echo date( 'Y-m-d H:i:s', $time  )?><br>
                                    <?php echo $time ?>
                                </td>
                                <td>
                                    <?php echo $token ?>
                                </td>
                                <td>
                                    <?php echo $key ?>
                                </td>
                                <td>
                                    <?php echo $items['schedule'] ?? '' ?><br>
                                    <?php echo isset($items['interval']) ? $items['interval'] / 60 . ' minutes' : '' ?><br>
                                    <?php echo ! empty($items['args']) ? serialize( $items['args'] ) : '' ?><br>
                                </td>
                                <td>
                                    <form method="post">
                                        <?php wp_nonce_field( 'cron_run_' . get_current_user_id(), 'cron_run_nonce' ) ?>
                                        <button type="submit" name="run_now" value="<?php echo $token ?>" class="button">Run Now</button>
                                    </form>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                }
            }
            ?>
            </tbody>
        </table>

        <br>
        <!-- End Box -->
        <?php
    }
}
DT_Network_Dashboard_Cron::instance();