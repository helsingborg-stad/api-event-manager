<?php

namespace HbgEventImporter\Entity;

abstract class CustomPostType
{
    protected $namePlural;
    protected $nameSingular;
    protected $slug;
    protected $args;

    /**
     * Registers a custom post type
     * @param string $namePlural   Post type name in plural
     * @param string $nameSingular Post type name in singular
     * @param string $slug         Post type slug
     * @param array  $args         Post type arguments
     */
    public function __construct($namePlural, $nameSingular, $slug, $args = array())
    {
        $this->namePlural = $namePlural;
        $this->nameSingular = $nameSingular;
        $this->slug = $slug;
        $this->args = $args;

        // Register post type on init
        add_action('init', array($this, 'registerPostType'));
    }

    /**
     * Registers the post type with WP
     * @return string Post type slug
     */
    public function registerPostType()
    {
        $labels = array(
            'name'                => $this->nameSingular,
            'singular_name'       => $this->nameSingular,
            'add_new'             => sprintf(__('Add New %s', 'event-manager'), $this->nameSingular),
            'add_new_item'        => sprintf(__('Add New %s', 'event-manager'), $this->nameSingular),
            'edit_item'           => sprintf(__('Edit %s', 'event-manager'), $this->nameSingular),
            'new_item'            => sprintf(__('New %s', 'event-manager'), $this->nameSingular),
            'view_item'           => sprintf(__('View %s', 'event-manager'), $this->nameSingular),
            'search_items'        => sprintf(__('Search %s', 'event-manager'), $this->namePlural),
            'not_found'           => sprintf(__('No %s found', 'event-manager'), $this->namePlural),
            'not_found_in_trash'  => sprintf(__('No %s found in Trash', 'event-manager'), $this->namePlural),
            'parent_item_colon'   => sprintf(__('Parent %s:', 'event-manager'), $this->nameSingular),
            'menu_name'           => $this->namePlural,
        );

        $this->args['labels'] = $labels;

        register_post_type($this->slug, $this->args);

        return $this->slug;
    }
}
