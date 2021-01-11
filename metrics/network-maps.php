<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.


class DT_Network_Dashboard_Metrics_Maps extends DT_Network_Dashboard_Metrics_Base
{
    public function __construct() {
        parent::__construct();

        $this->base_slug = 'maps';
        $this->slug = '';
        $this->base_title = __( 'Maps', 'disciple-tools-network-dashboard' );
        $this->title = __( 'Maps', 'disciple-tools-network-dashboard' );
        $this->menu_title = __( 'Maps', 'disciple-tools-network-dashboard' );
        $this->url = $this->root_slug . '/' . $this->base_slug;
        $this->key = $this->root_slug . '_' . $this->base_slug . '_' . $this->slug;

        add_filter( 'dt_network_dashboard_build_menu', array( $this, 'menu' ), 40 );

    }

    public function menu( $tree ){
        $tree[$this->base_slug] = array(
            'key' => $this->key,
            'label' => __( 'Maps', 'disciple-tools-network-dashboard' ),
            'url' => '/'.$this->url,
            'children' => array()
        );
        return $tree;
    }
}
new DT_Network_Dashboard_Metrics_Maps();
