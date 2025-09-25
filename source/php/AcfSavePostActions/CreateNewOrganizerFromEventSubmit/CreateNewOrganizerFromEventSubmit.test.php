<?php

namespace EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit;

use AcfService\Contracts\GetFields;
use EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\ClearFieldsFromPost\IClearFieldsFromPost;
use EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\CreateNewOrganizationTerm\ICreateNewOrganizationTerm;
use EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData\ICreateOrganizerDataFromSubmittedFields;
use EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData\IOrganizerData;
use EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData\OrganizerData;
use EventManager\AcfSavePostActions\IAcfSavePostAction;
use PHPUnit\Framework\TestCase;
use WP_Error;
use WpService\Contracts\WpSetObjectTerms;

class CreateNewOrganizerFromEventSubmitTest extends TestCase
{
    /**
     * @testdox creates term and assigns it to post
     */
    public function testSavePostCreatesOrganizer(): void
    {
        $wpService                 = $this->getWpService();
        $acfService                = $this->getAcfsService();
        $clearFields               = $this->getClearFields();
        $createNewOrganizationTerm = new class implements ICreateNewOrganizationTerm {
            public function createTerm(IOrganizerData $organizerData): int
            {
                return 456;
            }
        };
        $organizerDataFactory      = new class implements ICreateOrganizerDataFromSubmittedFields {
            public function tryCreate(array $fields): ?IOrganizerData
            {
                return new OrganizerData(
                    name: 'Test Organizer',
                    email: 'test@example.com',
                    contact: '123-456-7890',
                    telephone: '123-456-7890',
                    address: '123 Test St, Test City, TX 12345',
                    url: 'https://www.testorganizer.com'
                );
            }
        };

        $instance = new CreateNewOrganizerFromEventSubmit(
            $wpService,
            $acfService,
            'organizer',
            $clearFields,
            $createNewOrganizationTerm,
            $organizerDataFactory
        );

        $instance->savePost(123);

        $this->assertCount(1, $wpService->calls);
        $this->assertContains([123, 456, 'organizer', false], $wpService->calls);
    }

    /**
     * @testdox does not assign term if organizer data is invalid
     */
    public function testSavePostDoesNotCreateOrganizerIfDataInvalid(): void
    {
        $wpService                 = $this->getWpService();
        $acfService                = $this->getAcfsService();
        $clearFields               = $this->getClearFields();
        $createNewOrganizationTerm = new class implements ICreateNewOrganizationTerm {
            public function createTerm(IOrganizerData $organizerData): int
            {
                return 456;
            }
        };
        $organizerDataFactory      = new class implements ICreateOrganizerDataFromSubmittedFields {
            public function tryCreate(array $fields): ?IOrganizerData
            {
                return null;
            }
        };

        $instance = new CreateNewOrganizerFromEventSubmit(
            $wpService,
            $acfService,
            'organizer',
            $clearFields,
            $createNewOrganizationTerm,
            $organizerDataFactory
        );

        $instance->savePost(123);

        $this->assertCount(0, $wpService->calls);
    }

    private function getWpService(): WpSetObjectTerms
    {
        return $wpService = new class implements WpSetObjectTerms {
            public array $calls = [];
            public function wpSetObjectTerms(int $objectId, string|int|array $terms, string $taxonomy, bool $append = false): array|WP_Error
            {
                $this->calls[] = [$objectId, $terms, $taxonomy, $append];
                return [];
            }
        };
    }

    private function getClearFields(): IClearFieldsFromPost
    {
        return new class implements IClearFieldsFromPost {
            public function clearFields(int|string $postId): void
            {
            }
        };
    }

    private function getAcfsService(): GetFields
    {
        return new class implements GetFields {
            public function getFields(mixed $postId = false, bool $formatValue = true, bool $escapeHtml = false): array|false
            {
                return [];
            }
        };
    }
}
