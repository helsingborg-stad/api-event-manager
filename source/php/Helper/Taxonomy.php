<?php

namespace EventManager\Helper;

use WpService\WpService;

abstract class Taxonomy implements Hookable
{
    abstract public function getName(): string;
    abstract public function getObjectType(): string;
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

        $this->wp->registerTaxonomy($this->getName(), $this->getObjectType(), $args);
    }

    private function getLabels()
    {
        $prepareLabel = function (string $format, string $label) {
            return sprintf(__($format, 'api-event-manager'), strtolower($label));
        };

        $labelSingular = $this->getLabelSingular();
        $labelPlural   = $this->getLabelPlural();

        return[
            'name'                       => $labelPlural,
            'singular_name'              => $labelSingular,
            'search_items'               => $prepareLabel('Search %s', $labelPlural),
            'popular_items'              => $prepareLabel('Popular %s', $labelPlural),
            'all_items'                  => $prepareLabel('All %s', $labelPlural),
            'parent_item'                => $prepareLabel('Parent %s', $labelSingular),
            'parent_item_colon'          => $prepareLabel('Parent %s:', $labelSingular),
            'edit_item'                  => $prepareLabel('Edit %s', $labelSingular),
            'update_item'                => $prepareLabel('Update %s', $labelSingular),
            'add_new_item'               => $prepareLabel('Add new %s', $labelSingular),
            'new_item_name'              => $prepareLabel('New %s name', $labelSingular),
            'separate_items_with_commas' => $prepareLabel('Separate %s with commas', $labelPlural),
            'add_or_remove_items'        => $prepareLabel('Add or remove %s', $labelPlural),
            'choose_from_most_used'      => $prepareLabel('Choose from most used %s', $labelPlural),
            'not_found'                  => $prepareLabel('No %s found', $labelPlural),
            'menu_name'                  => $labelPlural,
        ];
    }
}
