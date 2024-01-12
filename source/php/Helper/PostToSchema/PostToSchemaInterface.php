<?php

namespace EventManager\Helper\PostToSchema;

use WP_Post;

interface PostToSchemaInterface
{
    public function transform(WP_Post $post): array;
}
