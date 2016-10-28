<?php

namespace HbgEventImporter\Taxonomy;

class EventTags
{
    public function __construct()
    {
        add_action('init', array($this, 'registerEventTags'));
    }

    public function registerEventTags()
    {
        $labels = array(
            'name'                  => _x('Tags', 'Taxonomy plural name', 'event-manager'),
            'singular_name'         => _x('Tag', 'Taxonomy singular name', 'event-manager'),
            'search_items'          => __('Search tags', 'event-manager'),
            'popular_items'         => __('Popular tags', 'event-manager'),
            'all_items'             => __('All tags', 'event-manager'),
            'parent_item'           => __('Parent', 'event-manager'),
            'parent_item_colon'     => __('Parent', 'event-manager'),
            'edit_item'             => __('Edit tag', 'event-manager'),
            'update_item'           => __('Update tag', 'event-manager'),
            'add_new_item'          => __('Add new tag', 'event-manager'),
            'new_item_name'         => __('New tag', 'event-manager'),
            'add_or_remove_items'   => __('Add or remove tags', 'event-manager'),
            'choose_from_most_used' => __('Choose from most used tags', 'event-manager'),
            'menu_name'             => __('Tags', 'event-manager'),
        );

        $args = array(
            'labels'                => $labels,
            'public'                => true,
            'show_in_nav_menus'     => true,
            'show_admin_column'     => true,
            'hierarchical'          => false,
            'show_tagcloud'         => true,
            'show_ui'               => true,
            'query_var'             => true,
            'rewrite'               => true
        );

        register_taxonomy('event-tags', array('event'), $args);
    }
}
