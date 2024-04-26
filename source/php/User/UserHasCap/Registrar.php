<?php

namespace EventManager\User\UserHasCap;

use EventManager\Helper\Hookable;
use WpService\Contracts\AddFilter;

class Registrar implements Hookable
{
    /**
     * @param UserHasCap[] $userHasCapArray
     * @param AddFilter    $wpService
     */
    public function __construct(private array $userHasCapArray, private AddFilter $wpService)
    {
    }

    public function addHooks(): void
    {
        foreach ($this->userHasCapArray as $userHasCap) {
            $this->wpService->addFilter('user_has_cap', [$userHasCap, 'userHasCap'], 10, 4);
        }
    }
}
