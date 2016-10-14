<?php

namespace HbgEventImporter\PostTypes;

class Packages extends \HbgEventImporter\Entity\CustomPostType
{
    public function __construct()
    {
        parent::__construct(
            __('Packages', 'event-manager'),
            __('Package', 'event-manager'),
            'package',
            array(
                'description'          => 'Packages',
                'menu_icon'            => 'dashicons-screenoptions',
                'public'               => false,
                'publicly_queriable'   => true,
                'show_ui'              => true,
                'show_in_nav_menus'    => true,
                'has_archive'          => true,
                'rewrite'              => array(
                    'slug'       => 'package',
                    'with_front' => false
                ),
                'hierarchical'         => false,
                'exclude_from_search'  => false,
                'supports'             => array('title', 'revisions', 'editor', 'thumbnail')
            )
        );

        $this->addTableColumn('cb', '<input type="checkbox">');
        $this->addTableColumn('title', __('Title'));
        $this->addTableColumn('events', __('Events included'), true, function ($column, $postId) {
            $events = get_post_meta($postId, 'events_included', true);
            if ($events) {
                $end = end($events);
                foreach ($events as $key => $value) {
                    echo('<a href="'.get_edit_post_link($value).'">'.get_the_title($value).'</a>');
                    if ($value != $end) {
                        echo ", ";
                    }
                }
            }
        }
        );
        $this->addTableColumn('date', __('Date'));
    }
}
