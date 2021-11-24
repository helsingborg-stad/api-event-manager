<?php

namespace HbgEventImporter\MiddleLayer;

class Guides extends \HbgEventImporter\MiddleLayer\SyncManager
{
    public $singularName = "guide";
    public $pluralName = "guides";

    public function __construct()
    {
        parent::__construct($this->singularName, $this->pluralName);

        if ($this->isCdnSyncEnabled) {
            add_action('save_post_' . $this->singularName, array($this, 'saveGuide'), 10, 3);
            add_action('delete_post', array($this, 'deleteGuide'), 10);
        }
    }

    public function saveGuide($postId, $post, $update)
    {
        if ($post->post_status === 'auto-draft') {
            return;
        }

        if ($post->post_status === 'publish') {
            $this->saveEmbeddedItem($postId);
            return;
        }

        $this->deleteItem($postId);
    }

    public function deleteGuide($postId)
    {
        $post = get_post($postId);
        if (empty($post) || $post->post_type !== $this->singularName) {
            return;
        }

        $this->deleteItem($postId);
    }

    public function getGuides($page = 1, $items = [])
    {
        $args = array(
                'paged' => $page,
                'posts_per_page' => 50,
                'post_type'   => $this->singularName,
                'post_status' => 'publish',
            );
        $query = new \WP_Query($args);

        if (!empty($query->posts)) {
            $items = array_merge($items, $query->posts);
        }

        if ($query->max_num_pages && (int)$page < (int)$query->max_num_pages) {
            return $this->getGuides(++$page, $items);
        }

        return $items;
    }

    public function startPopulate()
    {
        $posts = $this->getGuides();

        foreach ($posts as $key => $post) {
            $this->saveEmbeddedItem($post->ID);
        }
    }
}
