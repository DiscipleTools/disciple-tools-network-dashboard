<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.


class DT_Network_Dashboard_Metrics_Statistics extends DT_Network_Dashboard_Metrics_Base
{
    public function __construct() {
        parent::__construct();

        $this->base_slug = 'statistics';
        $this->slug = '';
        $this->base_title = __( 'Statistics', 'disciple-tools-network-dashboard' );
        $this->title = __( 'Statistics', 'disciple-tools-network-dashboard' );
        $this->menu_title = __( 'Statistics', 'disciple-tools-network-dashboard' );
        $this->url = $this->root_slug . '/' . $this->base_slug;
        $this->key = $this->root_slug . '_' . $this->base_slug;

        add_filter( 'dt_network_dashboard_build_menu', array( $this, 'menu' ), 30 );
    }

    public function menu( $tree ){
        // top levels load at 10, sub levels need to load at 50+
        $tree[$this->base_slug] = array(
            'key' => $this->key,
            'label' => __( 'Statistics', 'disciple-tools-network-dashboard' ),
            'url' => '/'.$this->url,
            'children' => array()
        );
        return $tree;
    }
}
new DT_Network_Dashboard_Metrics_Statistics();
