<?php

namespace EventManager\PostToSchema;

use Spatie\SchemaOrg\BaseType;
use WP_Post;

interface IPostToSchemaAdapter
{
    public function getSchema(WP_Post $post): BaseType;
}
