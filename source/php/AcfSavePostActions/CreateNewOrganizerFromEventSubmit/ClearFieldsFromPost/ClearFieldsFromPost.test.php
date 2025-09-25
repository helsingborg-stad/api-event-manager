<?php

namespace EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\ClearFieldsFromPost;

use AcfService\Contracts\DeleteField;
use PHPUnit\Framework\TestCase;

class ClearFieldsFromPostTest extends TestCase
{
    /**
     * @testdox class can be instantiated
     */
    public function testClassCanBeInstantiated(): void
    {
        $acfService = new class implements DeleteField {
            public function deleteField($selector, $postId = false): bool
            {
                return true;
            }
        };

        $this->assertInstanceOf(
            ClearFieldsFromPost::class,
            new ClearFieldsFromPost($acfService)
        );
    }

    /**
     * @testdox clears fields from post
     */
    public function testClearsFieldsFromPost(): void
    {
        $acfService = new class implements DeleteField {
            public array $calls;
            public function deleteField($selector, $postId = false): bool
            {
                $this->calls[] = [$selector, $postId];
                return true;
            }
        };

        $instance = new ClearFieldsFromPost($acfService);
        $instance->clearFields(123);

        $this->assertCount(7, $acfService->calls);
        $this->assertContains(['submitNewOrganization', 123], $acfService->calls);
        $this->assertContains(['organizerName', 123], $acfService->calls);
        $this->assertContains(['organizerEmail', 123], $acfService->calls);
        $this->assertContains(['organizerContact', 123], $acfService->calls);
        $this->assertContains(['organizerUrl', 123], $acfService->calls);
        $this->assertContains(['organizerAddress', 123], $acfService->calls);
        $this->assertContains(['organizerTelephone', 123], $acfService->calls);
    }
}
