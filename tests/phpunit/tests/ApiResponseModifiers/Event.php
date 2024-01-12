<?php

namespace EventManager\Tests\ApiResponseModifiers;

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
        $mockPostToSchema = Mockery::mock('EventManager\Helper\PostToSchema\PostToSchemaInterface');

        $mockRequest->shouldReceive('get_param')->with('context')->andReturn('view');
        WP_Mock::userFunction('rest_ensure_response')->never();

        $eventModifier = new \EventManager\ApiResponseModifiers\Event($mockPostToSchema);
        $eventModifier->modify($mockResponse, $mockPost, $mockRequest);

        $this->assertConditionsMet();
    }

    /**
     * @testdox response is modified in the schema context.
     */
    public function testResponseModifiedInSchemaContext()
    {
        /** @var \WP_Post $mockPost */
        $mockPost         = $this->mockPost();
        $mockResponse     = Mockery::mock('WP_REST_Response');
        $mockRequest      = Mockery::mock('WP_REST_Request');
        $mockPostToSchema = Mockery::mock('EventManager\Helper\PostToSchema\PostToSchemaInterface');
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
