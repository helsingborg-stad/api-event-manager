<?php

namespace EventManager\CreateUserWhenOrganizationCreated;

use AcfService\Contracts\UpdateField;
use EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData\IOrganizerData;
use EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData\OrganizerData;
use EventManager\Helper\CreateUserFromEmailInterface;
use PHPUnit\Framework\TestCase;
use WP_Error;
use WP_User;
use WpService\Contracts\AddAction;
use WpService\Contracts\DoAction;
use WpService\Contracts\WpUpdateUser;

class CreateUserWhenOrganizationCreatedTest extends TestCase
{
    /**
     * @testdox hooks into EventManager/OrganizationCreated
     */
    public function testHooksIntoOrganizationCreated(): void
    {
        $wpService  = static::createWpService();
        $acfService = static::createAcfService();

        $instance = new CreateUserWhenOrganizationCreated($wpService, $acfService, static::createCreateUserFromEmail());

        $instance->addHooks();

        $this->assertCount(1, $wpService->addedActions);
        $this->assertSame('EventManager/OrganizationCreated', $wpService->addedActions[0][0]);
    }

    /**
     * @testdox returns early when no organizer data is provided
     */
    public function testReturnsEarlyWhenNoOrganizerDataIsProvided(): void
    {
        $wpService  = static::createWpService();
        $acfService = static::createAcfService();

        $instance = new CreateUserWhenOrganizationCreated($wpService, $acfService, static::createCreateUserFromEmail());
        $instance->createUserForOrganization(1, 1, []);

        $this->assertCount(0, $wpService->updatedUsers);
        $this->assertCount(0, $acfService->calls);
    }

    /**
     * @testdox skips setting role and updating user when wpCreateUser fails
     */
    public function testSkipsRoleAndUpdateWhenUserCreationFails(): void
    {
        $wpService  = static::createWpService();
        $acfService = static::createAcfService();

        $instance = new CreateUserWhenOrganizationCreated($wpService, $acfService, static::createCreateUserFromEmail());
        $instance->createUserForOrganization(1, 1, [static::createOrganizerData()]);

        $this->assertCount(0, $wpService->updatedUsers);
        $this->assertCount(0, $acfService->calls);
    }

    /**
     * @testdox sets organization_admin role for a newly created user
     */
    public function testSetsOrganizationAdminRoleForCreatedUser(): void
    {
        $wpService  = static::createWpService();
        $acfService = static::createAcfService();
        $wpUser     = new class extends WP_User {
            public ?string $roleSet = null;

            public function set_role($role): void // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
            {
                $this->roleSet = $role;
            }
        };
        $wpUser->ID = 456;

        $instance = new CreateUserWhenOrganizationCreated($wpService, $acfService, static::createCreateUserFromEmail($wpUser));
        $instance->createUserForOrganization(1, 1, [static::createOrganizerData()]);

        $this->assertSame('organization_administrator', $wpUser->roleSet);
        $this->assertCount(1, $wpService->updatedUsers);
        $this->assertSame($wpUser, $wpService->updatedUsers[0]);
        $this->assertSame([
            ['organizations', [1], 'user_456'],
        ], $acfService->calls);
    }

    private static function createOrganizerData(?string $email = null): IOrganizerData
    {
        return new OrganizerData(
            'Test Organizer',
            $email ?? 'test' . rand(1000, 9999) . '@example.com',
            'John Doe',
            '123-456-7890',
            '123 Test St, Test City, TX 12345',
            'https://www.testorganizer.com'
        );
    }


    private static function createWpService(): AddAction&WpUpdateUser&DoAction
    {
        return new class implements AddAction, WpUpdateUser, DoAction  {
            public array $addedActions           = [];
            public array $createdUsers           = [];
            public array $updatedUsers           = [];
            public WP_User|false $userdataResult = false;

            public function __construct()
            {
                $this->userdataResult = new class (123) extends WP_User {
                    public function set_role($role): void // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
                    {
                    }
                };
            }

            public function addAction(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): true
            {
                $this->addedActions[] = func_get_args();
                return true;
            }

            public function wpUpdateUser(array|object $userdata): int|WP_Error
            {
                $this->updatedUsers[] = $userdata;
                return 123;
            }

            public function doAction(string $hookName, mixed ...$arg): void
            {
            }
        };
    }

    private static function createAcfService(): UpdateField
    {
        return new class implements UpdateField {
            public array $calls = [];

            public function updateField(string $selector, mixed $value, mixed $postId = false): bool
            {
                $this->calls[] = [$selector, $value, $postId];
                return true;
            }
        };
    }

    private static function createCreateUserFromEmail(?\WP_User $wpUser = null): CreateUserFromEmailInterface
    {
        return new class ($wpUser) implements CreateUserFromEmailInterface {
            public function __construct(private ?\WP_User $wpUser)
            {
                $this->wpUser = $wpUser;
            }

            public function createUserFromEmail(string $email): WP_User
            {
                if ($this->wpUser) {
                    return $this->wpUser;
                }

                throw new \Exception('User creation failed for email: ' . $email);
            }
        };
    }
}
