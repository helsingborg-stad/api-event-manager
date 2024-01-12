<?php

namespace EventManager\Tests;

use EventManager\App;
use Mockery;
use WP_Mock;
use WP_Mock\Tools\TestCase;

class AppTest extends TestCase
{
    /**
     * @testdox class exists
     */
    public function testClassExists()
    {
        $this->assertTrue(class_exists('EventManager\App'));
    }
}
