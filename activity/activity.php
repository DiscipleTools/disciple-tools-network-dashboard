<?php

class DT_Network_Activity_Log {
    public $token = 'movement_log';
    public $title = 'Movement Log';
    public $permissions = 'manage_option';
    public static $languages;

    /**  Singleton */
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    /**
     * Constructor function.
     * @access  public
     * @since   0.1.0
     */
    public function __construct()
    {
        // set up db

        self::$languages = json_decode(file_get_contents(plugin_dir_path(__FILE__) . '/languages.json'), true);

        // load required files
        add_action('rest_api_init', [$this, 'add_api_routes']);

        // load js function into header
        add_action('wp_head', [$this, 'movement_logging_script']);
    }

    public function add_api_routes() {
        $namespace = 'movement_log/v1';

        register_rest_route(
            // local site post
            $namespace, '/log', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'rest_log' ],
                ],
            ]
        );

        // @todo remote post from partner site

    }

    /**
    <!-- zume vision logging -->
    if (typeof window.movement_logging !== "undefined") {
    window.movement_logging({'action': 'requested_coach'} )
    }
    <!-- end zume vision logging -->


    <!-- zume vision logging -->
    <script>
    jQuery(document).ready(function(){
    let has_scrolled = false
    jQuery(document).scroll(function() {
    if (jQuery(document).scrollTop() >= 200 && has_scrolled === false ) {
    window.movement_logging({'action': 'studied_<?php echo esc_attr( $tool_number ) ?>' })
    has_scrolled = true
    }
    });
    })
    </script>
    <!-- end zume vision logging -->
     */

    public function rest_log( WP_REST_Request $request ) {
        $params = $request->get_params();

        if ( ! ( isset( $params['action'] ) && ! empty( $params['action' ] ) )  ) {
            dt_write_log( new WP_Error(__METHOD__, 'Required parameter missing.' ) );
            return false;
        }
        /**
         * Expects:
         * $params['action'] (required)
         * $params['group_size'] (optional)
         */

        return Zume_Vision_Log::log( $params );
    }

    public function logging_script(){
        ?>
        <script>
            window.movement_logging = ( args ) => {
                jQuery.ajax({
                    type: "POST",
                    data: JSON.stringify(args),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    url: '<?php echo esc_url_raw( rest_url() ) ?>movement_log/v1/log',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce( 'wp_rest' ) ?>' );
                    },
                })
                    .done(function(response){
                        console.log(response)
                    })
            }
        </script>
        <?php
    }

    public static function post_to_slack( $data ) {
        $slack_endpoint = get_option( 'zume_prayer_slack' );
        $channel = get_option( 'zume_prayer_slack_channel' ); // $channel = $_POST[0]['channel'] ?? '';
        $username = ''; // generally supplied by slack hook, but can be overridden
        $icon_emoji = ''; // generally supplied by slack hook, but can be overridden

        // Prepare the data / payload to be posted to Slack
        $data = array(
            'payload'   => json_encode( array(
                    "channel"       =>  $channel,
                    "text"          =>  $data['note'],
                    "username"	    =>  $username,
                    "icon_emoji"    =>  $icon_emoji
                )
            )
        );
        // Post our data via the slack webhook endpoint using wp_remote_post
        $posting_to_slack = wp_remote_post( $slack_endpoint, array(
                'method' => 'POST',
                'timeout' => 30,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                'body' => $data,
                'cookies' => array()
            )
        );
    }

    public static function log( $params ) {

        if ( ! ( isset( $params['action'] ) && ! empty( $params['action' ] ) )  ) {
            dt_write_log( new WP_Error(__METHOD__, 'Required parameter missing.' ) );
            return false;
        }
        if ( ! isset( $params['language'] ) && ! empty( $params['action' ] ) ) {
            $params['language'] = 'en';
        }

        $data = [
            'initials' => '',
            'action' => '',
            'group_size' => '',
            'lng' => '',
            'lat' => '',
            'level' => '',
            'label' => '',
            'grid_id' => '',
            'country' => '',
            'language' => '',
            'note' => '',
            'timestamp' => '',
            'hash' => '',
        ];

        // set action
        $data['action'] = sanitize_text_field( wp_unslash( $params['action'] ) );
        $data['language'] = sanitize_text_field( wp_unslash( $params['language'] ) );

        // set group size
        if ( isset( $params['group_size'] ) && ! empty( $params['group_size'] ) ) {
            $data['group_size'] = sanitize_text_field( wp_unslash( $params['group_size'] ) );
        } else {
            $data['group_size'] = 1;
        }

        // get ip address
        $ipstack = new DT_Ipstack_API();
        $ip_address = $ipstack::get_real_ip_address();
        $response = $ipstack::geocode_ip_address($ip_address);

        // set lng and lat
        $data['lng'] = $response['longitude'];
        $data['lat'] = $response['latitude'];

        // set level
        // @note blank level means lowest level possible.
        $level = '';
        if ( ! empty( $response['city'] ) ) {
            $level = '';
        } else if ( ! empty( $response['region_name'] ) ) {
            $level = 'admin1';
        } else if ( ! empty( $response['country_name'] ) ) {
            $level = 'admin0';
        }
        $data['level'] = $level;

        // set label and country
        $country = $ipstack::parse_raw_result( $response, 'country_name' );
        $region = $ipstack::parse_raw_result( $response, 'region_name' );
        if ( $country || $region ) {
            $data['label'] = $region . ( ! empty( $region ) ? ", " : "" ) . $country;
        }
        $data['country'] = $country;

        // set initials
        $letters = [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'N', 'S',
            'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'V', 'W', 'E', 'A', 'R', 'I',
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'D', 'E',
            'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'V', 'W', 'E', 'A', 'R', 'I',
            'E', 'A', 'R', 'I', 'T', 'N', 'S', 'L', 'E', 'A', 'R', 'I', 'N', 'S',
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'C', 'D',
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'A', 'B',
            'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'V', 'W', 'E', 'A', 'R', 'I',
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'A', 'B',
            'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'E', 'A', 'R',
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'N', 'S',
            'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'V', 'W', 'E', 'A', 'R', 'I',
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'D', 'E',
            'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'V', 'W', 'E', 'A', 'R', 'I',
            'E', 'A', 'R', 'I', 'T', 'N', 'S', 'L', 'E', 'A', 'R', 'I', 'N', 'S',
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'C', 'D',
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'A', 'B',
            'E', 'A', 'R', 'I', 'T', 'N', 'S', 'L', 'E', 'A', 'R', 'I', 'N', 'S',
            'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'V', 'W', 'E', 'A', 'R', 'I',
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'N', 'S',
        ];
        $ip_explode = explode( '.', $ip_address );
        if ( isset( $ip_explode[3] ) && isset( $ip_explode[2] ) ) {
            $data['initials'] = $letters[$ip_explode[2]] . $letters[$ip_explode[3]]; // using the v4 ip address to select the initials
        } else {
            $hash = strtoupper( base64_encode ( hash('sha256',  $data['label'] . $response['ip'] ) ) );
            $hash = str_replace( '1', '', $hash );
            $hash = str_replace( '2', '', $hash );
            $hash = str_replace( '3', '', $hash );
            $hash = str_replace( '4', '', $hash );
            $hash = str_replace( '5', '', $hash );
            $hash = str_replace( '6', '', $hash );
            $hash = str_replace( '7', '', $hash );
            $hash = str_replace( '8', '', $hash );
            $hash = str_replace( '9', '', $hash );
            $hash = str_replace( '0', '', $hash );
            $hash = str_replace( 'Q', '', $hash );
            $hash = str_replace( 'Z', '', $hash );
            $hash = str_replace( 'X', '', $hash );
            $data['initials'] = substr( $hash, 0, 1 );
        }

        // set grid id
        $geocoder = new Location_Grid_Geocoder();
        $grid_response = $geocoder->get_grid_id_by_lnglat( $data['lng'], $data['lat'], $ipstack::parse_raw_result( $response, 'country_code' ), $level );
        if ( ! empty( $grid_response ) ) {
            $data['grid_id'] = $grid_response['grid_id'];
        }

        // create language label section
        $language_label = '';
        foreach( self::$languages as $language ) {
            if ( $language['code'] === $data['language'] && 'en' !== $data['language'] ) {
                $language_label = ' in ' . $language['enDisplayName'];
                break;
            }
        }

        // create location label
        $label = empty( $data['label'] ) ? '' :  ' (' . $data['label'] . ')';

        // set note
        $number = explode( '_', $data['action'] );
        $number = $number[1] ?? 0;
        if ( $data['action'] === 'leading_' ) {
            $data['action'] = 'leading_1';
        }
        switch ( $data['action'] ) {

            /* greatest_blessing */
            case 'requested_coach':
                $data['note'] = $data['initials'] . ' is requesting coaching from Zúme coaches!'.$label;
                $data['category'] = 'greatest_blessing';
                break;
            case 'joined_community':
                $data['note'] = $data['initials'] . ' is joining the Zúme Community!'.$label;
                $data['category'] = 'greatest_blessing';
                break;


            /* greater_blessing */
            case 'leading_1':
            case 'leading_2':
            case 'leading_3':
            case 'leading_4':
            case 'leading_5':
            case 'leading_6':
            case 'leading_7':
            case 'leading_8':
            case 'leading_9':
            case 'leading_10':
                $label = empty( $data['label'] ) ? '' :  ' (' . $data['label'] . ')';
                if ( $data['group_size'] > 1 ) {
                    $data['note'] = $data['initials'] . ' is leading a group of '. esc_attr( $data['group_size'] ) .' through session '. esc_attr( $number ) .$language_label.'!'.$label;
                } else {
                    $data['note'] = $data['initials'] . ' is going through session '. esc_attr( $number ) .''.$language_label.'!'.$label;
                }
                $data['category'] = 'greater_blessing';
                break;



            /* great_blessing */
            case 'registered':
                $data['note'] = $data['initials'] . ' is registering for Zúme Training'.$language_label.'!'.$label;
                $data['category'] = 'great_blessing';
                break;
            case 'started_group':
                $data['note'] = $data['initials'] . ' is creating a new training group!'.$label;
                $data['category'] = 'great_blessing';
                break;
            case 'joined_group':
                $data['note'] = $data['initials'] . ' is joining a training group!'.$label;
                $data['category'] = 'great_blessing';
                break;
            case 'updated_3_month':
                $data['note'] = $data['initials'] . ' made a three month plan!'.$label;
                $data['category'] = 'great_blessing';
                break;



            /* blessing */
            case 'studied_1':
            case 'studied_2':
            case 'studied_3':
            case 'studied_4':
            case 'studied_5':
            case 'studied_6':
            case 'studied_7':
            case 'studied_8':
            case 'studied_9':
            case 'studied_10':
            case 'studied_11':
            case 'studied_12':
            case 'studied_13':
            case 'studied_14':
            case 'studied_15':
            case 'studied_16':
            case 'studied_17':
            case 'studied_18':
            case 'studied_19':
            case 'studied_20':
            case 'studied_21':
            case 'studied_22':
            case 'studied_23':
            case 'studied_24':
            case 'studied_25':
            case 'studied_26':
            case 'studied_27':
            case 'studied_28':
            case 'studied_29':
            case 'studied_30':
            case 'studied_31':
            case 'studied_32':
                $data['note'] = $data['initials'] . ' is studying "'. get_the_title( zume_landing_page_post_id( $number ) ).'"'.$language_label.'.'.$label;
                $data['category'] = 'blessing';
                break;
            default:
                return false;
                break;
        }

        $category = apply_filters('movement_log_category', $data['action'] );
        $note = apply_filters('movement_log_note', $data['note'], $data );

        // set hash
        $data['hash'] = hash('sha256', serialize( $data ) );

        // set timestamp
        $data['timestamp'] = time();

        // test if duplicate
        global $wpdb;
        $time = new DateTime();
        $time->modify('-30 minutes');
        $past_stamp = $time->format('U');
        $results = $wpdb->get_col( $wpdb->prepare( "SELECT hash FROM $wpdb->movement_log WHERE timestamp > %d",$past_stamp ) );
        if ( array_search( $data['hash'], $results ) !== false ) {
            return false;
        }

        // insert log record
        $wpdb->query( $wpdb->prepare( "
                INSERT INTO $wpdb->movement_log (
                    initials,
                    action,
                    category,
                    group_size,
                    lng,
                    lat,
                    level,
                    label,
                    grid_id,
                    country,
                    language,
                    note,
                    timestamp,
                    hash
                )
                VALUES (
                        %s,
                        %s,
                        %s,
                        %s,
                        %f,
                        %f,
                        %s,
                        %s,
                        %s,
                        %s,
                        %s,
                        %s,
                        %s,
                        %s
                        )",
            $data['initials'],
            $data['action'],
            $data['category'],
            $data['group_size'],
            $data['lng'],
            $data['lat'],
            $data['level'],
            $data['label'],
            $data['grid_id'],
            $data['country'],
            $data['language'],
            $data['note'],
            $data['timestamp'],
            $data['hash']
        ) );

        // post to slack
        self::post_to_slack( $data );

        return true;
    }
}