<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use EventManager\PostToSchema\IPostToSchemaAdapter;
use EventManager\Services\WPService\GetPostParent;
use Spatie\SchemaOrg\BaseType;

class SetSuperEventCommand implements CommandInterface
{
    public function __construct(
        private BaseType $schema,
        private int $postId,
        private GetPostParent $wpService,
        private IPostToSchemaAdapter $postToSchemaAdapter
    ) {
    }

    public function execute(): void
    {
        $superEventPost = $this->wpService->getPostParent($this->postId);


        if (!$superEventPost) {
            return;
        }

        $superEvent = $this->postToSchemaAdapter->getSchema($superEventPost);

        $this->schema->superEvent($superEvent);
    }
}
