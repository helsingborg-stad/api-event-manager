<?php

namespace HbgEventImporter\PostTypes;

class Sponsors extends \HbgEventImporter\Entity\CustomPostType
{
    public function __construct()
    {
        parent::__construct(
            __('Sponsors', 'event-manager'),
            __('Sponsor', 'event-manager'),
            'sponsor',
            array(
                'description'          => 'Sponsors of events.',
                'menu_icon'            => 'dashicons-businessman',
                'public'               => true,
                'publicly_queriable'   => true,
                'show_ui'              => true,
                'show_in_nav_menus'    => true,
                'has_archive'          => true,
                'rewrite'              => array(
                    'slug'       => 'sponsor',
                    'with_front' => false
                ),
                'hierarchical'         => false,
                'exclude_from_search'  => false,
                'supports'             => array('title', 'revisions', 'editor', 'thumbnail'),
                'map_meta_cap'         => true,
                'capability_type'      => 'sponsor'
            )
        );

        $this->addTableColumn('cb', '<input type="checkbox">');
        $this->addTableColumn('title', __('Title', 'event-manager'));
        $this->addTableColumn('date', __('Date', 'event-manager'));

        add_action('do_meta_boxes', array($this, 'changeImageBox'), 10, 3);
        add_filter('acf/update_value/key=field_58183e709de27', array($this, 'acfUpdatePhone'), 10, 3);
        add_filter('manage_edit-' . $this->slug . '_columns', array($this, 'addAcceptDenyTable'));
        add_action('manage_' . $this->slug . '_posts_custom_column', array($this,'addAcceptDenyButtons'), 10, 2);
    }

    public function changeImageBox($page, $context, $object)
    {
        if ($page !== 'sponsor') {
            return;
        }

        remove_meta_box('postimagediv', 'sponsor', 'side');
        add_meta_box('postimagediv', __('Logo'), 'post_thumbnail_meta_box', 'sponsor', 'side');
        remove_action('do_meta_boxes', array($this, 'changeImageBox'));
    }
}
