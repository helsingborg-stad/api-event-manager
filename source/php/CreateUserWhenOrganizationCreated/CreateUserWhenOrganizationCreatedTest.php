<?php

namespace EventManager\CreateUserWhenOrganizationCreated;

use AcfService\Contracts\UpdateField;
use EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData\IOrganizerData;
use EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData\OrganizerData;
use PHPUnit\Framework\TestCase;
use WP_Error;
use WP_User;
use WpService\Contracts\AddAction;
use WpService\Contracts\DoAction;
use WpService\Contracts\GetUserBy;
use WpService\Contracts\GetUserdata;
use WpService\Contracts\WpCreateUser;
use WpService\Contracts\WpGeneratePassword;
use WpService\Contracts\WpNewUserNotification;
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

        $instance = new CreateUserWhenOrganizationCreated($wpService, $acfService);

        $instance->addHooks();

        $this->assertCount(1, $wpService->addedActions);
        $this->assertSame('EventManager/OrganizationCreated', $wpService->addedActions[0][0]);
    }

    /**
     * @testdox does not create a user if the user already exists
     */
    public function testDoesNotCreateUserIfUserAlreadyExists(): void
    {
        $wpService                = static::createWpService();
        $acfService               = static::createAcfService();
        $existingUser             = new WP_User(123);
        $wpService->existingUsers = ['existing.user@example.com' => $existingUser];

        $instance = new CreateUserWhenOrganizationCreated($wpService, $acfService);
        $instance->createUserForOrganization(1, 1, [static::createOrganizerData('existing.user@example.com')]);

        $this->assertCount(0, $wpService->createdUsers, 'No user should be created if one already exists with the same email');
        $this->assertCount(0, $acfService->calls);
    }

    /**
     * @testdox creates a user when an organization is created and no user with the same email exists
     */
    public function testCreatesUserWhenOrganizationCreated(): void
    {
        $wpService  = static::createWpService();
        $acfService = static::createAcfService();

        $instance = new CreateUserWhenOrganizationCreated($wpService, $acfService);
        $instance->createUserForOrganization(1, 1, [static::createOrganizerData()]);

        $this->assertCount(1, $wpService->createdUsers, 'A user should be created when an organization is created and no user with the same email exists');
        $this->assertSame([
            ['organizations', [1], 'user_123'],
        ], $acfService->calls);
    }

    /**
     * @testdox returns early when no organizer data is provided
     */
    public function testReturnsEarlyWhenNoOrganizerDataIsProvided(): void
    {
        $wpService  = static::createWpService();
        $acfService = static::createAcfService();

        $instance = new CreateUserWhenOrganizationCreated($wpService, $acfService);
        $instance->createUserForOrganization(1, 1, []);

        $this->assertCount(0, $wpService->wpCreateUserCalls);
        $this->assertSame(0, $wpService->wpGeneratePasswordCalls);
        $this->assertCount(0, $wpService->updatedUsers);
        $this->assertCount(0, $acfService->calls);
    }

    /**
     * @testdox skips setting role and updating user when wpCreateUser fails
     */
    public function testSkipsRoleAndUpdateWhenUserCreationFails(): void
    {
        $wpService                     = static::createWpService();
        $acfService                    = static::createAcfService();
        $wpService->wpCreateUserResult = new WP_Error('user_creation_failed', 'Could not create user');

        $instance = new CreateUserWhenOrganizationCreated($wpService, $acfService);
        $instance->createUserForOrganization(1, 1, [static::createOrganizerData()]);

        $this->assertCount(1, $wpService->wpCreateUserCalls);
        $this->assertCount(0, $wpService->getUserdataCalls);
        $this->assertCount(0, $wpService->updatedUsers);
        $this->assertCount(0, $acfService->calls);
    }

    /**
     * @testdox uses e-mail address as user login when creating a new user
     */
    public function testUsesEmailAsUserLogin(): void
    {
        $wpService  = static::createWpService();
        $acfService = static::createAcfService();
        $email      = 'test' . rand(1000, 9999) . '@example.com';

        $instance = new CreateUserWhenOrganizationCreated($wpService, $acfService);
        $instance->createUserForOrganization(1, 1, [static::createOrganizerData($email)]);

        $this->assertCount(1, $wpService->wpCreateUserCalls);
        $this->assertSame($email, $wpService->wpCreateUserCalls[0][0], 'The email address should be used as the user login when creating a new user');
    }

    /**
     * @testdox sets organization_admin role for a newly created user
     */
    public function testSetsOrganizationAdminRoleForCreatedUser(): void
    {
        $wpService                     = static::createWpService();
        $acfService                    = static::createAcfService();
        $wpService->wpCreateUserResult = 456;
        $wpUser                        = new class (456) extends WP_User {
            public ?string $roleSet = null;

            public function set_role($role): void // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
            {
                $this->roleSet = $role;
            }
        };
        $wpService->userdataResult     = $wpUser;

        $instance = new CreateUserWhenOrganizationCreated($wpService, $acfService);
        $instance->createUserForOrganization(1, 1, [static::createOrganizerData()]);

        $this->assertSame([456], $wpService->getUserdataCalls);
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


    private static function createWpService(): AddAction&GetUserBy&WpCreateUser&WpGeneratePassword&WpUpdateUser&GetUserdata&DoAction
    {
        return new class implements AddAction, GetUserBy, WpCreateUser, WpGeneratePassword, WpUpdateUser, GetUserdata, DoAction  {
            public array $addedActions              = [];
            public array $createdUsers              = [];
            public array $wpCreateUserCalls         = [];
            public array $existingUsers             = [];
            public array $getUserdataCalls          = [];
            public int $wpGeneratePasswordCalls     = 0;
            public int|WP_Error $wpCreateUserResult = 123;
            public array $updatedUsers              = [];
            public WP_User|false $userdataResult    = false;

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

            public function getUserBy(string $field, int|string $value): WP_User|false
            {
                if ($field === 'email' && isset($this->existingUsers[$value])) {
                    return $this->existingUsers[$value];
                }

                return false;
            }

            public function wpCreateUser(string $username, string $password, string $email = ''): int|WP_Error
            {
                $this->wpCreateUserCalls[] = func_get_args();

                if (!($this->wpCreateUserResult instanceof WP_Error)) {
                    $this->createdUsers[] = $this->wpCreateUserResult;
                }

                return $this->wpCreateUserResult;
            }

            public function wpGeneratePassword(int $length = 12, bool $specialChars = true, bool $extraSpecialChars = false): string
            {
                $this->wpGeneratePasswordCalls++;
                return 'generated-password';
            }

            public function wpUpdateUser(array|object $userdata): int|WP_Error
            {
                $this->updatedUsers[] = $userdata;
                return 123;
            }

            public function getUserdata(int $userId): WP_User|false
            {
                $this->getUserdataCalls[] = $userId;
                return $this->userdataResult;
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
}
