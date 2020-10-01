<?php

namespace HbgEventImporter\PostTypes;

class InteractiveGuides extends \HbgEventImporter\Entity\CustomPostType
{
    public function __construct()
    {
        parent::__construct(
            __('Interactive guides', 'event-manager'),
            __('Interactive guide', 'event-manager'),
            'interactive_guide',
            array(
                'description'          => 'Guided tours with beacon information',
                'menu_icon'            => 'dashicons-format-chat',
                'public'               => true,
                'publicly_queriable'   => true,
                'show_ui'              => true,
                'show_in_nav_menus'    => true,
                'has_archive'          => true,
                'rewrite'              => array(
                    'slug'       => 'interactive_guide',
                    'with_front' => false
                ),
                'hierarchical'         => false,
                'exclude_from_search'  => true,
                'supports'             => array('title', 'revisions', 'thumbnail'),
                'map_meta_cap'         => true,
                'capability_type'      => 'interactive_guide',
                'taxonomies'           =>  array('guidegroup'),
            )
        );
    }
}
