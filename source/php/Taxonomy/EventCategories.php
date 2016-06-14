<?php

namespace HbgEventImporter\Taxonomy;

class EventCategories
{
    public function __construct()
    {
        add_action('init', array($this, 'registerTaxonomy'));
    }

    public function registerTaxonomy()
    {
        $labels = array(
            'name'                  => _x('Event categories', 'Taxonomy plural name', 'hbg-event-importer'),
            'singular_name'         => _x('Event category', 'Taxonomy singular name', 'hbg-event-importer'),
            'search_items'          => __('Search Event Categories', 'hbg-event-importer'),
            'popular_items'         => __('Popular Event Categories', 'hbg-event-importer'),
            'all_items'             => __('All Event Categories', 'hbg-event-importer'),
            'parent_item'           => __('Parent Event Category', 'hbg-event-importer'),
            'parent_item_colon'     => __('Parent Event Category', 'hbg-event-importer'),
            'edit_item'             => __('Edit Event Category', 'hbg-event-importer'),
            'update_item'           => __('Update Event Category', 'hbg-event-importer'),
            'add_new_item'          => __('Add New Event Category', 'hbg-event-importer'),
            'new_item_name'         => __('New Event Category', 'hbg-event-importer'),
            'add_or_remove_items'   => __('Add or remove Event Categories', 'hbg-event-importer'),
            'choose_from_most_used' => __('Choose from most used Event Categories', 'hbg-event-importer'),
            'menu_name'             => __('Event Category', 'hbg-event-importer'),
        );

        $args = array(
            'labels'                => $labels,
            'public'                => true,
            'show_in_nav_menus'     => true,
            'show_admin_column'     => false,
            'hierarchical'          => false,
            'show_tagcloud'         => true,
            'show_ui'               => true,
            'query_var'             => true,
            'rewrite'               => true
        );

        register_taxonomy('event-categories', array('event'), $args);
    }
}
