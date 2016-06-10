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
    }


}
