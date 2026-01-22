<?php

namespace EventManager\PostTypes;

use EventManager\PostTypes\Icons\Icon;
use PHPUnit\Framework\TestCase;
use WP_Post_Type;
use WP_Error;
use WpService\Contracts\__;
use WpService\Contracts\AddAction;
use WpService\Contracts\RegisterPostType;

class EventTest extends TestCase {

    /**
     * @testdox It should return the correct post type name
     */
    public function testGetName() {
        $eventPostType = new Event(static::createWpService());
        static::assertSame('event', $eventPostType->getName());
    }

    /**
     * @testdox post type is available in REST API
     */
    public function testGetArgs() {
        $eventPostType = new Event(static::createWpService());
        $args = $eventPostType->getArgs();

        static::assertArrayHasKey('show_in_rest', $args);
        static::assertTrue($args['show_in_rest']);
    }

    /**
     * @testdox It returns the correct singular label
     */
    public function testGetLabelSingular() {
        $eventPostType = new Event(static::createWpService());
        static::assertSame('Event', $eventPostType->getLabelSingular());
    }

    /**
     * @testdox It returns the correct plural label
     */
    public function testGetLabelPlural() {
        $eventPostType = new Event(static::createWpService());
        static::assertSame('Events', $eventPostType->getLabelPlural());
    }

    private static function createWpService(): AddAction|RegisterPostType|__ {
        return new class implements AddAction, RegisterPostType, __ {
            public function addAction(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): true
            {
                return true;
            }
            public function registerPostType(string $postType, array|string $args = []): WP_Post_Type|WP_Error
            {
                return new \WP_Post_Type([]);
            }
            public function __(string $text, string $domain = 'default'): string
            {
                return $text;
            }
        };
    }
}