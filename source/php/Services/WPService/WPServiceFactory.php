<?php

namespace EventManager\Services\WPService;

use WP_Error;
use WP_Post;
use WP_Term;

class WPServiceFactory
{
    public static function create(): WPService
    {
        return new class implements WPService {
            public function getPostMeta($postId, $key = '', $single = false): mixed
            {
                return get_post_meta($postId, $key, $single);
            }

            public function getPostParent(int|WP_Post|null $postId): ?WP_Post
            {
                $parent = get_post_parent($postId);

                if (is_a($parent, WP_Post::class)) {
                    return $parent;
                }

                return null;
            }

            public function getPosts(?array $args): array
            {
                return get_posts($args);
            }

            public function getThePostThumbnailUrl(
                int|WP_Post $postId,
                string|array $size = 'post-thumbnail'
            ): string|false {
                return get_the_post_thumbnail_url($postId, $size);
            }

            public function getPermalink(int|WP_Post $post = null, bool $leavename = false): string|false
            {
                return get_permalink($post, $leavename);
            }

            public function getTerm(
                int|object $term,
                string $taxonomy = '',
                string $output = OBJECT,
                string $filter = 'raw'
            ): WP_Term|array|WP_Error|null {
                return get_term($term, $taxonomy, $output, $filter);
            }

            public function getTermMeta(int $term_id, string $key = '', bool $single = false): mixed
            {
                return get_term_meta($term_id, $key, $single);
            }

            public function getTheTitle(int|WP_Post $post = 0): string
            {
                return get_the_title($post);
            }

            public function addAction(
                string $tag,
                callable $function_to_add,
                int $priority = 10,
                int $accepted_args = 1
            ): bool {
                return add_action($tag, $function_to_add, $priority, $accepted_args);
            }

            public function addFilter(
                string $tag,
                callable $function_to_add,
                int $priority = 10,
                int $accepted_args = 1
            ): bool {
                return add_filter($tag, $function_to_add, $priority, $accepted_args);
            }

            public function registerPostType(string $postType, array $args = []): void
            {
                register_post_type($postType, $args);
            }

            public function registerTaxonomy(string $taxonomy, array|string $objectType, array|string $args = []): void
            {
                register_taxonomy($taxonomy, $objectType, $args);
            }

            public function removeMenuPage(string $menuSlug): array|false
            {
                return remove_menu_page($menuSlug);
            }

            public function removeSubMenuPage(string $menuSlug, string $submenuSlug): array|false
            {
                return remove_submenu_page($menuSlug, $submenuSlug);
            }
        };
    }
}
