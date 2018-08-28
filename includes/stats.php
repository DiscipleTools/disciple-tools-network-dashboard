<?php

class DT_Saturation_Mapping_Stats {
    public static function get_location_tree() {
        $table_data = self::query_location_population_groups();

        $chart = [];
        foreach ( $table_data as $row ) {
            $chart[] = [ [ 'v' => $row['location'], 'f' => $row['location'] . '<br>pop: ' . $row['population'] . '<br>need: ' . $row['groups_needed']], $row['parent_name'], ''];
        }

        return $chart;
    }

    public static function get_location_table() {
        $table_data = self::query_location_population_groups();

        $chart = [];
        foreach ( $table_data as $row ) {
            $chart[] = [ $row['location'], (int) $row['population'], (int) $row['groups_needed'], (int) $row['groups']];
        }

        return $chart;
    }

    public static function get_location_map() {
        $table_data = self::query_location_latlng();

        $chart = [];
        $chart[] = ['Lat', 'Long', 'Name'];
        foreach ( $table_data as $row ) {
            if ( ! empty( $row['latitude'] ) && ! empty( $row['longitude'] ) ) {
                $chart[] = [
                    (float) $row['latitude'], (float) $row['longitude'], $row['location']
                ];
            }
        }

        return $chart;
    }

    public static function get_location_side_tree() {
        $table_data = self::query_location_population_groups();

        $chart = [];
        $chart[] = ['id', 'childLabel', 'parent', 'size', [ 'role' => 'style' ]];
        foreach ( $table_data as $row ) {
            if ( $row['parent_id'] == 0 ) {
                $row['parent_id'] = -1;
            }
            $chart[] = [ (int) $row['id'], $row['location'], (int) $row['parent_id'], 1, 'black' ];
        }

        return $chart;
    }

    public static function query_location_population_groups() {
        global $wpdb;

        $results = $wpdb->get_results("
            SELECT 
            t1.ID as id, 
            t1.post_parent as parent_id, 
            t1.post_title as location,
            (SELECT post_title FROM $wpdb->posts WHERE ID = t1.post_parent) as parent_name,
            t2.meta_value as population, 
            ROUND(t2.meta_value / (SELECT option_value FROM $wpdb->options WHERE option_name = 'dt_saturation_mapping_pd'), 0 ) as groups_needed,
            (SELECT count(*) FROM $wpdb->p2p WHERE p2p_to = t1.ID) as groups
            FROM $wpdb->posts as t1
            LEFT JOIN $wpdb->postmeta as t2
            ON t1.ID=t2.post_id
            AND t2.meta_key = 'population'
            WHERE post_type = 'locations' AND post_status = 'publish'
        ", ARRAY_A );

        return $results;
    }

    public static function query_location_latlng() {
        global $wpdb;

        $results = $wpdb->get_results("
            SELECT 
            t2.meta_value as latitude,
            t3.meta_value as longitude,
            t1.post_title as location
            FROM $wpdb->posts as t1
            LEFT JOIN $wpdb->postmeta as t2
            ON t1.ID=t2.post_id
            AND t2.meta_key = 'latitude'
            LEFT JOIN $wpdb->postmeta as t3
            ON t1.ID=t3.post_id
            AND t3.meta_key = 'longitude'
            WHERE post_type = 'locations' 
            AND post_status = 'publish'
            AND post_parent != '0'
        ", ARRAY_A );

        return $results;
    }

    public static function get_locations_level_tree() {
        global $wpdb;
        $query = $wpdb->get_results("
                    SELECT 
                    t1.ID as id, 
                    t1.post_parent as parent_id, 
                    t1.post_title as name,
                    t2.meta_value as population, 
                    ROUND(t2.meta_value / (SELECT option_value FROM $wpdb->options WHERE option_name = 'dt_saturation_mapping_pd'), 0 ) as groups_needed,
                    (SELECT count(*) FROM $wpdb->p2p WHERE p2p_to = t1.ID) as groups
                    FROM $wpdb->posts as t1
                    LEFT JOIN $wpdb->postmeta as t2
                    ON t1.ID=t2.post_id
                    AND t2.meta_key = 'population'
                    WHERE post_type = 'locations' AND post_status = 'publish'
                ", ARRAY_A );
        // prepare special array with parent-child relations
        $menuData = array(
            'items' => array(),
            'parents' => array()
        );
        foreach ( $query as $menuItem )
        {
            $menuData['items'][$menuItem['id']] = $menuItem;
            $menuData['parents'][$menuItem['parent_id']][] = $menuItem['id'];
        }

        function buildMenu($parent_id, $menuData, $gen)
        {
            $html = '';

            if (isset($menuData['parents'][$parent_id]))
            {
                $html = '<ul class="gen-ul ul-gen-'.$gen.'">';
                $gen++;
                foreach ($menuData['parents'][$parent_id] as $itemId)
                {
                    $html .= '<li class="gen-li li-gen-'.$gen.'">';
                    //            $html .= '(level: '.$gen.')<br> ';
                    $html .= '<strong>'. $menuData['items'][$itemId]['name'] . '</strong><br>';
                    $html .= 'population: '. ( $menuData['items'][$itemId]['population'] ?: '0' ) . '<br>';
                    $html .= 'groups needed: '. ( $menuData['items'][$itemId]['groups_needed'] ?: '0' ) . '<br>';
                    $html .= 'groups: '. $menuData['items'][$itemId]['groups'];

                    $html .= '</li>';

                    // find childitems recursively
                    $html .= buildMenu($itemId, $menuData, $gen);
                }
                $html .= '</ul>';
            }

            return $html;
        }

        $list = '<style>
                    .gen-ul {
                        list-style: none;
                        padding-left:30px;
                    }
                    .gen-li {
                        padding: 25px;
                        border: 1px solid grey;
                        margin-top: 10px;
                        width: 20%;
                        background: yellowgreen;
                        border-radius:10px;
                    }
                </style>';

        $list .= buildMenu(0, $menuData, -1);

        return $list;
    }
}