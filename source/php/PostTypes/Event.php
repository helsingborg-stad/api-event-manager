<?php

namespace EventManager\PostTypes;

use EventManager\Helper\PostType;

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
            'icon'                  => 'dashicons-calendar-alt',
            'rest_base'             => 'events',
            'rest_controller_class' => \EventManager\RestControllers\EventController::class,
            'supports'              => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', ]
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
