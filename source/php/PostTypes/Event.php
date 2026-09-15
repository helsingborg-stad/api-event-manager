<?php

namespace EventManager\PostTypes;

use EventManager\PostTypes\Icons\Icon;
use WpService\Contracts\AddAction;
use WpService\Contracts\RegisterPostType;
use WpService\Contracts\__;

class Event extends PostType
{
    public function __construct(AddAction&RegisterPostType&__ $wpService, private string $postType)
    {
        return parent::__construct($wpService);
    }

    public function getName(): string
    {
        return $this->postType;
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
            'supports'              => ['title', 'editor', 'revisions'],
            'capability_type'       => ['event', 'events'],
        ];
    }

    public function getLabelSingular(): string
    {
        return $this->wpService->__('Event', 'api-event-manager');
    }

    public function getLabelPlural(): string
    {
        return $this->wpService->__('Events', 'api-event-manager');
    }
}
