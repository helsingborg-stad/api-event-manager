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
            'name'                  => _x('Guide sender', 'Taxonomy plural name', 'event-manager'),
            'singular_name'         => _x('Guide sender', 'Taxonomy singular name', 'event-manager'),
            'search_items'          => __('Search guidesender', 'event-manager'),
            'popular_items'         => __('Popular guidesenders', 'event-manager'),
            'all_items'             => __('All guidesenders', 'event-manager'),
            'parent_item'           => __('Parent guidesenders', 'event-manager'),
            'parent_item_colon'     => __('Parent guidesender', 'event-manager'),
            'edit_item'             => __('Edit guidesender', 'event-manager'),
            'update_item'           => __('Update guidesender', 'event-manager'),
            'add_new_item'          => __('Add new guidesender', 'event-manager'),
            'new_item_name'         => __('New guidesender', 'event-manager'),
            'add_or_remove_items'   => __('Add or remove guidesender', 'event-manager'),
            'choose_from_most_used' => __('Choose from most used guidesender', 'event-manager'),
            'menu_name'             => __('Guide sender', 'event-manager'),
        );

        $args = array(
            'labels'                => $labels,
            'public'                => true,
            'show_in_nav_menus'     => true,
            'show_admin_column'     => true,
            'hierarchical'          => false,
            'show_tagcloud'         => false,
            'show_ui'               => true,
            'query_var'             => true,
            'rewrite'               => true
        );

        register_taxonomy('guide_sender', array('guide'), $args);
    }

    public function unregisterMetaBox()
    {
        remove_meta_box('tagsdiv-guide_sender', 'guide', 'side');
    }
}
