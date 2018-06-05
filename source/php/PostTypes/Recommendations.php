<?php

namespace HbgEventImporter\PostTypes;

class Recommendations extends \HbgEventImporter\Entity\CustomPostType
{
    public static $postTypeSlug = 'recommendation';

    public function __construct()
    {
        parent::__construct(
            __('Recommendations', 'event-manager'),
            __('Recommendation', 'event-manager'),
            self::$postTypeSlug,
            array(
                'description'          => 'Guided tours with beacon information',
                'menu_icon'            => 'dashicons-lightbulb',
                'public'               => true,
                'publicly_queriable'   => true,
                'show_ui'              => true,
                'show_in_nav_menus'    => true,
                'has_archive'          => true,
                'rewrite'              => array(
                    'slug'       => self::$postTypeSlug,
                    'with_front' => false
                ),
                'hierarchical'         => false,
                'exclude_from_search'  => true,
                'supports'             => array('title', 'revisions', 'editor'),
                'map_meta_cap'         => true,
                'capability_type'      => self::$postTypeSlug,
            )
        );

        $this->addTableColumn('cb', '<input type="checkbox">');
        $this->addTableColumn('title', __('Title', 'event-manager'));
        $this->addTableColumn('profile', __('Profile', 'event-manager'), true, function ($column, $postId) {
            echo get_post_meta($postId, 'profile_name', true) ? get_post_meta($postId, 'profile_name', true) :  '';
        });
        $this->addTableColumn('date', __('Date', 'event-manager'));

        add_action('save_post_' . self::$postTypeSlug, array($this, 'updateTaxonomies'), 11, 3);
    }

    /**
     * Set default post meta
     * @param int $postId The post ID.
     * @param post $post The post object.
     * @param bool $update Whether this is an existing post being updated or not.
     */
    public function updateTaxonomies($postId, $post, $update)
    {
        if (!$update) {
            wp_set_post_terms($postId, array('Recommendation'), 'guidetype', false);
        }
    }
}
