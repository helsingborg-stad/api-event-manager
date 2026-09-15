<?php

namespace EventManager\Organizations;

use AcfService\Contracts\GetField;
use EventManager\HooksRegistrar\Hookable;
use PHPUnit\Framework\TestCase;
use WP_Screen;
use WP_User;
use WpService\Contracts\__;
use WpService\Contracts\AddAction;
use WpService\Contracts\AddQueryArg;
use WpService\Contracts\CurrentUserCan;
use WpService\Contracts\GetCurrentScreen;
use WpService\Contracts\GetUsers;
use WpService\Contracts\WpAdminNotice;
use WpService\Contracts\WpCreateNonce;
use WpService\Contracts\WpVerifyNonce;

class MissingOrganizationAdminNoticeTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_GET['event_manager_action'], $_GET['tag_ID'], $_GET['taxonomy'], $_GET['_event_manager_nonce'], $_POST['event_manager_action'], $_POST['tag_ID'], $_POST['taxonomy'], $_POST['_event_manager_nonce']);
    }

    /**
     * @testdox hooks into admin_init and admin_notices
     */
    public function testHooksIntoAdminInitAndAdminNotices(): void
    {
        $wpService = $this->createWpService();
        $instance  = new MissingOrganizationAdminNotice(
            $wpService,
            $this->createAcfService(),
            $this->createOrganizationAdminUser(),
            'organization'
        );

        $instance->addHooks();

        $this->assertSame('admin_init', $wpService->addedActions[0][0]);
        $this->assertSame('admin_notices', $wpService->addedActions[1][0]);
    }

    /**
     * @testdox renderNotice() shows a create action when the organization has no administrator user
     */
    public function testRenderNoticeShowsCreateActionWhenOrganizationHasNoAdministratorUser(): void
    {
        $wpService                = $this->createWpService();
        $wpService->currentScreen = $this->createOrganizationTermScreen();
        $_GET['tag_ID']           = 174;
        $acfService               = $this->createAcfService(['organization_174' => 'admin@example.com']);
        $instance                 = new MissingOrganizationAdminNotice(
            $wpService,
            $acfService,
            $this->createOrganizationAdminUser(),
            'organization'
        );

        $instance->renderNotice();

        $this->assertCount(1, $wpService->adminNotices);
        $this->assertStringContainsString('Create organization administrator', $wpService->adminNotices[0]['message']);
        $this->assertStringContainsString('admin@example.com', $wpService->adminNotices[0]['message']);
        $this->assertStringContainsString('event_manager_action=create_missing_organization_admin_user', $wpService->adminNotices[0]['message']);
    }

    /**
     * @testdox renderNotice() does not show a create action when an administrator user is already connected
     */
    public function testRenderNoticeDoesNotShowCreateActionWhenAdministratorUserExists(): void
    {
        $wpService                = $this->createWpService([new WP_User()]);
        $wpService->currentScreen = $this->createOrganizationTermScreen();
        $_GET['tag_ID']           = 174;
        $instance                 = new MissingOrganizationAdminNotice(
            $wpService,
            $this->createAcfService(['organization_174' => 'admin@example.com']),
            $this->createOrganizationAdminUser(),
            'organization'
        );

        $instance->renderNotice();

        $this->assertSame([], $wpService->adminNotices);
    }

    /**
     * @testdox renderNotice() shows a warning when no organization email exists
     */
    public function testRenderNoticeShowsWarningWhenNoOrganizationEmailExists(): void
    {
        $wpService                = $this->createWpService();
        $wpService->currentScreen = $this->createOrganizationTermScreen();
        $_GET['tag_ID']           = 174;
        $instance                 = new MissingOrganizationAdminNotice(
            $wpService,
            $this->createAcfService(['organization_174' => '']),
            $this->createOrganizationAdminUser(),
            'organization'
        );

        $instance->renderNotice();

        $this->assertCount(1, $wpService->adminNotices);
        $this->assertStringContainsString('Add an email address to this organization before creating an administrator user.', $wpService->adminNotices[0]['message']);
    }

    /**
     * @testdox handleCreateRequest() creates a missing organization administrator user when the request is valid
     */
    public function testHandleCreateRequestCreatesMissingOrganizationAdministratorUserWhenRequestIsValid(): void
    {
        $wpService                    = $this->createWpService();
        $wpService->currentScreen     = $this->createOrganizationTermScreen();
        $_GET['tag_ID']               = 174;
        $_GET['event_manager_action'] = 'create_missing_organization_admin_user';
        $_GET['taxonomy']             = 'organization';
        $_GET['_event_manager_nonce'] = 'valid-nonce';
        $createOrganizationAdminUser  = $this->createOrganizationAdminUser();
        $instance                     = new MissingOrganizationAdminNotice(
            $wpService,
            $this->createAcfService(['organization_174' => 'admin@example.com']),
            $createOrganizationAdminUser,
            'organization'
        );

        $instance->handleCreateRequest();
        $instance->renderNotice();

        $this->assertSame([
            [174, 'admin@example.com'],
        ], $createOrganizationAdminUser->calls);
        $this->assertCount(1, $wpService->adminNotices);
        $this->assertStringContainsString('Organization administrator user created.', $wpService->adminNotices[0]['message']);
    }

    /**
     * @testdox handleCreateRequest() does not create a user when the nonce is invalid
     */
    public function testHandleCreateRequestDoesNotCreateUserWhenNonceIsInvalid(): void
    {
        $wpService                    = $this->createWpService();
        $wpService->nonceIsValid      = false;
        $_GET['event_manager_action'] = 'create_missing_organization_admin_user';
        $_GET['tag_ID']               = 174;
        $_GET['taxonomy']             = 'organization';
        $_GET['_event_manager_nonce'] = 'invalid-nonce';
        $createOrganizationAdminUser  = $this->createOrganizationAdminUser();
        $instance                     = new MissingOrganizationAdminNotice(
            $wpService,
            $this->createAcfService(['organization_174' => 'admin@example.com']),
            $createOrganizationAdminUser,
            'organization'
        );

        $instance->handleCreateRequest();

        $this->assertSame([], $createOrganizationAdminUser->calls);
    }

    private function createWpService(array $users = []): AddAction&AddQueryArg&GetCurrentScreen&CurrentUserCan&WpAdminNotice&WpCreateNonce&WpVerifyNonce&GetUsers&__
    {
        return new class ($users) implements AddAction, AddQueryArg, GetCurrentScreen, CurrentUserCan, WpAdminNotice, WpCreateNonce, WpVerifyNonce, GetUsers, __ {
            public array $addedActions       = [];
            public ?WP_Screen $currentScreen = null;
            public array $adminNotices       = [];
            public bool $canEditOrganization = true;
            public bool $nonceIsValid        = true;

            public function __construct(private array $users)
            {
            }

            public function addAction(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): true
            {
                $this->addedActions[] = func_get_args();
                return true;
            }

            public function addQueryArg(...$args): string
            {
                return 'http://example.com/wp-admin/term.php?' . http_build_query($args[0] ?? []);
            }

            public function getCurrentScreen(): ?WP_Screen
            {
                return $this->currentScreen;
            }

            public function currentUserCan(string $capability, mixed ...$args): bool
            {
                return $this->canEditOrganization;
            }

            public function wpAdminNotice(string $message, array $args = []): void
            {
                $this->adminNotices[] = [
                    'message' => $message,
                    'args'    => $args,
                ];
            }

            public function wpCreateNonce(string|int $action): string
            {
                return 'valid-nonce';
            }

            public function wpVerifyNonce(string $nonce, string|int $action): int|false
            {
                return $this->nonceIsValid ? 1 : false;
            }

            public function getUsers(array $args = []): array
            {
                return $this->users;
            }

            public function __(string $text, string $domain = 'default'): string
            {
                return $text;
            }
        };
    }

    private function createAcfService(array $emailByPostId = []): GetField
    {
        return new class ($emailByPostId) implements GetField {
            public function __construct(private array $emailByPostId)
            {
            }

            public function getField(string $selector, int|false|string $postId = false, bool $formatValue = true, bool $escapeHtml = false)
            {
                return $this->emailByPostId[$postId] ?? '';
            }
        };
    }

    private function createOrganizationAdminUser(): CreateOrganizationAdminUser
    {
        return new class extends CreateOrganizationAdminUser {
            public array $calls = [];

            public function __construct()
            {
            }

            public function create(int $organizationId, string $email): WP_User
            {
                $this->calls[] = [$organizationId, $email];
                return new WP_User();
            }
        };
    }

    private function createOrganizationTermScreen(): WP_Screen
    {
        $screen           = new WP_Screen();
        $screen->base     = 'term';
        $screen->taxonomy = 'organization';

        return $screen;
    }
}
