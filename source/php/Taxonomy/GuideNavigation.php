<?php

namespace HbgEventImporter\Taxonomy;

class GuideNavigation
{
    public function __construct()
    {
        add_action('init', array($this, 'registerTaxonomy'));
        add_action('admin_menu', array($this, 'unregisterMetaBox'));
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

        register_taxonomy('navigation', array('guide', 'recommendation'), $args);
    }

    public function unregisterMetaBox()
    {
        remove_meta_box('tagsdiv-navigation', 'guide', 'side');
        remove_meta_box('tagsdiv-navigation', 'recommendation', 'side');
    }
}
