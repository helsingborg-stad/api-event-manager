<?php

namespace EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit;

use AcfService\Contracts\GetFields;
use AcfService\Implementations\FakeAcfService;
use EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\ClearFieldsFromPost\IClearFieldsFromPost;
use EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\CreateNewOrganizationTerm\ICreateNewOrganizationTerm;
use EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData\ICreateOrganizerDataFromSubmittedFields;
use EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData\IOrganizerData;
use EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData\OrganizerData;
use EventManager\AcfSavePostActions\IAcfSavePostAction;
use PHPUnit\Framework\TestCase;
use WP_Error;
use WpService\Contracts\WpSetObjectTerms;
use WpService\Implementations\FakeWpService;
use WpService\WpService;

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
        $this->assertCount(1, $wpService->methodCalls['wpSetObjectTerms']);
        $this->assertContains([123, 456, 'organizer'], $wpService->methodCalls['wpSetObjectTerms']);
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

        $this->assertArrayNotHasKey('wpSetObjectTerms', $wpService->methodCalls);
    }

    private function getWpService(): WpService
    {
        return new FakeWpService([
            'wpSetObjectTerms' => []
        ]);
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
        return new FakeAcfService([
            'getFields' => []
        ]);
    }
}
