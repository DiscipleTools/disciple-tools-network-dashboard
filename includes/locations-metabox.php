<?php


class DT_Structured_Mapping_Metabox {
    public function __construct()
    {
        add_action( 'admin_menu', [ $this, 'meta_box_setup' ], 20 );
        add_filter( "dt_custom_fields_settings", [$this, 'saturation_field_filter'], 10, 2 );
    }

    function meta_box_setup () {
        add_meta_box( 'location_details_notes', __( 'Saturation Mapping', 'disciple_tools' ), [$this, 'dt_mapping_load_mapping_meta_box'], 'locations', 'advanced', 'high' );
    }

    function dt_mapping_load_mapping_meta_box() {
        Disciple_Tools_Location_Post_Type::instance()->meta_box_content( 'saturation_mapping' );


        global $post_id;
        $post_parent_id = wp_get_post_parent_id( $post_id );
        $post_parent = get_post( $post_parent_id );

        echo '<hr>';
        echo "<h3>Parent Location</h3>";
        echo '<a href="' . admin_url() .'post.php?post=' . $post_parent->ID . '&action=edit">' . $post_parent->post_title . '</a>: ';

        if ( $par_loc = get_post_meta( $post_parent->ID, 'population', true ) ) {
            echo number_format( $par_loc, 0, ".", ",") . ' people live here ';

            $groups = $par_loc / 5000;
            echo ' | ' . number_format( $groups, 0, ".", ",") . ' groups needed' ;
        }
        echo '<hr>';
        echo "<h3>Child Locations</h3>";
        $child_population = $this->get_child_populations();
        if (! empty( $child_population ) ) {
            foreach ( $child_population as $location ) {
                echo '<a href="'.admin_url() .'post.php?post='.$location->ID.'&action=edit">' . $location->post_title . '</a>: ';

                if ( $loc_population = get_post_meta( $location->ID, 'population', true ) ) {
                    echo number_format( $loc_population, 0, ".", ",") . ' people live here ';

                    $groups = $loc_population / 5000;
                    echo ' | ' . number_format( $groups, 0, ".", ",") . ' groups needed | ' ;
                }

                echo $this->get_child_groups() . 'groups in area';

                echo '<br><br>';
            }
        }
    }

    public function get_child_groups() {
        // get the groups and child groups of the location
        
    }

    public function saturation_field_filter( $fields, $post_type ) {
        if( 'locations' === $post_type ) {
            $fields['population'] = [
                'name'        => 'Population ',
                'description' => '',
                'type'        => 'number',
                'default'     => '',
                'section'     => 'saturation_mapping',
            ];
            $fields['geonameid'] = [
                'name'        => 'GeoNames ID ',
                'description' => '',
                'type'        => 'text',
                'default'     => '',
                'section'     => 'saturation_mapping',
            ];
            $fields['geojson'] = [
                'name'        => 'GeoJSON Polygon',
                'description' => '',
                'type'        => 'text',
                'default'     => '',
                'section'     => 'saturation_mapping',
            ];
        }
        return $fields;
    }

    public function get_child_populations() {
        global $post_id;

        if( empty( $post_id ) ) {
            return 0;
        }

        // Set up the objects needed
        $my_wp_query = new WP_Query();
        $all_wp_pages = $my_wp_query->query(array('post_type' => 'locations', 'posts_per_page' => '-1'));

        // Filter through all pages and find Portfolio's children
        $portfolio_children = get_page_children( $post_id, $all_wp_pages );

        // echo what we get back from WP to the browser
        return $portfolio_children;

    }


}
new DT_Structured_Mapping_Metabox;
