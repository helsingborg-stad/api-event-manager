<?php

namespace EventManager\Tests\ApiResponseModifiers;

use EventManager\Services\AcfService\AcfService;
use EventManager\Services\WPService\WPService;
use Mockery;
use WP_Mock;
use WP_Mock\Tools\TestCase;

class EventTest extends TestCase
{
    /**
     * @testdox response is only modified in the schema context.
     */
    public function testResponseOnlyModifiedInSchemaContext()
    {
        $mockPost         = Mockery::mock('WP_Post');
        $mockResponse     = Mockery::mock('WP_REST_Response');
        $mockRequest      = Mockery::mock('WP_REST_Request');
        $mockPostToSchema = Mockery::mock('EventManager\PostToSchema\PostToSchemaInterface');

        $mockRequest->shouldReceive('get_param')->with('context')->andReturn('view');
        WP_Mock::userFunction('rest_ensure_response')->never();

        $eventModifier = new \EventManager\ApiResponseModifiers\Event($mockPostToSchema);
        $eventModifier->modify($mockResponse, $mockPost, $mockRequest);

        $this->assertConditionsMet();
    }

    /**
     * @testdox response is modified in the schema context.
     * @runInSeparateProcess
     */
    public function testResponseModifiedInSchemaContext()
    {
        Mockery::mock('alias:\EventManager\Services\WPService\WPServiceFactory')
            ->shouldReceive('create')
            ->andReturn(Mockery::mock(WPService::class));
        Mockery::mock('alias:\EventManager\Services\AcfService\AcfServiceFactory')
            ->shouldReceive('create')
            ->andReturn(Mockery::mock(AcfService::class));
        $eventBuilder = Mockery::mock('overload:\EventManager\PostToSchema\EventBuilder');
        $eventBuilder->shouldReceive('build')->andReturnSelf();
        $eventBuilder->shouldReceive('toArray')->andReturn([]);


        /** @var \WP_Post $mockPost */
        $mockPost         = $this->mockPost();
        $mockResponse     = Mockery::mock('WP_REST_Response');
        $mockRequest      = Mockery::mock('WP_REST_Request');
        $mockPostToSchema = Mockery::mock('EventManager\PostToSchema\PostToSchemaInterface');
        $mockPostToSchema->shouldReceive('transform')->with($mockPost)->andReturn([]);

        $mockRequest->shouldReceive('get_param')->with('context')->andReturn('schema');

        WP_Mock::userFunction('rest_ensure_response')
            ->once()
            ->andReturn(Mockery::mock('WP_REST_Response'));

        $eventModifier = new \EventManager\ApiResponseModifiers\Event($mockPostToSchema);
        $eventModifier->modify($mockResponse, $mockPost, $mockRequest);

        $this->assertConditionsMet();
    }
}
