<?php

namespace EventManager\ApiResponseModifiers;

use EventManager\HooksRegistrar\Hookable;
use EventManager\PostToSchema\IPostToSchemaAdapter;
use WpService\Contracts\RestEnsureResponse;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;

class EventResponseModifier implements Hookable, IResponseModifier
{
    private const CONTEXT = 'schema';

    public function __construct(
        private IPostToSchemaAdapter $postToSchemaAdapter,
        private RestEnsureResponse $wpService,
    ) {
    }

    public function addHooks(): void
    {
        add_filter('rest_prepare_event', [$this, 'modify'], 10, 3);
    }

    public function modify(WP_REST_Response $response, WP_Post $post, WP_REST_Request $request): WP_REST_Response
    {
        if (!$this->shouldModify($request)) {
            return $response;
        }

        $schema = $this->postToSchemaAdapter->getSchema($post);
        return $this->wpService->restEnsureResponse($schema->toArray());
    }

    private function shouldModify(WP_REST_Request $request): bool
    {
        return $request->get_param('context') === self::CONTEXT;
    }
}
