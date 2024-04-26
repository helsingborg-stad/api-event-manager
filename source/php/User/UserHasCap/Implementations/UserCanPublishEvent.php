<?php

namespace EventManager\User\UserHasCap\Implementations;

use AcfService\Contracts\GetField;
use EventManager\User\UserHasCap\UserHasCapInterface;
use WP_User;
use WpService\Contracts\GetPost;
use WpService\Contracts\GetPostTerms;

class UserCanPublishEvent implements UserHasCapInterface
{
    public function __construct(private GetPostTerms&GetPost $wpService, private GetField $acfService)
    {
    }

    public function userHasCap(array $allcaps, array $caps, array $args, WP_User $user): array
    {
        if (!isset($args[0]) || $args[0] !== 'publish_events') {
            return $allcaps;
        }

        if (
            in_array('administrator', $user->roles) ||
            in_array('organization_administrator', $user->roles) ||
            in_array('organization_member', $user->roles)
        ) {
            $allcaps['publish_events'] = true;
            return $allcaps;
        }

        return $allcaps;
    }
}
