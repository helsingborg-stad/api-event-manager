<?php

namespace HbgEventImporter;

class PostTypes
{
    public function __construct()
    {
        // Removes media buttons for new posts
        add_action('admin_head', function () {
            remove_action('media_buttons', 'media_buttons');
        });

        add_filter('get_sample_permalink_html', array($this, 'replacePermalink'), 10, 5);

        // Init the post types
        new PostTypes\Events();
        new PostTypes\Locations();
        new PostTypes\Contacts();
        new PostTypes\Sponsors();
        new PostTypes\Packages();
        new PostTypes\MembershipCards();
        new PostTypes\Guides();
    }

    /**
     * Replaces permalink on edit post with API-url
     * @return string
     */
    public function replacePermalink($return, $post_id, $new_title, $new_slug, $post)
    {
        $postType = $post->post_type;
        $jsonUrl = home_url() . '/json/wp/v2/' . $postType . '/';
        $apiUrl = $jsonUrl . $post_id;

        return '<strong>' . __('API-url', 'event-manager').':</strong> <a href="' . $apiUrl . '" target="_blank">' . $apiUrl . '</a>';
    }
}
