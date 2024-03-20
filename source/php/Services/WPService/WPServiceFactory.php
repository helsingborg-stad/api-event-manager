<?php

namespace EventManager\Services\WPService;

use EventManager\Resolvers\FileSystem\FilePathResolverInterface;
use EventManager\Resolvers\FileSystem\NullFilePathResolver;
use WP_Error;
use WP_Post;
use WP_REST_Response;
use WP_Screen;
use WP_Term;

class WPServiceFactory
{
    public static function create(?FilePathResolverInterface $filePathResolver = new NullFilePathResolver()): WPService
    {
        return new class ($filePathResolver) implements WPService {
            public function __construct(private FilePathResolverInterface $filePathResolver){}

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

            public function loadPluginTextDomain(string $domain, string $path, string $relativeTo): void
            {
                load_plugin_textdomain($domain, $path, $relativeTo);
            }

            public function getTheId(): int|false
            {
                return get_the_ID();
            }

            public function getEditTermLink(int|WP_Term $term, string $taxonomy = '', string $objectType = ''): ?string
            {
                return get_edit_term_link($term, $taxonomy, $objectType);
            }

            public function restEnsureResponse($response): WP_REST_Response|WP_Error
            {
                return rest_ensure_response($response);
            }

            public function getChildren(mixed $args = '', string $output = OBJECT): array
            {
                return get_children($args, $output);
            }

            public function deletePost(int $postId, bool $forceDelete): void
            {
                wp_delete_post($postId, $forceDelete);
            }

            public function adminNotice(string $message, array $args): void
            {
                wp_admin_notice($message, $args);
            }

            public function getCurrentScreen(): ?WP_Screen
            {
                return get_current_screen();
            }

            public function nextScheduled(string $hook, array $args = []): int|false
            {
                return wp_next_scheduled($hook, $args);
            }

            public function scheduleEvent(
                int $timestamp,
                string $recurrence,
                string $hook,
                array $args = [],
                bool $wpError = false
            ): bool|WP_Error {
                return wp_schedule_event($timestamp, $recurrence, $hook, $args, $wpError);
            }

            public function enqueueStyle(
                string $handle
            ): void {
                //TODO: Check if the handle is registered before enqueue. Throw error if not.
                wp_enqueue_style($handle);
            }

            public function enqueueScript(
                string $handle
            ): void {
                //TODO: Check if the handle is registered before enqueue. Throw error if not.
                wp_enqueue_script($handle);
            }

            public function registerStyle(
                string $handle, 
                string $src = '', 
                array $deps = array(), 
                string|bool|null $ver = false, 
                string $media = 'all'
            ): void {
                $src = $this->filePathResolver->resolveToUrl($src);
                wp_register_style($handle, $src, $deps, $ver, $media);
            }

            public function registerScript(
                string $handle, 
                string $src = '', 
                array $deps = array(), 
                string|bool|null $ver = false, 
                bool $in_footer = true
            ): void {
                $src = $this->filePathResolver->resolveToUrl($src) ?? $src;
                wp_register_script($handle, $src, $deps, $ver, $in_footer);
            }

            public function isAdmin(): bool
            {
                return is_admin();
            }

            public function getEnvironmentType(): string
            {
                return wp_get_environment_type();
            }

            public function pluginDirPath(string $file): string
            {
                return plugin_dir_path($file);
            }

            public function pluginsUrl(string $path, string $plugin):string
            {
                return plugins_url($path, $plugin);
            }
        };
    }
}
