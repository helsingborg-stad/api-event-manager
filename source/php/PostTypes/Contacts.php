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
                'description'          => 'Contacts in the system, partially parsed from events.',
                'menu_icon'            => 'dashicons-admin-users',
                'public'               => true,
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
        $this->addTableColumn('title', __('Title', 'event-manager'));
        $this->addTableColumn('name', __('Name', 'event-manager'), true, function ($column, $postId) {
            echo get_post_meta($postId, 'name', true) ? get_post_meta($postId, 'name', true) :  __('n/a', 'event-manager');
        });
        $this->addTableColumn('email', __('Email', 'event-manager'), true, function ($column, $postId) {
            echo get_post_meta($postId, 'email', true) ? get_post_meta($postId, 'email', true) :  __('n/a', 'event-manager');
        });
        $this->addTableColumn('phone', __('Phone', 'event-manager'), true, function ($column, $postId) {
            echo get_post_meta($postId, 'phone_number', true) ? get_post_meta($postId, 'phone_number', true) : __('n/a', 'event-manager');
        });
        $this->addTableColumn('acceptAndDeny', __('Public', 'event-manager'), false, function ($column, $postId) {
            $post_status = get_post_status($postId);
            $first = '';
            $second = '';
            if ($post_status == 'publish') {
                $first = 'hiddenElement';
            } else {
                $second = 'hiddenElement';
            }
            echo '<a href="#" class="accept button-primary ' . $first . '" postid="' . $postId . '">' . __('Accept', 'event-manager') . '</a>
            <a href="#" class="deny button-primary ' . $second . '" postid="' . $postId . '">' . __('Deny', 'event-manager') . '</a>';
        });
        $this->addTableColumn('date', __('Date', 'event-manager'));
        add_action('do_meta_boxes', array($this, 'changeImageBox'), 10, 3);
        add_filter('acf/update_value/key=field_57445c09358ce', array($this, 'acfUpdatePhone'), 10, 3);
    }

    public function changeImageBox($page, $context, $object)
    {
        if($page == 'contact')
        {
            remove_meta_box( 'postimagediv', 'contact', 'side' );
            add_meta_box('postimagediv', __('Profile image', 'event-manager'), 'post_thumbnail_meta_box', 'contact', 'side');
            remove_action('do_meta_boxes', array($this, 'changeImageBox'));
        }
    }

}
