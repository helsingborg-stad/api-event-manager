<?php

namespace EventManager\Notifications\NotificationsEditor;

use AcfService\Contracts\AddLocalFieldGroup;
use EventManager\HooksRegistrar\Hookable;
use PHPUnit\Framework\TestCase;
use WpService\Contracts\__;
use WpService\Contracts\AddAction;

class NotificationsEditorTest extends TestCase
{
    /**
     * @testdox acf field group is registered on acf/init action hook
     */
    public function testFieldGroupIsRegistered(): void
    {
        $acfService = static::createAcfService();
        $wpService  = static::createWpService();

        $editor = new NotificationsEditor($acfService, $wpService);
        $editor->addHooks();
        $callable = $wpService->addedActions['acf/init'][0];
        call_user_func($callable);

        static::assertCount(1, $acfService->addedFieldGroups);
    }

    private static function createAcfService(): AddLocalFieldGroup
    {
        return new class implements AddLocalFieldGroup {
            public array $addedFieldGroups = [];

            public function addLocalFieldGroup(array $fieldGroup): bool
            {
                $this->addedFieldGroups[] = $fieldGroup;
                return true;
            }
        };
    }

    private static function createWpService(): AddAction&__
    {
        return new class implements AddAction, __ {
            public array $addedActions = [];

            public function addAction(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): true
            {
                $this->addedActions[$hookName][] = $callback;
                return true;
            }

            public function __($text, $domain = 'default'): string
            {
                return $text;
            }
        };
    }
}
