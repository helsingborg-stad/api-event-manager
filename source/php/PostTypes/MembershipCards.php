<?php

namespace HbgEventImporter\PostTypes;

class MembershipCards extends \HbgEventImporter\Entity\CustomPostType
{
    public function __construct()
    {
        parent::__construct(
            __('Membership Cards', 'event-manager'),
            __('Membership Card', 'event-manager'),
            'membership-card',
            array(
                'description'          => 'Information absout membership cards',
                'menu_icon'            => 'dashicons-tickets-alt',
                'public'               => true,
                'publicly_queriable'   => true,
                'show_ui'              => true,
                'show_in_nav_menus'    => true,
                'has_archive'          => true,
                'rewrite'              => array(
                    'slug'       => 'membership-card',
                    'with_front' => false
                ),
                'hierarchical'         => false,
                'exclude_from_search'  => false,
                'supports'             => array('title', 'revisions', 'editor', 'thumbnail')
            )
        );

        $this->addTableColumn('cb', '<input type="checkbox">');
        $this->addTableColumn('title', __('Title', 'event-manager'));
        $this->addTableColumn('date', __('Date', 'event-manager'));

        add_action('do_meta_boxes', array($this, 'changeImageBox'), 10, 3);
        add_filter('manage_edit-' . $this->slug . '_columns', array($this, 'addAcceptDenyTable'));
        add_action('manage_' . $this->slug . '_posts_custom_column', array($this,'addAcceptDenyButtons'), 10, 2);
    }

    public function changeImageBox($page, $context, $object)
    {
        if ($page !== 'membership-card') {
            return;
        }

        remove_meta_box('postimagediv', 'membership-card', 'side');
        add_meta_box('postimagediv', __('Image'), 'post_thumbnail_meta_box', 'membership-card', 'side');
        remove_action('do_meta_boxes', array($this, 'changeImageBox'));
    }
}
