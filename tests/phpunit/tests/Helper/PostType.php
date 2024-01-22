<?php

namespace EventManager\Tests\Helper;

use Mockery;
use WP_Mock;
use WP_Mock\Tools\TestCase;

class PostTypeTest extends TestCase
{
    /**
     * @testdox registers with provided post type name
     */
    public function testRegistersPostTypeByName()
    {
        $postTypeName = 'test_post_type';
        WP_Mock::userFunction('register_post_type', [
            'times' => 1,
            'args'  => [$postTypeName, Mockery::any()],
        ]);

        $mockPostType = $this->getMockForAbstractClass('EventManager\Helper\PostType');
        $mockPostType->expects($this->once())->method('getName')->willReturn($postTypeName);

        $mockPostType->register();
    }

    /**
     * @testdox registers with provided labels
     */
    public function testRegistersWithProvidedLabels()
    {
        $labelSingular = 'Foo';
        $labelPlural   = 'Foos';

        WP_Mock::userFunction('register_post_type', [
            'times' => 1,
            'args'  => [Mockery::any(), Mockery::on(fn ($args) =>
                isset($args['labels']) &&
                is_array($args['labels']) &&
                $args['labels']['name'] === 'Foos' &&
                $args['labels']['singular_name'] === 'Foo')],
        ]);

        $mockPostType = $this->getMockForAbstractClass('EventManager\Helper\PostType');
        $mockPostType->expects($this->once())->method('getLabelSingular')->willReturn($labelSingular);
        $mockPostType->expects($this->once())->method('getLabelPlural')->willReturn($labelPlural);

        $mockPostType->register();
    }

    public function testRegistersWithProvidedArgs()
    {
        $args = ['foo' => 'bar'];

        WP_Mock::userFunction('register_post_type', [
            'times' => 1,
            'args'  => [Mockery::any(), Mockery::on(fn ($args) => $args['foo'] === 'bar')],
        ]);

        $mockPostType = $this->getMockForAbstractClass('EventManager\Helper\PostType');
        $mockPostType->expects($this->once())->method('getArgs')->willReturn($args);

        $mockPostType->register();
    }
}
