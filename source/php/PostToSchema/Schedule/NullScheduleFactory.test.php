<?php

namespace EventManager\PostToSchema\Schedule;

use PHPUnit\Framework\TestCase;

class NullScheduleFactoryTest extends TestCase
{
    /**
     * @testdox Returns null.
     */
    public function testReturnsNull()
    {
        $factory = new NullScheduleFactory();
        $this->assertNull($factory->create([]));
    }
}
