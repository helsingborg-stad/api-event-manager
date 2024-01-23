<?php

namespace EventManager\PostTypes;

use EventManager\Helper\PostType;
use EventManager\PostTypes\Icons\Icon;

class Event extends PostType
{
    public function getName(): string
    {
        return 'event';
    }

    public function getArgs(): array
    {
        return [
            'show_in_rest'          => true,
            'public'                => true,
            'hierarchical'          => true,
            'menu_icon'             => (new Icon('Event'))->getIcon(),
            'rest_base'             => 'events',
            'rest_controller_class' => \EventManager\RestControllers\EventController::class,
            'supports'              => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes' ],
            'taxonomies'            => [ 'audience-type' ],
        ];
    }

    public function getLabelSingular(): string
    {
        return 'Event';
    }

    public function getLabelPlural(): string
    {
        return 'Events';
    }
}
