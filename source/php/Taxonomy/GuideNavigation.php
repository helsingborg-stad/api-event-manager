<?php

namespace HbgEventImporter\Taxonomy;

class GuideNavigation
{
    public function __construct()
    {
        add_action('init', array($this, 'registerTaxonomy'));

        add_filter('acf/update_value/name=included_posts', array($this, 'addPostToTerm'), 10, 3);
    }

    public function registerTaxonomy()
    {
        $labels = array(
            'name'                  => _x('Guide navigation', 'Taxonomy plural name', 'event-manager'),
            'singular_name'         => _x('Guide navigation item', 'Taxonomy singular name', 'event-manager'),
            'search_items'          => __('Search navigation item', 'event-manager'),
            'popular_items'         => __('Popular navigation items', 'event-manager'),
            'all_items'             => __('All navigation items', 'event-manager'),
            'parent_item'           => __('Parent navigation item', 'event-manager'),
            'parent_item_colon'     => __('Parent navigation item', 'event-manager'),
            'edit_item'             => __('Edit navigation item', 'event-manager'),
            'update_item'           => __('Update navigation item', 'event-manager'),
            'add_new_item'          => __('Add new navigation item', 'event-manager'),
            'new_item_name'         => __('New navigation item', 'event-manager'),
            'add_or_remove_items'   => __('Add or remove navigation item', 'event-manager'),
            'choose_from_most_used' => __('Choose from most used navigation items', 'event-manager'),
            'menu_name'             => __('Guide navigation', 'event-manager'),
        );

        $capabilities = array(
            'manage_terms' => 'read_private_guides',
            'edit_terms'   => 'read_private_guides',
            'delete_terms' => 'read_private_guides',
            'assign_terms' => 'read',
        );

        $args = array(
            'capabilities'          => $capabilities,
            'labels'                => $labels,
            'public'                => true,
            'show_in_nav_menus'     => true,
            'show_admin_column'     => true,
            'hierarchical'          => false,
            'show_tagcloud'         => false,
            'show_ui'               => true,
            'query_var'             => true,
            'rewrite'               => true,
            'show_in_rest'          => true,
        );

        register_taxonomy('navigation', array('guide'), $args);
    }

    /**
     * Update taxonomy connection to selected posts
     * @param  $value     Value before save
     * @param  $post_id   Id of the post being saved or updated
     * @param  $field     Array containing field details
     */
    public function addPostToTerm($value, $post_id, $field)
    {

        /*

        var_dump($post_id);
        var_dump($value);
        exit;

/*
        if(!is_numeric($post_id)) {
            $post_id = get_term( $term, $taxonomy = '', $output = OBJECT, $filter = 'raw' )
        }

        //wp_set_object_terms((int) $post_id, array((int) $value), 'navigation');*/
        return $value;
    }
}
