<?php

namespace EventManager\Tests\PostTableColumns\Helpers;

use EventManager\PostTableColumns\Helpers\GetNestedArrayStringValueRecursive;
use PHPUnit\Framework\TestCase;

class GetNestedArrayStringValueRecursiveTest extends TestCase
{
    /**
     * @testdox returns the value of a nested array
     */
    public function testGetNestedArrayStringValueRecursive(): void
    {
        $getNestedArrayStringValueRecursive = new GetNestedArrayStringValueRecursive();
        $nestedArray                        = [ 'foo' => [ 'bar' => [ 'baz' => 'qux' ] ] ];
        $columnIdentifiers                  = ['foo', 'bar', 'baz'];

        $actual = $getNestedArrayStringValueRecursive->getNestedArrayStringValueRecursive($columnIdentifiers, $nestedArray);

        $this->assertEquals('qux', $actual);
    }

    /**
     * @testdox returns the value of a flat array
     */
    public function testGetNestedArrayStringValueRecursiveFlatArray(): void
    {
        $getNestedArrayStringValueRecursive = new GetNestedArrayStringValueRecursive();
        $nestedArray                        = [ 'foo' => 'bar' ];
        $columnIdentifiers                  = ['foo'];

        $actual = $getNestedArrayStringValueRecursive->getNestedArrayStringValueRecursive($columnIdentifiers, $nestedArray);

        $this->assertEquals('bar', $actual);
    }
}
