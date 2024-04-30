<?php

namespace EventManager\HooksRegistrar;

use EventManager\HooksRegistrar\Hookable;
use WP_Mock\Tools\TestCase;

class HooksRegistrarTest extends TestCase
{
    /**
     * @testdox register() calls addHooks() on provided object
     */
    public function testRegisterCallsAddHooksOnProvidedObject()
    {
        $hookable       = $this->getHookableClass();
        $hooksRegistrar = new \EventManager\HooksRegistrar\HooksRegistrar();

        ob_start();
        $hooksRegistrar->register($hookable);

        $this->assertEquals('Hooks added!', ob_get_clean());
    }

    private function getHookableClass(): Hookable
    {
        return new class implements Hookable {
            public function addHooks(): void
            {
                echo 'Hooks added!';
            }
        };
    }
}
