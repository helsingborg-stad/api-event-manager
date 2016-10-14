<?php

namespace HbgEventImporter\PostTypes;

class Contacts extends \HbgEventImporter\Entity\CustomPostType
{
    public function __construct()
    {
        parent::__construct(
            __('Contacts', 'event-manager'),
            __('Contact', 'event-manager'),
            'contact',
            array(
                'description'          => 'Contacts',
                'menu_icon'            => 'dashicons-admin-users',
                'public'               => false,
                'publicly_queriable'   => true,
                'show_ui'              => true,
                'show_in_nav_menus'    => true,
                'has_archive'          => true,
                'rewrite'              => array(
                    'slug'       => 'contact',
                    'with_front' => false
                ),
                'hierarchical'         => false,
                'exclude_from_search'  => false,
                'supports'             => array('title', 'revisions', 'editor', 'thumbnail')
            )
        );

        $this->addTableColumn('cb', '<input type="checkbox">');
        $this->addTableColumn('title', __('Title'));
        $this->addTableColumn('name', __('Name'), true, function ($column, $postId) {
            echo get_post_meta($postId, 'name', true) ? get_post_meta($postId, 'name', true) : 'n/a';
        });
        $this->addTableColumn('email', __('Email'), true, function ($column, $postId) {
            echo get_post_meta($postId, 'email', true) ? get_post_meta($postId, 'email', true) : 'n/a';
        });
        $this->addTableColumn('phone', __('Phone'), true, function ($column, $postId) {
            echo get_post_meta($postId, 'phone_number', true) ? get_post_meta($postId, 'phone_number', true) : 'n/a';
        });
        $this->addTableColumn('date', __('Date'));
        add_action('do_meta_boxes', array($this, 'changeImageBox'), 10, 3);
    }

    public function changeImageBox($page, $context, $object)
    {
        if($page == 'contact')
        {
            remove_meta_box( 'postimagediv', 'contact', 'side' );
            add_meta_box('postimagediv', __('Profile image'), 'post_thumbnail_meta_box', 'contact', 'side');
            remove_action('do_meta_boxes', array($this, 'changeImageBox'));
        }
    }

}
