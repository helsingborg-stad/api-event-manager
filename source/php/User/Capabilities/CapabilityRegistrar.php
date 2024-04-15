<?php

namespace EventManager\User\Capabilities;

use EventManager\Helper\Hookable;
use EventManager\Services\WPService\AddFilter;
use WP_User;

class CapabilityRegistrar implements Hookable
{
    /**
     * @param CapabilityInterface[] $capabilities
     * @param AddFilter    $wpService
     */
    public function __construct(private array $capabilities, private AddFilter $wpService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addFilter('user_has_cap', [$this, 'filterUserCapabilities'], 10, 4);
    }

    /**
     * Dynamically filter a user's capabilities.
     *
     * @param bool[]   $allcaps Array of key/value pairs where keys represent a capability name
     *                          and boolean values represent whether the user has that capability.
     * @param string[] $caps    Required primitive capabilities for the requested capability.
     * @param array    $args {
     *     Arguments that accompany the requested capability check.
     *
     *     @type string    $0 Requested capability.
     *     @type int       $1 Concerned user ID.
     *     @type mixed  ...$2 Optional second and further parameters, typically object ID.
     * }
     * @param WP_User  $user    The user object.
     */
    public function filterUserCapabilities(array $allcaps, array $caps, array $args, WP_User $user): array
    {
        foreach ($this->capabilities as $capability) {
            if ($capability->getName() === $args[0]) {
                $userCan = $capability->userCan($user->ID, array_slice($args, 2));

                if ($userCan) {
                    // Set all capabilities to true
                    $allcaps = array_fill_keys($caps, true);
                }
            }
        }

        return $allcaps;
    }
}
