<?php

namespace EventManager\Tests\Helper;

use EventManager\Taxonomies\Taxonomy;
use WpService\WpService;
use PHPUnit\Framework\TestCase;

class TaxonomyTest extends TestCase
{
    /**
     * @testdox taxonomy is registered with supplied name and object type.
     */
    public function testTaxonomyIsRegisteredWithSuppliedNameAndObjectType()
    {
        $wpService = $this->createMock(WpService::class);
        $taxonomy  = $this->getTaxonomyInstance($wpService);

        $wpService->expects($this->once())
            ->method('registerTaxonomy')
            ->with('test_taxonomy', 'test_object_type');

        $taxonomy->register();
    }

    /**
     * @testdox taxonomy labels are automatically set up and passed to the registerTaxonomy method.
     */
    public function testTaxonomyLabelsAreAutomaticallySetUpAndPassedToTheRegisterTaxonomyMethod()
    {
        $wpService = $this->createMock(WpService::class);
        $taxonomy  = $this->getTaxonomyInstance($wpService);

        $wpService->expects($this->once())
            ->method('registerTaxonomy')
            ->with('test_taxonomy', 'test_object_type', [ 'labels' => $this->getLabelsSnapshot() ]);

        $taxonomy->register();
    }

    private function getTaxonomyInstance(WPService $wpService)
    {
        return new class ($wpService) extends Taxonomy {
            public function getName(): string
            {
                return 'test_taxonomy';
            }

            public function getObjectType(): string
            {
                return 'test_object_type';
            }

            public function getArgs(): array
            {
                return [];
            }

            public function getLabelSingular(): string
            {
                return 'Term';
            }

            public function getLabelPlural(): string
            {
                return 'Terms';
            }
        };
    }

    private function getLabelsSnapshot(): array
    {
        return  array
        (
            'name'                       => 'Terms',
            'singular_name'              => 'Term',
            'search_items'               => 'Search terms',
            'popular_items'              => 'Popular terms',
            'all_items'                  => 'All terms',
            'parent_item'                => 'Parent term',
            'parent_item_colon'          => 'Parent term:',
            'edit_item'                  => 'Edit term',
            'update_item'                => 'Update term',
            'add_new_item'               => 'Add new term',
            'new_item_name'              => 'New term name',
            'separate_items_with_commas' => 'Separate terms with commas',
            'add_or_remove_items'        => 'Add or remove terms',
            'choose_from_most_used'      => 'Choose from most used terms',
            'not_found'                  => 'No terms found',
            'menu_name'                  => 'Terms'
        );
    }
}
