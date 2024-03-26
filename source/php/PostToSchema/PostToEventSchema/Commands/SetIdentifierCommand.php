<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use Spatie\SchemaOrg\BaseType;
use WP_Post;

class SetIdentifierCommand implements CommandInterface
{
    public function __construct(private BaseType $schema, private WP_Post $post)
    {
    }

    public function execute(): void
    {
        $this->schema->identifier($this->post->ID);
    }
}
