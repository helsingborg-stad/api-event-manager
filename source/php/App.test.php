<?php

namespace EventManager;

use AcfService\AcfService;
use EventManager\CronScheduler\CronSchedulerInterface;
use EventManager\HooksRegistrar\HooksRegistrarInterface;
use PHPUnit\Framework\TestCase;
use WpService\WpService;

class AppTest extends TestCase
{
    protected function setUp(): void
    {
        if (!defined('OBJECT')) {
            define('OBJECT', 'object');
        }

        if (!defined('ARRAY_A')) {
            define('ARRAY_A', 'array');
        }
    }

    /**
     * @testdox does not crash on instantiation
     */
    public function testInstantiation()
    {
        $app = new App(
            'textdomain',
            $this->createMock(WpService::class),
            $this->createMock(AcfService::class),
            $this->createMock(HooksRegistrarInterface::class),
            $this->createMock(CronSchedulerInterface::class)
        );

        $this->assertInstanceOf(App::class, $app);
    }

    /**
     * @testdox all functions can be run without crashing
     */
    public function testAllFunctions()
    {
        $app = new App(
            'textdomain',
            $this->createMock(WpService::class),
            $this->createMock(AcfService::class),
            $this->createMock(HooksRegistrarInterface::class),
            $this->createMock(CronSchedulerInterface::class)
        );

        foreach (get_class_methods(App::class) as $method) {
            if ($method === '__construct') {
                continue;
            }

            $app->$method();
        }

        $this->assertTrue(true);
    }
}
