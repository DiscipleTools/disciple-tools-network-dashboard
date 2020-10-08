<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.


class DT_Network_Dashboard_Metrics_Maps extends DT_Network_Dashboard_Metrics_Base
{
    public function __construct() {
        parent::__construct();

        $this->base_slug = 'maps';
        $this->slug = '';
        $this->base_title = __( 'Maps', 'disciple_tools' );
        $this->title = __( 'Maps', 'disciple_tools' );
        $this->menu_title = 'Maps';
        $this->url = $this->root_slug . '/' . $this->base_slug;
        $this->key = $this->root_slug . '_' . $this->base_slug . '_' . $this->slug;

        add_filter( 'dt_network_dashboard_build_menu', [ $this, 'menu' ], 40 );

    }

    public function menu( $tree ){
        $tree[$this->base_slug] = [
            'key' => $this->key,
            'label' => $this->menu_title,
            'url' => '/'.$this->url,
            'children' => []
        ];
        return $tree;
    }
}
new DT_Network_Dashboard_Metrics_Maps();
