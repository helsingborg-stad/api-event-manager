<?php

namespace EventManager\Tests\Helper;

use Mockery;
use WP_Mock\Tools\TestCase;

class HooksRegistrarTest extends TestCase
{
    /**
     * @testdox class exists
     */
    public function testClassExists()
    {
        $this->assertTrue(class_exists('EventManager\Helper\HooksRegistrar'));
    }

    /**
     * @testdox register() calls addHooks() on provided object
     */
    public function testRegisterCallsAddHooksOnProvidedObject()
    {
        $mock = Mockery::mock('EventManager\Helper\Hookable');
        $mock->shouldReceive('addHooks')->once();

        $hooksRegistrar = new \EventManager\Helper\HooksRegistrar();
        $hooksRegistrar->register($mock);

        $this->assertConditionsMet();
    }
}
