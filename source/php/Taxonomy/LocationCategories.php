<?php

namespace HbgEventImporter\Taxonomy;

class LocationCategories
{
    public function __construct()
    {
        add_action('init', array($this, 'registerTaxonomy'));
    }

    public function registerTaxonomy()
    {
        $labels = array(
            'name'                  => _x('Location categories', 'Taxonomy plural name', 'event-manager'),
            'singular_name'         => _x('Location category', 'Taxonomy singular name', 'event-manager'),
            'search_items'          => __('Search categories', 'event-manager'),
            'popular_items'         => __('Popular categories', 'event-manager'),
            'all_items'             => __('All categories', 'event-manager'),
            'parent_item'           => __('Parent category', 'event-manager'),
            'parent_item_colon'     => __('Parent category', 'event-manager'),
            'edit_item'             => __('Edit category', 'event-manager'),
            'update_item'           => __('Update category', 'event-manager'),
            'add_new_item'          => __('Add new category', 'event-manager'),
            'new_item_name'         => __('New category', 'event-manager'),
            'add_or_remove_items'   => __('Add or remove categories', 'event-manager'),
            'choose_from_most_used' => __('Choose from most used categories', 'event-manager'),
            'menu_name'             => __('Categories', 'event-manager'),
        );

        $capabilities = array(
            'manage_terms' => 'export',
            'edit_terms'   => 'export',
            'delete_terms' => 'export',
            'assign_terms' => 'read',
        );

        $args = array(
            'capabilities'          => $capabilities,
            'labels'                => $labels,
            'public'                => true,
            'show_in_nav_menus'     => true,
            'show_admin_column'     => true,
            'hierarchical'          => true,
            'show_tagcloud'         => true,
            'show_ui'               => true,
            'query_var'             => true,
            'rewrite'               => true
        );

        register_taxonomy('location_categories', array('location'), $args);
    }
}
