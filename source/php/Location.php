<?php

namespace HbgEventImporter;

class Location extends \HbgEventImporter\Post
{
    public static function initPostType()
    {
        $labels = array(
            'name'               => _x('Location', 'post type name'),
            'singular_name'      => _x('Location', 'post type singular name'),
            'menu_name'          => __('Location'),
            'add_new'            => __('Lägg till ny plats'),
            'add_new_item'       => __('Lägg till plats'),
            'edit_item'          => __('Redigera plats'),
            'new_item'           => __('Ny plats'),
            'all_items'          => __('Alla platser'),
            'view_item'          => __('Visa plats'),
            'search_items'       => __('Sök plats'),
            'not_found'          => __('Inga platser att visa'),
            'not_found_in_trash' => __('Inga platser i papperskorgen')
        );

        $args = array(
            'labels'               => $labels,
            'description'          => 'Locations',
            'menu_icon'            => 'dashicons-store',
            'public'               => true,
            'publicly_queriable'   => true,
            'show_ui'              => true,
            'show_in_nav_menus'    => true,
            'has_archive'          => true,
            'rewrite'              => array(
                'slug'       => 'location',
                'with_front' => false
            ),
            'hierarchical'         => false,
            'exclude_from_search'  => false,
            'supports'             => array('title', 'revisions', 'editor', 'thumbnail')
        );

        register_post_type('location', $args);
    }

    public static function get($count = 10, $metaQuery = null, $postStatus = array(), $postType = 'location')
    {
        return parent::get($count, $metaQuery, $postStatus, $postType);
    }

    /**
     * Get poi:s
     * @param  integer $count     Number of POI to get
     * @param  array   $metaQuery Optional meta query array
     * @return object             Object with POI posts
     */
    /*public static function get($count = 10, $metaQuery = null, $postStatus = array())
    {
        $args = array(
            'posts_per_page' => $count,
            'post_type'      => 'location',
            'orderby'        => 'date',
            'order'          => 'DESC'
        );

        if ($postStatus) {
            $args['post_status'] = $postStatus;
        }

        if (is_array($metaQuery)) {
            $args['meta_query'] = $metaQuery;
        }

        $posts = get_posts($args);

        if ($count == 1 && isset($posts[0])) {
            $posts = $posts[0];
        }

        return $posts;
    }*/

    /**
     * Craete a post of type location
     * @param  string $postId The post's id
     * @param  array $data Holds all data from export
     * @return void
     */
    public static function add($data)
    {
        $requiredKeysExists = \HbgEventImporter\Helper\Arr::arrayKeysExist(
            $data,
            'locationName',
            'locationDescription',
            'country',
            'municipality',
            'city',
            'address',
            'postcode',
            'latitude',
            'longitude'
        );

        if (!$requiredKeysExists) {
            return false;
        }

        //See if location already exists
        $postExists = Location::get(1, array(
            array(
                'key' => 'postalAddress',
                'value' => $data['address'],
                'compare' => '='
            )
        ), array('publish', 'draft', 'pending'), 'location');

        $postId = isset($postExists->ID) ? $postExists->ID : null;

        if($postId != null)
        {
            echo "Location with address: " . $data['address'] . " already exists!\n It have post id: " . $postId . "!\n";
            return $postId;
        }

        //Create new location post and collect the id
        $postId = wp_insert_post(array(
            'post_title'   => $data['locationName'],
            'post_content' => $data['locationDescription'] != null ? $data['locationDescription'] : "",
            'post_status'  => 'publish',
            'post_type'    => 'location'
        ));

        self::addMeta($postId, $data);

        return $postId;
    }

    private static function addMeta($postId, $data)
    {
        update_post_meta($postId, 'country', $data['country']);
        update_post_meta($postId, 'municipality', $data['municipality']);
        update_post_meta($postId, 'city', $data['city']);
        update_post_meta($postId, 'postalAddress', $data['address']);
        update_post_meta($postId, 'postcode', $data['postcode']);

        //Default coordinates are what you get if you input 'Helsingborg' in google maps
        if($data['latitude'] == null || $data['longitude'] == null)
        {
            $mapLat = "56.046467";
            $mapLong = "12.694512";
            $mapAddress = "Helsingborg, Sweden";
        }
        else
        {
            $mapLat = $data['latitude'];
            $mapLong = $data['longitude'];
            $mapAddress = "";
            if($data['address'] != null)
                $mapAddress .= $data['address'] . ", ";
            if($data['city'] != null)
                $mapAddress .= $data['city'] . ", ";
            else
                $mapAddress .= "Helsingborg, ";
            //if($data['country'] != null)
            $mapAddress .= "Sweden";
        }

        $mapArray = array('address' => $mapAddress, 'lat' => $mapLat, 'lng' => $mapLong);

        update_field('map', $mapArray, $postId);
    }
}
