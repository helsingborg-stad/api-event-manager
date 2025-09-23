<?php

namespace EventManager\PostTypes;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\__;
use WpService\Contracts\AddAction;
use WpService\Contracts\RegisterPostType;

abstract class PostType implements Hookable
{
    abstract public function getName(): string;
    abstract public function getArgs(): array;
    abstract public function getLabelSingular(): string;
    abstract public function getLabelPlural(): string;

    public function __construct(protected AddAction&RegisterPostType&__ $wpService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('init', [$this, 'register']);
    }

    public function register(): void
    {
        $args = array_merge($this->getArgs(), [
            'labels' => $this->getLabels(),
        ]);

        $this->wpService->registerPostType($this->getName(), $args);
    }

    private function getLabels()
    {
        $labelSingular = $this->getLabelSingular();
        $labelPlural   = $this->getLabelPlural();

        return[
            'name'               => $labelPlural,
            'singular_name'      => $labelSingular,
            'add_new'            => $this->wpService->__('Add New %s', 'api-event-manager'),
            'add_new_item'       => sprintf($this->wpService->__('Add new %s', 'api-event-manager'), strtolower($labelSingular)),
            'edit_item'          => sprintf($this->wpService->__('Edit %s', 'api-event-manager'), strtolower($labelSingular)),
            'new_item'           => sprintf($this->wpService->__('New %s', 'api-event-manager'), strtolower($labelSingular)),
            'view_item'          => sprintf($this->wpService->__('View %s', 'api-event-manager'), strtolower($labelSingular)),
            'search_items'       => sprintf($this->wpService->__('Search %s', 'api-event-manager'), strtolower($labelPlural)),
            'not_found'          => sprintf($this->wpService->__('No %s found', 'api-event-manager'), strtolower($labelPlural)),
            'not_found_in_trash' => sprintf($this->wpService->__('No %s found in trash', 'api-event-manager'), strtolower($labelPlural)),
            'parent_item_colon'  => sprintf($this->wpService->__('Parent %s:', 'api-event-manager'), strtolower($labelSingular)),
            'menu_name'          => $labelPlural,
        ];
    }
}
