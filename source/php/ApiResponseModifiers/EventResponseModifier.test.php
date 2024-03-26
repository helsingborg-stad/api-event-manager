<?php

namespace EventManager\ApiResponseModifiers;

use EventManager\ApiResponseModifiers\EventResponseModifier;
use EventManager\PostToSchema\IPostToSchemaAdapter;
use EventManager\Services\WPService\RestEnsureResponse;
use Mockery;
use Spatie\SchemaOrg\BaseType;
use WP_Error;
use WP_Mock\Tools\TestCase;
use WP_REST_Response;

class EventResponseModifierTest extends TestCase
{
    /**
     * @testdox response is only modified in the schema context.
     */
    public function testResponseOnlyModifiedInSchemaContext()
    {
        $mockPostToSchema = Mockery::mock(IPostToSchemaAdapter::class);
        $wpService        = Mockery::mock(RestEnsureResponse::class);

        /** @var \WP_Post $mockPost */
        $mockPost     = $this->mockPost();
        $mockResponse = Mockery::mock('WP_REST_Response');
        $mockRequest  = Mockery::mock('WP_REST_Request');

        $mockRequest->shouldReceive('get_param')->with('context')->andReturn('view');

        $wpService->shouldNotReceive('restEnsureResponse');

        $eventModifier = new EventResponseModifier($mockPostToSchema, $wpService);
        $eventModifier->modify($mockResponse, $mockPost, $mockRequest);

        $this->assertConditionsMet();
    }

    /**
     * @testdox response is modified in the schema context.
     * @runInSeparateProcess
     */
    public function testResponseModifiedInSchemaContext()
    {
        $mockPostToSchema = Mockery::mock(IPostToSchemaAdapter::class);
        $wpService        = $this->getWpService();
        $schema           = Mockery::mock(BaseType::class);

        $mockPostToSchema->shouldReceive('getSchema')->andReturn($schema);
        $schema->shouldReceive('toArray')->andReturn(['foo']);


        /** @var \WP_Post $mockPost */
        $mockPost     = $this->mockPost();
        $mockResponse = Mockery::mock('WP_REST_Response');
        $mockRequest  = Mockery::mock('WP_REST_Request');

        $mockRequest->shouldReceive('get_param')->with('context')->andReturn('schema');

        $eventModifier = new EventResponseModifier($mockPostToSchema, $wpService);
        $response      = $eventModifier->modify($mockResponse, $mockPost, $mockRequest);

        $this->assertEquals(['foo'], $response->get_data());
    }

    public function getWpService(): RestEnsureResponse
    {
        return new class implements RestEnsureResponse {
            public function restEnsureResponse($response): WP_REST_Response|WP_Error
            {
                $ensured = Mockery::mock(WP_REST_Response::class);
                $ensured->allows('get_data')->andReturn($response);
                return $ensured;
            }
        };
    }
}
