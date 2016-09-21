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
            'name'                  => _x('Event tags', 'Taxonomy plural name', 'hbg-event-importer'),
            'singular_name'         => _x('Event tag', 'Taxonomy singular name', 'hbg-event-importer'),
            'search_items'          => __('Search Event Tags', 'hbg-event-importer'),
            'popular_items'         => __('Popular Event Tags', 'hbg-event-importer'),
            'all_items'             => __('All Event Tags', 'hbg-event-importer'),
            'parent_item'           => __('Parent Event Tag', 'hbg-event-importer'),
            'parent_item_colon'     => __('Parent Event Tag', 'hbg-event-importer'),
            'edit_item'             => __('Edit Event Tag', 'hbg-event-importer'),
            'update_item'           => __('Update Event Tag', 'hbg-event-importer'),
            'add_new_item'          => __('Add New Event Tag', 'hbg-event-importer'),
            'new_item_name'         => __('New Event Tag', 'hbg-event-importer'),
            'add_or_remove_items'   => __('Add or remove Event Tags', 'hbg-event-importer'),
            'choose_from_most_used' => __('Choose from most used Event Tags', 'hbg-event-importer'),
            'menu_name'             => __('Tags', 'hbg-event-importer'),
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
