<?php

namespace EventManager\User;

use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    /**
     * @testdox getRole() returns the role
     */
    public function testGetRole()
    {
        $Role = new Role('testRole', 'Test Role');
        $this->assertEquals('testRole', $Role->getRole());
    }

    /**
     * @testdox getName() returns the name
     */
    public function testGetName()
    {
        $Role = new Role('testRole', 'Test Role');
        $this->assertEquals('Test Role', $Role->getName());
    }

    /**
     * @testdox getCapabilities() returns the capabilities
     */
    public function testGetCapabilities()
    {
        $Role = new Role('testRole', 'Test Role', ['testCapability' => true]);
        $this->assertEquals(['testCapability' => true], $Role->getCapabilities());
    }
}
