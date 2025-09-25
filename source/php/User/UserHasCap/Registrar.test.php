<?php

namespace EventManager\User\UserHasCap;

use WpService\Contracts\AddFilter;
use PHPUnit\Framework\TestCase;
use WP_User;

class RegistrarTest extends TestCase
{
    /**
     * @testdox adds filter for each user capability
     */
    public function testAddHooks()
    {
        $wpService = $this->getWpService();
        $registrar = new \EventManager\User\UserHasCap\Registrar([$this->getFakeUserHasCap()], $wpService);

        $registrar->addHooks();

        $this->assertCount(1, $wpService->invoked);
    }

    private function getFakeUserHasCap(): UserHasCapInterface
    {
        return new class implements UserHasCapInterface {
            public function userHasCap(array $allcaps, array $caps, array $args, WP_User $user): array
            {
                return [];
            }
        };
    }

    private function getWpService(): AddFilter
    {
        return new class implements AddFilter {
            public array $invoked = [];
            public function addFilter(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): true
            {
                $this->invoked[] = [$hookName, $callback, $priority, $acceptedArgs];
                return true;
            }
        };
    }
}
