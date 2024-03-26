<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use EventManager\PostToSchema\IPostToSchemaAdapter;
use EventManager\Services\AcfService\Functions\GetFields;
use EventManager\Services\WPService\GetPosts;
use Spatie\SchemaOrg\BaseType;

class SetSubEvents implements CommandInterface
{
    public function __construct(
        private BaseType $schema,
        private int $postId,
        private GetPosts $wpService,
        private GetFields $acfService,
        private IPostToSchemaAdapter $postToSchemaAdapter
    ) {
    }

    public function execute(): void
    {
        $subEventPosts = $this->wpService->getPosts([
            'post_parent' => $this->postId,
            'post_type'   => 'event',
            'numberposts' => -1
        ]);

        if (empty($subEventPosts)) {
            return;
        }

        $subEvents = array_map(function ($subPost) {
            $subEvent = $this->postToSchemaAdapter->getSchema($subPost);

            return $subEvent->toArray();
        }, $subEventPosts);

        $this->schema->subEvents($subEvents);
    }
}
