<?php

namespace EventManager\Notifications\Content;

use WP_User;

class OrganizationAdministratorWelcomeEmailTemplate implements WelcomeEmailTemplate
{
    public function getSubject(WP_User $user, string $blogName): string
    {
        return sprintf('Welcome to %s', $blogName);
    }

    public function getMessage(WP_User $user, string $blogName): string
    {
        return sprintf(
            "Hi %s,\n\nWelcome to %s. Your organization administrator account is now active.\n\nYou can now sign in and get started.",
            $user->user_login,
            $blogName
        );
    }
}
