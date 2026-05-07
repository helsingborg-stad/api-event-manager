<?php

namespace EventManager;

use AcfService\AcfService;
use EventManager\CronScheduler\CronSchedulerInterface;
use EventManager\HooksRegistrar\HooksRegistrarInterface;
use PHPUnit\Framework\TestCase;
use WpService\WpService;
use WpService\Implementations\FakeWpService;

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
            $this->getWpService(),
            $this->createMock(AcfService::class),
            $this->createMock(HooksRegistrarInterface::class),
            $this->createMock(CronSchedulerInterface::class)
        );

        $this->assertInstanceOf(App::class, $app);
    }

    /**
     * @testdox all functions can be run without crashing
     * @dataProvider provideAllFunctions
     */
    public function testAllFunctions(string $method)
    {
        $app = new App(
            'textdomain',
            $this->getWpService(),
            $this->createMock(AcfService::class),
            $this->createMock(HooksRegistrarInterface::class),
            $this->createMock(CronSchedulerInterface::class)
        );

        $app->$method();

        $this->assertTrue(true);
    }

    public function provideAllFunctions(): array
    {
        $appClass = App::class;
        $methods  = get_class_methods(App::class);
        $methods  = array_filter($methods, fn ($method) => $method !== '__construct');

        $providedMethods = [];

        foreach ($methods as $method) {
            $providedMethods["{$appClass}::{$method}()"] = [$method];
        }

        return $providedMethods;
    }

    private function getWpService(): WpService
    {
        return new FakeWpService([
            'addAction' => true,
            'addFilter' => true,
            '__'        => fn (string $text): string => $text,
        ]);
    }
}
