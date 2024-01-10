<?php

namespace EventManager\Tests\PostTypeRegistrar;

use Mockery;
use WP_Mock;
use WP_Mock\Tools\TestCase;
use WP_Post_Type;

class PostTypeRegistrar extends TestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists('EventManager\PostTypeRegistrar\PostTypeRegistrar'));
    }

    /**
     * @testdox register() calls register_post_type() with supplied name and arguments
     */
    public function testRegisterCallsRegisterPostType()
    {
        // Arrange
        $postTypeName = 'test_post_type';
        $postTypeArgs = ['labels' => ['name' => 'Test Post Type']];

        WP_Mock::userFunction('register_post_type', [
            'times' => 1,
            'args'  => [$postTypeName, $postTypeArgs],
        ]);

        // Act
        $postTypeRegistrar = new \EventManager\PostTypeRegistrar\PostTypeRegistrar();
        $postTypeRegistrar->register($postTypeName, $postTypeArgs);

        // Assert
        $this->assertConditionsMet();
    }
}
