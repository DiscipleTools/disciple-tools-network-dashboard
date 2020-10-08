<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.


class DT_Network_Dashboard_Metrics_Statistics extends DT_Network_Dashboard_Metrics_Base
{
    public function __construct() {
        parent::__construct();

        $this->base_slug = 'statistics';
        $this->slug = '';
        $this->base_title = __( 'Statistics', 'disciple_tools' );
        $this->title = __( 'Statistics', 'disciple_tools' );
        $this->menu_title = 'Statistics';
        $this->url = $this->root_slug . '/' . $this->base_slug;
        $this->key = $this->root_slug . '_' . $this->base_slug;

        add_filter( 'dt_network_dashboard_build_menu', [ $this, 'menu' ], 30 );
    }

    public function menu( $tree ){
        // top levels load at 10, sub levels need to load at 50+
        $tree[$this->base_slug] = [
            'key' => $this->key,
            'label' => $this->menu_title,
            'url' => '/'.$this->url,
            'children' => []
        ];
        return $tree;
    }

}
new DT_Network_Dashboard_Metrics_Statistics();
