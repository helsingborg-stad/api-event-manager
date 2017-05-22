<?php

namespace HbgEventImporter\PostTypes;

class Organizers extends \HbgEventImporter\Entity\CustomPostType
{
    public function __construct()
    {
        parent::__construct(
            __('Organizers', 'event-manager'),
            __('Organizer', 'event-manager'),
            'organizer',
            array(
                'description'          => 'Organizers in the system, partially parsed from events.',
                'menu_icon'            => 'dashicons-megaphone',
                'public'               => true,
                'publicly_queriable'   => true,
                'show_ui'              => true,
                'show_in_nav_menus'    => true,
                'has_archive'          => true,
                'rewrite'              => array(
                    'slug'       => 'organizer',
                    'with_front' => false
                ),
                'hierarchical'         => false,
                'exclude_from_search'  => false,
                'supports'             => array('title', 'revisions', 'editor', 'thumbnail'),
                'map_meta_cap'         => true,
                'capability_type'      => 'organizer'
            )
        );

        $this->addTableColumn('cb', '<input type="checkbox">');
        $this->addTableColumn('title', __('Title', 'event-manager'));
        $this->addTableColumn('email', __('Email', 'event-manager'), true, function ($column, $postId) {
            echo get_post_meta($postId, 'email', true) ? get_post_meta($postId, 'email', true) :  __('n/a', 'event-manager');
        });
        $this->addTableColumn('phone', __('Phone', 'event-manager'), true, function ($column, $postId) {
            echo get_post_meta($postId, 'phone', true) ? get_post_meta($postId, 'phone', true) : __('n/a', 'event-manager');
        });
        $this->addTableColumn('date', __('Date', 'event-manager'));

        add_action('do_meta_boxes', array($this, 'changeImageBox'), 10, 3);
        add_filter('acf/update_value/key=field_591eefdc13c43', array($this, 'acfUpdatePhone'), 10, 3);
        add_filter('acf/update_value/key=field_591ef12413c49', array($this, 'acfUpdatePhone'), 10, 3);
        add_filter('manage_edit-' . $this->slug . '_columns', array($this, 'addAcceptDenyTable'));
        add_action('manage_' . $this->slug . '_posts_custom_column', array($this, 'addAcceptDenyButtons'), 10, 2);
    }

    public function changeImageBox($page, $context, $object)
    {
        if ($page !== 'organizer') {
            return;
        }

        remove_meta_box('postimagediv', 'organizer', 'side');
        add_meta_box('postimagediv', __('Logo'), 'post_thumbnail_meta_box', 'organizer', 'side');
        remove_action('do_meta_boxes', array($this, 'changeImageBox'));
    }

}
