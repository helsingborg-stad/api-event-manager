<?php

namespace HbgEventImporter\Taxonomy;

/* DEPRICATED */

class PointProperty
{
    public function __construct()
    {
        add_action('init', array($this, 'registerTaxonomy'));
    }

    public function registerTaxonomy()
    {
        $labels = array(
            'name'                  => _x('Point properties', 'Taxonomy plural name', 'event-manager'),
            'singular_name'         => _x('Point propery', 'Taxonomy singular name', 'event-manager'),
            'search_items'          => __('Search properties', 'event-manager'),
            'popular_items'         => __('Popular properties', 'event-manager'),
            'all_items'             => __('All properties types', 'event-manager'),
            'parent_item'           => __('Parent properties', 'event-manager'),
            'parent_item_colon'     => __('Parent properties', 'event-manager'),
            'edit_item'             => __('Edit property', 'event-manager'),
            'update_item'           => __('Update property', 'event-manager'),
            'add_new_item'          => __('Add new property', 'event-manager'),
            'new_item_name'         => __('New property', 'event-manager'),
            'add_or_remove_items'   => __('Add or remove property', 'event-manager'),
            'choose_from_most_used' => __('Choose from most used properties', 'event-manager'),
            'menu_name'             => __('Point properties', 'event-manager'),
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

        register_taxonomy('property', array('location', 'guide', 'recommendation'), $args);
    }
}
