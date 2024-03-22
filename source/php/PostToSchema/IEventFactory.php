<?php

namespace EventManager\PostToSchema;

use WP_Post;

interface IEventFactory
{
    public function create(WP_Post $post): IPostToSchemaAdapter;
}
