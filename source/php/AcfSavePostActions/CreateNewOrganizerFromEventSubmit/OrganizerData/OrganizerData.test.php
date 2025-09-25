<?php

namespace EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData;

use PHPUnit\Framework\TestCase;

class OrganizerDataTest extends TestCase
{
    /**
     * @testdox provided data is returned correctly
     */
    public function testProvidedDataIsReturnedCorrectly(): void
    {
        $data = new OrganizerData(
            name: 'Test Organizer',
            email: 'test@example.com',
            contact: '123456789',
            url: 'https://example.com',
            address: '123 Test St, Test City, TX 12345',
            telephone: '123-456-7890'
        );

        $this->assertSame('Test Organizer', $data->getName());
        $this->assertSame('test@example.com', $data->getEmail());
        $this->assertSame('123456789', $data->getContact());
        $this->assertSame('https://example.com', $data->getUrl());
        $this->assertSame('123 Test St, Test City, TX 12345', $data->getAddress());
        $this->assertSame('123-456-7890', $data->getTelephone());
    }
}
