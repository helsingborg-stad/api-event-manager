<?php

namespace EventManager\Organizations;

use WpService\Implementations\FakeWpService;

class UserTableOrganizationColumnTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @testdox appends organization column to the user table
     */
    public function testAddOrganizationColumn(): void
    {
        $wpService                   = new FakeWpService(['__' => fn($text) => $text]);
        $userTableOrganizationColumn = new UserTableOrganizationColumn($wpService, 'organization_taxonomy');

        $columns = ['username' => 'Username', 'email' => 'Email'];
        $result  = $userTableOrganizationColumn->addOrganizationColumn($columns);

        static::assertArrayHasKey('organization', $result);
        static::assertEquals('Organization', $result['organization']);
    }
}
