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
            'name'                  => _x('Location categories', 'Taxonomy plural name', 'hbg-event-importer'),
            'singular_name'         => _x('Location category', 'Taxonomy singular name', 'hbg-event-importer'),
            'search_items'          => __('Search Location Categories', 'hbg-event-importer'),
            'popular_items'         => __('Popular Location Categories', 'hbg-event-importer'),
            'all_items'             => __('All Location Categories', 'hbg-event-importer'),
            'parent_item'           => __('Parent Location Category', 'hbg-event-importer'),
            'parent_item_colon'     => __('Parent Location Category', 'hbg-event-importer'),
            'edit_item'             => __('Edit Location Category', 'hbg-event-importer'),
            'update_item'           => __('Update Location Category', 'hbg-event-importer'),
            'add_new_item'          => __('Add New Location Category', 'hbgevent-importer'),
            'new_item_name'         => __('New Location Category', 'hbg-event-importer'),
            'add_or_remove_items'   => __('Add or remove Location Categories', 'hbg-event-importer'),
            'choose_from_most_used' => __('Choose from most used Location Categories', 'hbg-event-importer'),
            'menu_name'             => __('Location Category', 'hbg-event-importer'),
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

        register_taxonomy('location-categories', array('location'), $args);
    }
}
