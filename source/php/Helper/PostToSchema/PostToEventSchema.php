<?php

namespace EventManager\Helper\PostToSchema;

use WP_Post;

class PostToEventSchema implements PostToSchemaInterface
{
    public function transform(WP_Post $post): array
    {
        $event = new \Spatie\SchemaOrg\Event();
        $event->identifier($post->ID);
        $event->name($post->post_title);
        $event->description($post->post_content);
        $event->image(get_the_post_thumbnail_url($post->ID) ?: null);
        $event->url(get_permalink($post->ID));

        return $event->toArray();
    }
}
