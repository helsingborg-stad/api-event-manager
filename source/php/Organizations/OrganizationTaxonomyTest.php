<?php

namespace EventManager\Organizations;

use WpService\Implementations\FakeWpService;

class OrganizationTaxonomyTest extends \PHPUnit\Framework\TestCase
{
    public function testGetName(): void
    {
        $taxonomy             = 'organization';
        $organizationTaxonomy = new OrganizationTaxonomy(new FakeWpService(), $taxonomy);

        $this->assertEquals($taxonomy, $organizationTaxonomy->getName());
    }
}
