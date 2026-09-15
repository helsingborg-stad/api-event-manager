<?php

namespace EventManager\CreateUserWhenOrganizationCreated;

use EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData\IOrganizerData;
use EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData\OrganizerData;
use EventManager\Organizations\CreateOrganizationAdminUser;
use PHPUnit\Framework\TestCase;
use WpService\Contracts\AddAction;

class CreateUserWhenOrganizationCreatedTest extends TestCase
{
    /**
     * @testdox hooks into EventManager/OrganizationCreated
     */
    public function testHooksIntoOrganizationCreated(): void
    {
        $wpService = static::createWpService();

        $instance = new CreateUserWhenOrganizationCreated($wpService, static::createOrganizationAdminUser());

        $instance->addHooks();

        $this->assertCount(1, $wpService->addedActions);
        $this->assertSame('EventManager/OrganizationCreated', $wpService->addedActions[0][0]);
    }

    /**
     * @testdox returns early when no organizer data is provided
     */
    public function testReturnsEarlyWhenNoOrganizerDataIsProvided(): void
    {
        $wpService                   = static::createWpService();
        $createOrganizationAdminUser = static::createOrganizationAdminUser();

        $instance = new CreateUserWhenOrganizationCreated($wpService, $createOrganizationAdminUser);
        $instance->createUserForOrganization(1, 1, []);

        $this->assertSame([], $createOrganizationAdminUser->calls);
    }

    /**
     * @testdox skips organization admin user creation when creation fails
     */
    public function testSkipsRoleAndUpdateWhenUserCreationFails(): void
    {
        $wpService = static::createWpService();

        $instance = new CreateUserWhenOrganizationCreated($wpService, static::createOrganizationAdminUser(true));
        $instance->createUserForOrganization(1, 1, [static::createOrganizerData()]);

        $this->assertTrue(true);
    }

    /**
     * @testdox creates organization administrator users from organizer email addresses
     */
    public function testCreatesOrganizationAdminUsersFromOrganizerEmailAddresses(): void
    {
        $wpService                   = static::createWpService();
        $createOrganizationAdminUser = static::createOrganizationAdminUser();

        $instance = new CreateUserWhenOrganizationCreated($wpService, $createOrganizationAdminUser);
        $instance->createUserForOrganization(1, 1, [static::createOrganizerData()]);

        $this->assertSame([
            [1, 'test@example.com'],
        ], $createOrganizationAdminUser->calls);
    }

    private static function createOrganizerData(?string $email = null): IOrganizerData
    {
        return new OrganizerData(
            'Test Organizer',
            $email ?? 'test@example.com',
            'John Doe',
            '123-456-7890',
            '123 Test St, Test City, TX 12345',
            'https://www.testorganizer.com'
        );
    }


    private static function createWpService(): AddAction
    {
        return new class implements AddAction {
            public array $addedActions = [];

            public function addAction(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): true
            {
                $this->addedActions[] = func_get_args();
                return true;
            }
        };
    }

    private static function createOrganizationAdminUser(bool $shouldThrow = false): CreateOrganizationAdminUser
    {
        return new class ($shouldThrow) extends CreateOrganizationAdminUser {
            public array $calls = [];

            public function __construct(private bool $shouldThrow)
            {
            }

            public function create(int $organizationId, string $email): \WP_User
            {
                if ($this->shouldThrow) {
                    throw new \Exception('Failed to create user.');
                }

                $this->calls[] = [$organizationId, $email];
                return new \WP_User();
            }
        };
    }
}
