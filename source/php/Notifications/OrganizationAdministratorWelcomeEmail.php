<?php

namespace EventManager\Notifications;

use EventManager\HooksRegistrar\Hookable;
use EventManager\Notifications\Content\WelcomeEmailTemplate;
use WP_User;
use WpService\Contracts\AddFilter;

class OrganizationAdministratorWelcomeEmail implements Hookable
{
    public function __construct(
        private AddFilter $wpService,
        private WelcomeEmailTemplate $template
    ) {
    }

    public function addHooks(): void
    {
        $this->wpService->addFilter('wp_new_user_notification_email', [$this, 'filterWelcomeEmail'], 10, 3);
    }

    public function filterWelcomeEmail(array $email, WP_User $user, string $blogName): array
    {
        if (!$this->isOrganizationAdministrator($user)) {
            return $email;
        }

        $email['subject'] = $this->template->getSubject($user, $blogName);
        $email['message'] = $this->template->getMessage($user, $blogName);

        return $email;
    }

    private function isOrganizationAdministrator(WP_User $user): bool
    {
        return in_array('organization_administrator', $user->roles, true);
    }
}
