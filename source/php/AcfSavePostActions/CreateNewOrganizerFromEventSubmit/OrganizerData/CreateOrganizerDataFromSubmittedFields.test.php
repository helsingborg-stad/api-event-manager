<?php

namespace EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData;

use PHPUnit\Framework\TestCase;

class CreateOrganizerDataFromSubmittedFieldsTest extends TestCase
{
    /**
     * @testdox valid fields create organizer data
     */
    public function testValidFieldsCreateOrganizerData(): void
    {
        $instance      = new CreateOrganizerDataFromSubmittedFields();
        $organizerData = $instance->tryCreate($this->getValidFields());

        $this->assertInstanceOf(OrganizerData::class, $organizerData);
    }

    /**
     * @testdox invalid fields do not create organizer data
     * @dataProvider invalidFieldsProvider
     */
    public function testInvalidFieldsDoNotCreateOrganizerData(array $fields): void
    {
        $instance      = new CreateOrganizerDataFromSubmittedFields();
        $organizerData = $instance->tryCreate($fields);

        $this->assertNull($organizerData);
    }

    public function invalidFieldsProvider(): array
    {
        return [
            'invalid submitNewOrganization' => [array_merge($this->getValidFields(), ['submitNewOrganization' => false])],
            'invalid organizerName'         => [array_merge($this->getValidFields(), ['organizerName' => ''])],
            'invalid organizerEmail'        => [array_merge($this->getValidFields(), ['organizerEmail' => ''])],
            'invalid organizerContact'      => [array_merge($this->getValidFields(), ['organizerContact' => ''])],
            'invalid organizerTelephone'    => [array_merge($this->getValidFields(), ['organizerTelephone' => ''])],
            'invalid organizerAddress'      => [array_merge($this->getValidFields(), ['organizerAddress' => ''])],
            'invalid organizerUrl'          => [array_merge($this->getValidFields(), ['organizerUrl' => ''])],
        ];
    }

    private function getValidFields(): array
    {
        return [
            'submitNewOrganization' => true,
            'organizerName'         => 'Test Organizer',
            'organizerEmail'        => 'test@example.com',
            'organizerContact'      => '123456789',
            'organizerTelephone'    => '123-456-7890',
            'organizerAddress'      => '123 Test St, Test City, TX 12345',
            'organizerUrl'          => 'https://example.com'
        ];
    }
}
