<?php

namespace EventManager\PostTypes;

use EventManager\HooksRegistrar\Hookable;
use PHPUnit\Framework\TestCase;
use WP_Post_Type;
use WP_Error;
use WpService\Contracts\__;
use WpService\Contracts\AddAction;
use WpService\Contracts\RegisterPostType;

class PostTypeTest extends TestCase
{
    /**
     * @testdox registers posttype on init hook
     */
    public function testRegistersPosttypeOnInitHook(): void
    {
        $wpService = static::createWpService();
        $postType  = new class ($wpService) extends PostType {
            public function getName(): string
            {
                return 'test_post_type';
            }

            public function getArgs(): array
            {
                return ['public' => true];
            }

            public function getLabelSingular(): string
            {
                return 'Test Post';
            }

            public function getLabelPlural(): string
            {
                return 'Test Posts';
            }
        };

        $postType->addHooks();
        $wpService->addedActions['init'][0][1]();

        // Check that post type was registered
        static::assertArrayHasKey('test_post_type', $wpService->registeredPostTypes);
    }

    private static function createWpService(): AddAction|RegisterPostType|__
    {
        return new class implements AddAction, RegisterPostType, __ {
            public array $addedActions        = [];
            public array $registeredPostTypes = [];
            public function addAction(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): true
            {
                $this->addedActions[$hookName][] = [$priority, $callback];
                return true;
            }

            public function registerPostType(string $postType, array|string $args = []): WP_Post_Type|WP_Error
            {
                $this->registeredPostTypes[$postType] = $args;
                return new \WP_Post_Type([]);
            }

            public function __(string $text, string $domain = 'default'): string
            {
                return $text;
            }
        };
    }
}
