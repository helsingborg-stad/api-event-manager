<?php

namespace HbgEventImporter\Taxonomy;

class GuideCategories
{
    public function __construct()
    {
        add_action('init', array($this, 'registerTaxonomy'));
        add_action('admin_menu', array($this, 'unregisterMetaBox'));
    }

    public function registerTaxonomy()
    {
        $labels = array(
            'name'                  => _x('Guide group', 'Taxonomy plural name', 'event-manager'),
            'singular_name'         => _x('Guide group', 'Taxonomy singular name', 'event-manager'),
            'search_items'          => __('Search guide group', 'event-manager'),
            'popular_items'         => __('Popular guide groups', 'event-manager'),
            'all_items'             => __('All guide groups', 'event-manager'),
            'parent_item'           => __('Parent guide group', 'event-manager'),
            'parent_item_colon'     => __('Parent guide group', 'event-manager'),
            'edit_item'             => __('Edit guide group', 'event-manager'),
            'update_item'           => __('Update guide group', 'event-manager'),
            'add_new_item'          => __('Add new guide group', 'event-manager'),
            'new_item_name'         => __('New guide group', 'event-manager'),
            'add_or_remove_items'   => __('Add or remove guide group', 'event-manager'),
            'choose_from_most_used' => __('Choose from most used guide groups', 'event-manager'),
            'menu_name'             => __('Guide groups', 'event-manager'),
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

        register_taxonomy('guide_group', array('guide'), $args);
    }

    public function unregisterMetaBox()
    {
        remove_meta_box('tagsdiv-guide_group', 'guide', 'side');
    }
}
