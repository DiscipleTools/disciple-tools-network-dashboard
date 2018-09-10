<?php


class DT_Saturation_Mapping_Metabox {
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'meta_box_setup' ], 20 );
        add_filter( "dt_custom_fields_settings", [ $this, 'saturation_field_filter' ], 10, 2 );
        add_action( 'dt_locations_map_additions', [ $this, 'add_geojson' ], 20 );
    }

    public function meta_box_setup() {
        add_meta_box( 'location_details_notes', __( 'Saturation Mapping', 'disciple_tools' ), [ $this, 'load_mapping_meta_box' ], 'locations', 'advanced', 'high' );

    }

    public function load_mapping_meta_box() {

        Disciple_Tools_Location_Post_Type::instance()->meta_box_content( 'saturation_mapping' );

        echo '<p><a href="javascript:void(0);" onclick="jQuery(\'#saturation_mapping_hidden\').toggle();">show more</a></p>';
        echo '<div style="display:none;" id="saturation_mapping_hidden">';
        Disciple_Tools_Location_Post_Type::instance()->meta_box_content( 'saturation_mapping_hidden' );
        echo '</div>';

        echo '<br><button type="submit" class="button">Update</button>';




        global $post, $post_id;
        $post_parent_id = wp_get_post_parent_id( $post_id );

        /**
         * Parent Location
         */
        if ( $post_parent_id ) {
            $post_parent = get_post( $post_parent_id );
            $location_group_count = $this->get_child_groups();
            $population_division = get_option( 'dt_saturation_mapping_pd' );
            $post_parent_title = isset( $post_parent->post_title ) ? $post_parent->post_title : '';

            echo '<hr>';
            echo "<h3>". esc_attr__( 'Parent Location' ) . "</h3>";
            echo '<a href="' . admin_url() .'post.php?post=' . $post_parent_id . '&action=edit">' . $post_parent_title . '</a>: ';

            if ( $par_loc = get_post_meta( $post_parent_id, 'gn_population', true ) ) {
                echo number_format( $par_loc, 0, ".", "," ) . ' people live here ';

                $groups = $par_loc / $population_division;
                echo ' | ' . number_format( $groups, 0, ".", "," ) . ' groups needed';
            }
            echo '<br><br>';
        }


        /**
         * Current Location
         */
        echo '<hr>';
        echo "<h3>". esc_attr__( 'Current Location' ) . "</h3>";
        if ( $cur_population = get_post_meta( $post_id, 'gn_population', true ) ) {
            echo '<strong>' . $post->post_title . '</strong>: ';
            echo number_format( $cur_population, 0, ".", "," ) . ' people live here ';

            $groups = $cur_population / $population_division;
            echo ' | ' . number_format( $groups, 0, ".", "," ) . ' groups needed | ';

            $groups_in_area = 0;
            foreach ($location_group_count as $value ) {
                if ( $value['location'] == $post_id ) {
                    $groups_in_area = $value['count'];
                    break;
                }
            }
            echo $groups_in_area . ' groups in area';

        }
        echo '<br><br>';

        /**
         * Child Location
         */
        echo '<hr>';
        echo "<h3>". esc_attr__( 'Child Locations' ) . "</h3>";
        $child_population = $this->get_child_populations();
        if ( ! empty( $child_population ) ) {
            foreach ( $child_population as $location ) {
                echo '<a href="'.admin_url() .'post.php?post='.$location->ID.'&action=edit">' . $location->post_title . '</a>: ';

                if ( $loc_population = get_post_meta( $location->ID, 'gn_population', true ) ) {
                    echo number_format( $loc_population, 0, ".", "," ) . ' people live here ';

                    $groups = $loc_population / $population_division;
                    echo ' | ' . number_format( $groups, 0, ".", "," ) . ' groups needed | ';
                }

                $groups_in_area = 0;
                foreach ($location_group_count as $value ) {
                    if ( $value['location'] == $location->ID ) {
                        $groups_in_area = $value['count'];
                        break;
                    }
                }
                echo $groups_in_area . ' groups in area';

                echo '<br><br>';
            }
        }
    }

    /**
     * Returns array of locations and counts of groups
     * This does not distinguish between types of groups.
     * The array contains 'location' and 'count' fields.
     *
     * @return array|null|object
     */
    public function get_child_groups() {
        // get the groups and child groups of the location
        global $wpdb;
        return $wpdb->get_results( "SELECT p2p_to as location, count(p2p_id) as count FROM $wpdb->p2p WHERE p2p_type = 'groups_to_locations' GROUP BY p2p_to", ARRAY_A );
    }

    public function saturation_field_filter( $fields, $post_type ) {
        if ( 'locations' === $post_type ) {
            $fields['gn_geonameid'] = [
                'name'        => 'GeoNames ID ',
                'description' => '',
                'type'        => 'text',
                'default'     => '',
                'section'     => 'saturation_mapping',
            ];
            $fields['gn_name'] = [
                'name'        => 'Name ',
                'description' => '',
                'type'        => 'text',
                'default'     => '',
                'section'     => 'saturation_mapping_hidden',
            ];
            $fields['gn_asciiname'] = [
                'name'        => 'Ascii Name ',
                'description' => '',
                'type'        => 'text',
                'default'     => '',
                'section'     => 'saturation_mapping_hidden',
            ];
            $fields['gn_alternatenames'] = [
                'name'        => 'Alternate Names',
                'description' => '',
                'type'        => 'text',
                'default'     => '',
                'section'     => 'saturation_mapping_hidden',
            ];
            $fields['gn_latitude'] = [
                'name'        => 'Latitude',
                'description' => '',
                'type'        => 'text',
                'default'     => '',
                'section'     => 'saturation_mapping',
            ];
            $fields['gn_longitude'] = [
                'name'        => 'Longitude',
                'description' => '',
                'type'        => 'text',
                'default'     => '',
                'section'     => 'saturation_mapping',
            ];
            $fields['gn_feature_class'] = [
                'name'        => 'Feature Class',
                'description' => '',
                'type'        => 'text',
                'default'     => '',
                'section'     => 'saturation_mapping_hidden',
            ];
            $fields['gn_feature_code'] = [
                'name'        => 'Feature Code',
                'description' => '',
                'type'        => 'text',
                'default'     => '',
                'section'     => 'saturation_mapping_hidden',
            ];
            $fields['gn_country_code'] = [
                'name'        => 'Country Code',
                'description' => '',
                'type'        => 'text',
                'default'     => '',
                'section'     => 'saturation_mapping_hidden',
            ];
            $fields['gn_admin1_code'] = [
                'name'        => 'Admin1 Code',
                'description' => '',
                'type'        => 'text',
                'default'     => '',
                'section'     => 'saturation_mapping_hidden',
            ];
            $fields['gn_admin2_code'] = [
                'name'        => 'Admin2 Code',
                'description' => '',
                'type'        => 'text',
                'default'     => '',
                'section'     => 'saturation_mapping_hidden',
            ];
            $fields['gn_admin3_code'] = [
                'name'        => 'Admin3 Code',
                'description' => '',
                'type'        => 'text',
                'default'     => '',
                'section'     => 'saturation_mapping_hidden',
            ];
            $fields['gn_admin4_code'] = [
                'name'        => 'Admin4 Code',
                'description' => '',
                'type'        => 'text',
                'default'     => '',
                'section'     => 'saturation_mapping_hidden',
            ];
            $fields['gn_population'] = [
                'name'        => 'Population',
                'description' => '',
                'type'        => 'number',
                'default'     => '',
                'section'     => 'saturation_mapping',
            ];
            $fields['gn_elevation'] = [
                'name'        => 'Elevation ',
                'description' => '',
                'type'        => 'text',
                'default'     => '',
                'section'     => 'saturation_mapping_hidden',
            ];
            $fields['gn_dem'] = [
                'name'        => 'DEM ',
                'description' => '',
                'type'        => 'text',
                'default'     => '',
                'section'     => 'saturation_mapping_hidden',
            ];
            $fields['gn_timezone'] = [
                'name'        => 'Timezone',
                'description' => '',
                'type'        => 'text',
                'default'     => '',
                'section'     => 'saturation_mapping_hidden',
            ];
            $fields['gn_modification_date'] = [
                'name'        => 'Modification Date',
                'description' => '',
                'type'        => 'text',
                'default'     => '',
                'section'     => 'saturation_mapping_hidden',
            ];
            $fields['gn_geojson'] = [
                'name'        => 'GeoJSON Polygon',
                'description' => 'Add only the contents of "geometry". Add only "features->geometry->{GEOJSON SNIPPET}" ',
                'type'        => 'text',
                'default'     => '',
                'section'     => 'saturation_mapping_hidden',
            ];
            $fields['zoom_level'] = [
                'name'        => 'Default Zoom',
                'description' => 'Choose between 1-15. 1 is widest. 15 is closest.',
                'type'        => 'number',
                'default'     => '',
                'section'     => 'saturation_mapping_hidden',
            ];
        }
        return $fields;
    }

    public function get_child_populations() {
        global $post_id;

        if ( empty( $post_id ) ) {
            return 0;
        }

        // Set up the objects needed
        $my_wp_query = new WP_Query();
        $all_wp_pages = $my_wp_query->query( array(
            'post_type' => 'locations',
            'posts_per_page' => '-1'
        ) );

        $children = get_page_children( $post_id, $all_wp_pages );

        return $children;

    }

    public function add_geojson() {
        global $post;
        if ( empty( $post ) || ! $post->post_type === 'locations' ) {
            return;
        }

        $geojson = get_post_meta( $post->ID, 'geojson', true );
        if ( ! $geojson ) {
            return;
        }
        $nonce = wp_create_nonce( 'dt_location_map_'.$post->ID );
        $setZoom = '';
        if ( $level = get_post_meta( $post->ID, 'zoom_level', true ) ) {
            $setZoom = 'map.setZoom('.$level.');';
        }

        echo "
        map.data.loadGeoJson('/wp-content/plugins/disciple-tools-saturation-mapping/exports/geojson.php?page=".$post->ID."&nonce=".$nonce."');
        map.data.setStyle({
          fillColor: 'green',
          strokeWeight: 1
        });
        ".$setZoom."
        ";
    }


}
new DT_Saturation_Mapping_Metabox();
