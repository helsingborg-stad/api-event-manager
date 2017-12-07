<?php

namespace HbgEventImporter\Taxonomy;

/* DEPRICATED */

class GuideType
{
    public function __construct()
    {
        add_action('init', array($this, 'registerTaxonomy'));
    }

    public function registerTaxonomy()
    {
        $labels = array(
            'name'                  => _x('Guide type', 'Taxonomy plural name', 'event-manager'),
            'singular_name'         => _x('Guide type', 'Taxonomy singular name', 'event-manager'),
            'search_items'          => __('Search guide type', 'event-manager'),
            'popular_items'         => __('Popular guide types', 'event-manager'),
            'all_items'             => __('All guide types', 'event-manager'),
            'parent_item'           => __('Parent guide type', 'event-manager'),
            'parent_item_colon'     => __('Parent guide type', 'event-manager'),
            'edit_item'             => __('Edit guide type', 'event-manager'),
            'update_item'           => __('Update guide type', 'event-manager'),
            'add_new_item'          => __('Add new guide type', 'event-manager'),
            'new_item_name'         => __('New guide type', 'event-manager'),
            'add_or_remove_items'   => __('Add or remove guide type', 'event-manager'),
            'choose_from_most_used' => __('Choose from most used guide types', 'event-manager'),
            'menu_name'             => __('Guide types', 'event-manager'),
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

        register_taxonomy('guidetype', array('guide'), $args);
    }
}
