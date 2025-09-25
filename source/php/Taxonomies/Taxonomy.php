<?php

namespace EventManager\Taxonomies;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\__;
use WpService\Contracts\AddAction;
use WpService\Contracts\RegisterTaxonomy;

abstract class Taxonomy implements Hookable
{
    abstract public function getName(): string;
    abstract public function getObjectType(): string;
    abstract public function getArgs(): array;
    abstract public function getLabelSingular(): string;
    abstract public function getLabelPlural(): string;

    public function __construct(protected AddAction&RegisterTaxonomy&__ $wpService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('init', [$this, 'register']);
        $this->wpService->addAction('init', [$this, 'seed']);
    }

    public function register(): void
    {
        $args = array_merge($this->getArgs(), [
            'labels' => $this->getLabels(),
        ]);

        $this->wpService->registerTaxonomy($this->getName(), $this->getObjectType(), $args);
    }

    private function getLabels()
    {
        $labelSingular = $this->getLabelSingular();
        $labelPlural   = $this->getLabelPlural();

        return[
            'name'                       => $labelPlural,
            'singular_name'              => $labelSingular,
            'search_items'               => sprintf($this->wpService->__('Search %s', 'api-event-manager'), $labelPlural),
            'popular_items'              => sprintf($this->wpService->__('Popular %s', 'api-event-manager'), $labelPlural),
            'all_items'                  => sprintf($this->wpService->__('All %s', 'api-event-manager'), $labelPlural),
            'parent_item'                => sprintf($this->wpService->__('Parent %s', 'api-event-manager'), $labelSingular),
            'parent_item_colon'          => sprintf($this->wpService->__('Parent %s:', 'api-event-manager'), $labelSingular),
            'edit_item'                  => sprintf($this->wpService->__('Edit %s', 'api-event-manager'), $labelSingular),
            'update_item'                => sprintf($this->wpService->__('Update %s', 'api-event-manager'), $labelSingular),
            'add_new_item'               => sprintf($this->wpService->__('Add new %s', 'api-event-manager'), $labelSingular),
            'new_item_name'              => sprintf($this->wpService->__('New %s name', 'api-event-manager'), $labelSingular),
            'separate_items_with_commas' => sprintf($this->wpService->__('Separate %s with commas', 'api-event-manager'), $labelPlural),
            'add_or_remove_items'        => sprintf($this->wpService->__('Add or remove %s', 'api-event-manager'), $labelPlural),
            'choose_from_most_used'      => sprintf($this->wpService->__('Choose from most used %s', 'api-event-manager'), $labelPlural),
            'not_found'                  => sprintf($this->wpService->__('No %s found', 'api-event-manager'), $labelPlural),
            'menu_name'                  => $labelPlural,
        ];
    }

    public function seed(): void
    {
        foreach ($this->getSeed() as $term) {
            if (!term_exists($term, $this->getName())) {
                wp_insert_term($term, $this->getName());
            }
        }
    }

    /**
     * Seed data for the taxonomy.
     *
     * @return array
     */
    public function getSeed(): array
    {
        return [];
    }
}
