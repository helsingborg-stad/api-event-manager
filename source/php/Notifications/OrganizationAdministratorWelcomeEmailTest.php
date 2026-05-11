<?php

namespace EventManager\Notifications;

use EventManager\Notifications\Content\OrganizationAdministratorWelcomeEmailTemplate;
use PHPUnit\Framework\TestCase;
use WP_User;
use WpService\Contracts\AddFilter;

class OrganizationAdministratorWelcomeEmailTest extends TestCase
{
    /**
     * @testdox addHooks() registers wp_new_user_notification_email filter
     */
    public function testAddHooksRegistersWelcomeEmailFilter(): void
    {
        $wpService = static::createWpService();
        $sut       = new OrganizationAdministratorWelcomeEmail($wpService, new OrganizationAdministratorWelcomeEmailTemplate());

        $sut->addHooks();

        $this->assertCount(1, $wpService->addedFilters);
        $this->assertSame('wp_new_user_notification_email', $wpService->addedFilters[0][0]);
    }

    /**
     * @testdox filterWelcomeEmail() overrides subject and message for organization administrator users
     */
    public function testFilterWelcomeEmailOverridesEmailForOrganizationAdministrator(): void
    {
        $wpService   = static::createWpService();
        $sut         = new OrganizationAdministratorWelcomeEmail($wpService, new OrganizationAdministratorWelcomeEmailTemplate());
        $user        = new WP_User(1);
        $user->roles = ['organization_administrator'];

        $defaultEmail = [
            'to'      => 'admin@example.com',
            'subject' => 'Default subject',
            'message' => 'Default message',
            'headers' => '',
        ];

        $result = $sut->filterWelcomeEmail($defaultEmail, $user, 'Event Manager');

        $this->assertNotSame('Default subject', $result['subject']);
        $this->assertNotSame('Default message', $result['message']);
        $this->assertStringContainsString('Welcome', $result['subject']);
        $this->assertStringContainsString('Event Manager', $result['message']);
    }

    /**
     * @testdox filterWelcomeEmail() leaves email unchanged for other roles
     */
    public function testFilterWelcomeEmailKeepsDefaultEmailForOtherRoles(): void
    {
        $wpService   = static::createWpService();
        $sut         = new OrganizationAdministratorWelcomeEmail($wpService, new OrganizationAdministratorWelcomeEmailTemplate());
        $user        = new WP_User(1);
        $user->roles = ['subscriber'];

        $defaultEmail = [
            'to'      => 'subscriber@example.com',
            'subject' => 'Default subject',
            'message' => 'Default message',
            'headers' => '',
        ];

        $result = $sut->filterWelcomeEmail($defaultEmail, $user, 'Event Manager');

        $this->assertSame($defaultEmail, $result);
    }

    private static function createWpService(): AddFilter
    {
        return new class implements AddFilter {
            public array $addedFilters = [];

            public function addFilter(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): true
            {
                $this->addedFilters[] = func_get_args();
                return true;
            }
        };
    }
}
