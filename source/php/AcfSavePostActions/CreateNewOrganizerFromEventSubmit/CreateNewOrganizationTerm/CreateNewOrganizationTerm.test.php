<?php

namespace EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\CreateNewOrganizationTerm;

use AcfService\Contracts\UpdateField;
use EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData\OrganizerData;
use PHPUnit\Framework\TestCase;
use WP_Error;
use WpService\Contracts\WpInsertTerm;

/**
 * @covers \EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\CreateNewOrganizationTerm\CreateNewOrganizationTerm
 */
class CreateNewOrganizationTermTest extends TestCase
{
    /**
     * @testdox class can be instantiated
     */
    public function testClassCanBeInstantiated(): void
    {
        $wpService = new class implements WpInsertTerm {
            public function wpInsertTerm(string $term, string $taxonomy, array|string $args = []): array|WP_Error
            {
                return [];
            }
        };

        $this->assertInstanceOf(
            CreateNewOrganizationTerm::class,
            new CreateNewOrganizationTerm('organizer', $wpService, $this->getAcfService())
        );
    }

    /**
     * @testdox inserts a new term with name from OrganizationData
     */
    public function testInsertsNewTermWithNameFromOrganizationData(): void
    {
        $wpService = new class implements WpInsertTerm {
            public $calls = [];
            public function wpInsertTerm(string $term, string $taxonomy, array|string $args = []): array|WP_Error
            {
                $this->calls[] = [$term, $taxonomy, $args];
                return ['term_id' => 123];
            }
        };

        $organization = new OrganizerData(name: 'New Organization', email:'', contact:'', telephone:'', address:'', url:'');
        $instance     = new CreateNewOrganizationTerm('organizer', $wpService, $this->getAcfService());
        $instance->createTerm($organization);

        $this->assertSame([ [$organization->getName(), 'organizer', []] ], $wpService->calls);
    }

    /**
     * @testdox sets acf fields on created term
     */
    public function testSetsAcfFieldsOnCreatedTerm(): void
    {
        $wpService = new class implements WpInsertTerm {
            public $calls = [];
            public function wpInsertTerm(string $term, string $taxonomy, array|string $args = []): array|WP_Error
            {
                $this->calls[] = [$term, $taxonomy, $args];
                return ['term_id' => 123];
            }
        };

        $acfService   = $this->getAcfService();
        $organization = new OrganizerData(name: 'Name', email:'Email', contact:'Contact', telephone:'Telephone', address:'Address', url:'Url');
        $instance     = new CreateNewOrganizationTerm('organizer', $wpService, $acfService);
        $instance->createTerm($organization);

        $this->assertContains(['email', 'Email', 'organizer_123'], $acfService->calls);
        $this->assertContains(['contact', 'Contact', 'organizer_123'], $acfService->calls);
        $this->assertContains(['telephone', 'Telephone', 'organizer_123'], $acfService->calls);
        $this->assertContains(['address', 'Address', 'organizer_123'], $acfService->calls);
        $this->assertContains(['url', 'Url', 'organizer_123'], $acfService->calls);
    }

    /**
     * @testdox returns id of exiting term if it already exists
     */
    public function testReturnsIdOfExistingTermIfItAlreadyExists(): void
    {
        $wpService = new class implements WpInsertTerm {
            public function wpInsertTerm(string $term, string $taxonomy, array|string $args = []): array|WP_Error
            {
                return new class extends \WP_Error
                {
                    public function get_error_code() // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
                    {
                        return 'term_exists';
                    }

                    public function get_error_data($code = '') // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
                    {
                        return 321;
                    }
                };
            }
        };

        $organization = new OrganizerData(name: 'Name', email:'Email', contact:'Contact', telephone:'Telephone', address:'Address', url:'Url');
        $instance     = new CreateNewOrganizationTerm('organizer', $wpService, $this->getAcfService());
        $this->assertSame(321, $instance->createTerm($organization));
    }

    /**
     * @testdox throws RuntimeException if term creation fails
     */
    public function testThrowsRuntimeExceptionIfTermCreationFails(): void
    {
        $wpService = new class implements WpInsertTerm {
            public function wpInsertTerm(string $term, string $taxonomy, array|string $args = []): array|WP_Error
            {
                return new class extends \WP_Error
                {
                    public function get_error_code() // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
                    {
                        return 'unhandled_error';
                    }
                };
            }
        };

        $acfService = new class implements UpdateField {
            public function updateField(string $selector, mixed $value, mixed $postId = false): bool
            {
                return true;
            }
        };

        $organization = new OrganizerData(name: 'Name', email:'Email', contact:'Contact', telephone:'Telephone', address:'Address', url:'Url');
        $instance     = new CreateNewOrganizationTerm('organizer', $wpService, $acfService);

        try {
            $instance->createTerm($organization);
        } catch (\RuntimeException $e) {
            $this->assertTrue(true);
        }
    }


    private function getAcfService(): UpdateField
    {
        return new class implements UpdateField {
            public $calls = [];
            public function updateField(string $selector, mixed $value, mixed $postId = false): bool
            {
                $this->calls[] = [$selector, $value, $postId];
                return true;
            }
        };
    }
}
