<?php

namespace EventManager\Tests\Helper;

use WpService\WpService;
use Mockery;
use WP_Mock\Tools\TestCase;

class PostTypeTest extends TestCase
{
    /**
     * @testdox registers with provided post type name
     */
    public function testRegistersPostTypeByName()
    {
        $postTypeName = 'test_post_type';

        $mockWPService = Mockery::mock(WPService::class);
        $mockWPService->shouldReceive('registerPostType')->once()->with($postTypeName, Mockery::any());
        $mockPostType = $this->getMockForAbstractClass('EventManager\Helper\PostType', [$mockWPService]);
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

        $mockWPService = Mockery::mock(WPService::class);
        $mockWPService->shouldReceive('registerPostType')->once()->with(
            Mockery::any(),
            Mockery::on(fn ($args) =>
                isset($args['labels']) &&
                is_array($args['labels']) &&
                $args['labels']['name'] === 'Foos' &&
                $args['labels']['singular_name'] === 'Foo'),
        );
        $mockPostType = $this->getMockForAbstractClass('EventManager\Helper\PostType', [$mockWPService]);
        $mockPostType->expects($this->once())->method('getLabelSingular')->willReturn($labelSingular);
        $mockPostType->expects($this->once())->method('getLabelPlural')->willReturn($labelPlural);

        $mockPostType->register();
    }

    public function testRegistersWithProvidedArgs()
    {
        $args = ['foo' => 'bar'];

        $mockWPService = Mockery::mock(WPService::class);
        $mockWPService->shouldReceive('registerPostType')->once()->with(Mockery::any(), Mockery::on(fn ($args) => $args['foo'] === 'bar'));
        $mockPostType = $this->getMockForAbstractClass('EventManager\Helper\PostType', [$mockWPService]);
        $mockPostType->expects($this->once())->method('getArgs')->willReturn($args);

        $mockPostType->register();
    }
}
