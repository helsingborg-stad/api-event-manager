<?php

namespace HbgEventImporter\PostTypes;

class Recommendations extends \HbgEventImporter\Entity\CustomPostType
{
    public static $postTypeSlug = 'recommendation';

    public function __construct()
    {
        parent::__construct(
            __('Recommendations', 'event-manager'),
            __('Recommendation', 'event-manager'),
            self::$postTypeSlug,
            array(
                'description'          => 'Guided tours with beacon information',
                'menu_icon'            => 'dashicons-lightbulb',
                'public'               => true,
                'publicly_queriable'   => true,
                'show_ui'              => true,
                'show_in_nav_menus'    => true,
                'has_archive'          => true,
                'rewrite'              => array(
                    'slug'       => self::$postTypeSlug,
                    'with_front' => false
                ),
                'hierarchical'         => false,
                'exclude_from_search'  => true,
                'supports'             => array('title', 'revisions', 'editor'),
                'map_meta_cap'         => true,
                'capability_type'      => self::$postTypeSlug,
            )
        );
    }
}
