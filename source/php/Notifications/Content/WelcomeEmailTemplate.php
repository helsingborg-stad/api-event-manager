<?php

namespace EventManager\Notifications\Content;

use WP_User;

interface WelcomeEmailTemplate
{
    public function getSubject(WP_User $user, string $blogName): string;

    public function getMessage(WP_User $user, string $blogName): string;
}
