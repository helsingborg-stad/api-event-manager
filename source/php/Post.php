<?php

namespace HbgEventImporter;

class Post
{
    protected static function get($count, $metaQuery, $postStatus, $postType)
    {
        $args = array(
            'posts_per_page' => $count,
            'post_type'      => $postType,
            'orderby'        => 'date',
            'order'          => 'DESC'
        );

        if ($postStatus) {
            $args['post_status'] = $postStatus;
        }

        if (is_array($metaQuery)) {
            $args['meta_query'] = $metaQuery;
        }

        $posts = get_posts($args);

        if ($count == 1 && isset($posts[0])) {
            $posts = $posts[0];
        }

        return $posts;
    }
}
