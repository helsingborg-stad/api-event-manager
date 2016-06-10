<?php

namespace HbgEventImporter;

class Contact extends \HbgEventImporter\Post
{
    public static function initPostType()
    {
        $labels = array(
            'name'               => _x('Contact', 'post type name'),
            'singular_name'      => _x('Contact', 'post type singular name'),
            'menu_name'          => __('Contact'),
            'add_new'            => __('Lägg till ny kontakt'),
            'add_new_item'       => __('Lägg till kontakt'),
            'edit_item'          => __('Redigera kontakt'),
            'new_item'           => __('Ny kontakt'),
            'all_items'          => __('Alla kontakter'),
            'view_item'          => __('Visa kontakt'),
            'search_items'       => __('Sök kontakt'),
            'not_found'          => __('Inga kontakter att visa'),
            'not_found_in_trash' => __('Inga kontakter i papperskorgen')
        );

        $args = array(
            'labels'               => $labels,
            'description'          => 'Contacts',
            'menu_icon'            => 'dashicons-admin-users',
            'public'               => true,
            'publicly_queriable'   => true,
            'show_ui'              => true,
            'show_in_nav_menus'    => true,
            'has_archive'          => true,
            'rewrite'              => array(
                'slug'       => 'contact',
                'with_front' => false
            ),
            'hierarchical'         => false,
            'exclude_from_search'  => false,
            'supports'             => array('title', 'revisions', 'editor', 'thumbnail')
        );

        register_post_type('contact', $args);
    }

    public static function get($count = 10, $metaQuery = null, $postStatus = array(), $postType = 'contact')
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
            'post_type'      => 'contact',
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
     * Craete a post of type contact
     * @param  string $postId The post's id
     * @param  array $data Holds all data from export
     * @return contact id
     */
    public static function add($data)
    {
        //See if location already exists
        $postExists = Contact::get(1, array(
            array(
                'key' => 'email',
                'value' => $data['contactEmail'],
                'compare' => '='
            )
        ), array('publish', 'draft', 'pending'), 'contact');

        $postId = isset($postExists->ID) ? $postExists->ID : null;

        if($postId != null)
        {
            return $postId;
        }

        $postId = wp_insert_post(array(
            'post_title'   => $data['contactPerson'],
            'post_content' => "",
            'post_status'  => 'publish',
            'post_type'    => 'contact'
        ));

        self::addMeta($postId, $data);

        return $postId;
    }

    private static function addMeta($postId, $data)
    {
        update_post_meta($postId, 'email', $data['contactEmail']);
        update_post_meta($postId, 'phoneNumber', $data['contactPhoneNumber']);
    }
}
