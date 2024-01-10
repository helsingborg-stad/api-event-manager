<?php

namespace EventManager\Tests;

use EventManager\App;
use Mockery;
use WP_Mock;
use WP_Mock\Tools\TestCase;

class AppTest extends TestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists('EventManager\App'));
    }

    /**
     * @testdox registerPostTypes() registers the event post type with name and vital arguments
     */
    public function testPostTypeEventIsRegistered()
    {
        // Arrange
        $vitalArgsValidation = function ($args) {
            return  $args['show_in_rest'] === true &&
                    $args['public'] === true &&
                    $args['hierarchical'] === true;
        };

        $postTypeRegistrar = Mockery::mock('EventManager\PostTypeRegistrar\PostTypeRegistrar');
        $postTypeRegistrar
            ->shouldReceive('register')
            ->once()
            ->with('event', Mockery::on($vitalArgsValidation));

        // Act
        $app = new \EventManager\App($postTypeRegistrar);
        $app->registerPostTypes();

        // Assert
        $this->assertConditionsMet();
    }
}
