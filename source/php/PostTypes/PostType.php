<?php

namespace EventManager\PostTypes;

use EventManager\HooksRegistrar\Hookable;
use WpService\WpService;

abstract class PostType implements Hookable
{
    abstract public function getName(): string;
    abstract public function getArgs(): array;
    abstract public function getLabelSingular(): string;
    abstract public function getLabelPlural(): string;
    private WPService $wp;

    public function __construct(WPService $wpService)
    {
        $this->wp = $wpService;
    }

    public function addHooks(): void
    {
        $this->wp->addAction('init', [$this, 'register']);
    }

    public function register(): void
    {
        $args = array_merge($this->getArgs(), [
            'labels' => $this->getLabels(),
        ]);

        $this->wp->registerPostType($this->getName(), $args);
    }

    private function getLabels()
    {
        $prepareLabel = function (string $format, string $label) {
            return sprintf(__($format, 'api-event-manager'), strtolower($label));
        };

        $labelSingular = $this->getLabelSingular();
        $labelPlural   = $this->getLabelPlural();

        return[
            'name'               => $labelPlural,
            'singular_name'      => $labelSingular,
            'add_new'            => $prepareLabel('Add new %s', $labelSingular),
            'add_new_item'       => $prepareLabel('Add new %s', $labelSingular),
            'edit_item'          => $prepareLabel('Edit %s', $labelSingular),
            'new_item'           => $prepareLabel('New %s', $labelSingular),
            'view_item'          => $prepareLabel('View %s', $labelSingular),
            'search_items'       => $prepareLabel('Search %s', $labelPlural),
            'not_found'          => $prepareLabel('No %s found', $labelPlural),
            'not_found_in_trash' => $prepareLabel('No %s found in trash', $labelPlural),
            'parent_item_colon'  => $prepareLabel('Parent %s:', $labelSingular),
            'menu_name'          => $labelPlural,
        ];
    }
}
