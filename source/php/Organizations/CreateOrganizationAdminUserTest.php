<?php

namespace EventManager\Organizations;

use AcfService\Contracts\UpdateField;
use EventManager\Helper\CreateUserFromEmailInterface;
use PHPUnit\Framework\TestCase;
use WP_Error;
use WP_User;
use WpService\Contracts\DoAction;
use WpService\Contracts\WpUpdateUser;

class CreateOrganizationAdminUserTest extends TestCase
{
    /**
     * @testdox creates, promotes and connects an organization administrator user
     */
    public function testCreatesPromotesAndConnectsAnOrganizationAdministratorUser(): void
    {
        $wpService                   = $this->createWpService();
        $acfService                  = $this->createAcfService();
        $user                        = new class extends WP_User {
            public ?string $roleSet = null;

            public function set_role($role): void // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
            {
                $this->roleSet = $role;
            }
        };
        $user->ID                    = 123;
        $createOrganizationAdminUser = new CreateOrganizationAdminUser(
            $wpService,
            $acfService,
            $this->createCreateUserFromEmail($user)
        );

        $createdUser = $createOrganizationAdminUser->create(174, 'admin@example.com');

        $this->assertSame($user, $createdUser);
        $this->assertSame('organization_administrator', $user->roleSet);
        $this->assertSame([$user], $wpService->updatedUsers);
        $this->assertSame([
            ['organizations', [174], 'user_123'],
        ], $acfService->calls);
        $this->assertSame([
            ['EventManager/OrganizationUserCreated', [$user]],
        ], $wpService->actions);
    }

    private function createWpService(): WpUpdateUser&DoAction
    {
        return new class implements WpUpdateUser, DoAction {
            public array $updatedUsers = [];
            public array $actions      = [];

            public function wpUpdateUser(array|object $userdata): int|WP_Error
            {
                $this->updatedUsers[] = $userdata;
                return 123;
            }

            public function doAction(string $hookName, mixed ...$arg): void
            {
                $this->actions[] = [$hookName, $arg];
            }
        };
    }

    private function createAcfService(): UpdateField
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

    private function createCreateUserFromEmail(WP_User $user): CreateUserFromEmailInterface
    {
        return new class ($user) implements CreateUserFromEmailInterface {
            public function __construct(private WP_User $user)
            {
            }

            public function createUserFromEmail(string $email): WP_User
            {
                return $this->user;
            }
        };
    }
}
