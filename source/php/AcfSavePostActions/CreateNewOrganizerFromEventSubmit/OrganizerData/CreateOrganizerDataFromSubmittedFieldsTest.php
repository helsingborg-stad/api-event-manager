<?php

namespace EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData;

use PHPUnit\Framework\TestCase;
use WpService\Contracts\EscUrlRaw;
use WpService\Contracts\SanitizeEmail;
use WpService\Contracts\SanitizeTextField;

class CreateOrganizerDataFromSubmittedFieldsTest extends TestCase
{
    /**
     * @testdox valid fields create organizer data
     */
    public function testValidFieldsCreateOrganizerData(): void
    {
        $instance      = new CreateOrganizerDataFromSubmittedFields($this->createWpService());
        $organizerData = $instance->tryCreate($this->getValidFields());

        $this->assertInstanceOf(OrganizerData::class, $organizerData[0]);
    }

    public function testEmptyOrganizersDataReturnsNull(): void
    {
        $instance      = new CreateOrganizerDataFromSubmittedFields($this->createWpService());
        $organizerData = $instance->tryCreate($this->emptyOrganizersData());

        $this->assertNull($organizerData);
    }

    /**
     * @testdox invalid fields do not create organizer data
     * @dataProvider invalidFieldsProvider
     */
    public function testInvalidFieldsDoNotCreateOrganizerData(array $fields): void
    {
        $instance      = new CreateOrganizerDataFromSubmittedFields($this->createWpService());
        $organizerData = $instance->tryCreate($fields);

        $this->assertNull($organizerData);
    }

    public function emptyOrganizersData(): array
    {
        return [
            'submitNewOrganization' => true,
            'newOrganizers'         => []
        ];
    }

    public function invalidFieldsProvider(): array
    {
        return [
            'invalid submitNewOrganization' => [array_merge($this->getValidFields(), ['submitNewOrganization' => false])],
            'invalid organizerName'         => [array_replace_recursive($this->getValidFields(), [
                'newOrganizers' => [
                    ['organizerName' => '']
                ]
            ])],
            'invalid organizerEmail'        => [array_replace_recursive($this->getValidFields(), [
                'newOrganizers' => [
                    ['organizerEmail' => '']
                ]
            ])],
            'invalid organizerContact'      => [array_replace_recursive($this->getValidFields(), [
                'newOrganizers' => [
                    ['organizerContact' => '']
                ]
            ])],
            'invalid organizerTelephone'    => [array_replace_recursive($this->getValidFields(), [
                'newOrganizers' => [
                    ['organizerTelephone' => '']
                ]
            ])],
            'invalid organizerAddress'      => [array_replace_recursive($this->getValidFields(), [
                'newOrganizers' => [
                    ['organizerAddress' => '']
                ]
            ])],
            'invalid organizerUrl'          => [array_replace_recursive($this->getValidFields(), [
                'newOrganizers' => [
                    ['organizerUrl' => '']
                ]
            ])],
        ];
    }

    private function getValidFields(): array
    {
        return [
            'submitNewOrganization' => true,
            'newOrganizers'         => [
                [
                    'organizerName'      => 'Test Organizer',
                    'organizerEmail'     => 'test@example.com',
                    'organizerContact'   => '123456789',
                    'organizerTelephone' => '123-456-7890',
                    'organizerAddress'   => '123 Test St, Test City, TX 12345',
                    'organizerUrl'       => 'https://example.com'
                ]
            ]
        ];
    }

    private function createWpService(): SanitizeTextField|SanitizeEmail|EscUrlRaw
    {
        return new class implements SanitizeTextField, SanitizeEmail, EscUrlRaw {
            public function sanitizeTextField(string $text): string
            {
                return trim($text);
            }

            public function sanitizeEmail(string $email): string
            {
                return filter_var($email, FILTER_SANITIZE_EMAIL);
            }

            public function escUrlRaw(string $url, ?array $protocols = null): string
            {
                return filter_var($url, FILTER_SANITIZE_URL);
            }
        };
    }
}
