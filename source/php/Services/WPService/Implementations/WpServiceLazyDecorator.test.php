<?php

namespace EventManager\Services\WPService\Implementations;

use PHPUnit\Framework\TestCase;

class WpServiceLazyDecoratorTest extends TestCase
{
    /**
     * @testdox Allows setting the WP service
     */
    public function testAllowsSettingTheWpService(): void
    {
        $wpService              = new NativeWpService();
        $wpServiceLazyDecorator = new WpServiceLazyDecorator();
        $wpServiceLazyDecorator->setInner($wpService);

        $this->assertSame($wpService, $wpServiceLazyDecorator->inner);
    }

    /**
     * @testdox The inner WP service is null by default
     */
    public function testInnerWpServiceIsNullByDefault(): void
    {
        $wpServiceLazyDecorator = new WpServiceLazyDecorator();

        $this->assertNull($wpServiceLazyDecorator->inner);
    }
}
