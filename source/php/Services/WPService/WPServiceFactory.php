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

            public function applyFilters(string $hook_name, mixed $value, mixed $args): mixed
            {
                return apply_filters($hook_name, $value, $args);
            }

            public function deleteTerm(int $term, string $taxonomy, array|string $args = array()): bool|int|WP_Error
            {
                return wp_delete_term($term, $taxonomy, $args);
            }
            public function getPermalink(null|int|WP_Post $post = null, bool $leavename = false): string|false
            {
                return get_permalink($post, $leavename);
            }

            public function getPost(
                null|int|WP_Post $post = null,
                string $output = OBJECT,
                string $filter = "raw"
            ): WP_Post|array|null {
                return get_post($post, $output, $filter);
            }

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

            public function getTerms(array|string $args = array(), array|string $deprecated = ""): array|string|WP_Error
            {
                return get_terms($args, $deprecated);
            }

            public function getThePostThumbnailUrl(
                int|WP_Post $postId,
                string|array $size = 'post-thumbnail'
            ): string|false {
                return get_the_post_thumbnail_url($postId, $size);
            }

            public function getTheTitle(int|WP_Post $post = 0): string
            {
                return get_the_title($post);
            }

            public function isWPError(mixed $thing): bool
            {
                return is_wp_error($thing);
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

            public function termExists(int|string $term, string $taxonomy = "", ?int $parentTerm = null): null|int|array
            {
                return term_exists($term, $taxonomy, $parentTerm);
            }

            public function getPostTerms(
                int $post_id,
                string|array $taxonomy = 'post_tag',
                array $args = array()
            ): array|WP_Error {
                return wp_get_post_terms($post_id, $taxonomy, $args);
            }

            public function insertTerm(
                string $term,
                string $taxonomy = "",
                array $args = []
            ): array|WP_Error {
                return wp_insert_term($term, $taxonomy, $args);
            }

            public function setPostTerms(
                int $postId,
                string|array $terms = "",
                string $taxonomy = "post_tag",
                bool $append = false
            ): array|false|WP_Error {
                return wp_set_post_terms($postId, $terms, $taxonomy, $append);
            }

            public function enqueueStyle(
                string $handle, 
                string $src = '', 
                array $deps = array(), 
                string|bool|null $ver = false, 
                string $media = 'all'
            ): void {
                wp_enqueue_style($handle, $src, $deps, $ver, $media);
            }

            public function enqueueScript(
                string $handle, 
                string $src = '', 
                array $deps = array(), 
                string|bool|null $ver = false, 
                array|bool $args = array(),
                bool $in_footer = false
            ): void {
                wp_enqueue_script($handle, $src, $deps, $ver, $in_footer);
            }

            public function isAdmin(): bool
            {
                return is_admin();
            }

            public function getEnvironmentType(): string
            {
                return wp_get_environment_type();
            }
        };
    }
}
