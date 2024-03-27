<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use EventManager\Services\WPService\GetThePostThumbnailUrl;
use Spatie\SchemaOrg\BaseType;

class SetImage implements CommandInterface
{
    public function __construct(
        private BaseType $schema,
        private int $postId,
        private GetThePostThumbnailUrl $wpService
    ) {
    }

    public function execute(): void
    {
        $this->schema->image($this->wpService->getThePostThumbnailUrl($this->postId) ?: null);
    }
}
