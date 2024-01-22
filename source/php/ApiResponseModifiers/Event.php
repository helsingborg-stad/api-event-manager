<?php

namespace EventManager\ApiResponseModifiers;

use EventManager\Helper\Hookable;
use EventManager\Helper\PostToSchema\PostToEventSchema;
use EventManager\Helper\PostToSchema\PostToSchemaInterface;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;

class Event implements Hookable
{
    protected string $targetContext = 'schema';
    protected PostToSchemaInterface $postToSchema;

    public function addHooks(): void
    {
        add_filter('rest_prepare_event', [$this, 'modify'], 10, 3);
    }

    public function modify(WP_REST_Response $response, WP_Post $post, WP_REST_Request $request): WP_REST_Response
    {
        if (!$this->shouldModify($request)) {
            return $response;
        }

        $wp          = \EventManager\Services\WPService\WPServiceFactory::create();
        $eventSchema = new PostToEventSchema($wp, $post);
        return rest_ensure_response($eventSchema->toArray());
    }

    private function shouldModify(WP_REST_Request $request): bool
    {
        return $request->get_param('context') === $this->targetContext;
    }
}
