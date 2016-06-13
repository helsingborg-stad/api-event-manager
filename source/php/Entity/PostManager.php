<?php

namespace HbgEventImporter\Entity;

abstract class PostManager
{
    public $postType = null;
    public $postStatus = 'publish';

    /**
     * Keys that counts as post object properties
     * Any other key will be treated as meta properties
     * @var array
     */
    protected $postFields = array(
        'ID',
        'post_author',
        'post_date',
        'post_date_gmt',
        'post_content',
        'post_content_filtered',
        'post_title',
        'post_excerpt',
        'post_status',
        'comment_status',
        'ping_status',
        'post_password',
        'post_name',
        'to_ping',
        'pinged',
        'post_modified',
        'post_modified_gmt',
        'post_parent',
        'menu_order',
        'post_mime_type',
        'guid'
    );

    /**
     * Keys that will not be added as properties
     * @var array
     */
    protected $forbiddenKeys = array(
        'post_type'
    );

    /**
     * Constructor
     * @param array $postData Post object fields as array
     * @param array $metaData Post meta as array
     */
    public function __construct($postData = array(), $metaData = array())
    {
        if (is_null($this->postType)) {
            throw new \Exception('You need to specify a post type by setting the class property $postType');
            exit;
        }

        // Add post data as separate object parameters
        foreach ($postData as $key => $value) {
            if (in_array($key, $this->forbiddenKeys)) {
                continue;
            }

            $this->{$key} = $value;
        }

        // Add meta data as separate object parameters
        foreach ($metaData as $key => $value) {
            if (in_array($key, $this->forbiddenKeys)) {
                continue;
            }

            $this->{$key} = $value;
        }
    }

    public static function get($count, $metaQuery, $postType, $postStatus = 'publish')
    {
        $args = array(
            'posts_per_page' => $count,
            'post_type'      => $postType,
            'orderby'        => 'date',
            'order'          => 'DESC'
        );

        $args['post_status'] = (array)$postStatus;

        if (is_array($metaQuery)) {
            $args['meta_query'] = $metaQuery;
        }

        $posts = get_posts($args);

        if ($count == 1 && isset($posts[0])) {
            $posts = $posts[0];
        }

        return $posts;
    }

    /**
     * Saves the event and it's data
     * @return integer The inserted/updated post id
     */
    public function save()
    {
        $data = array_filter(get_object_vars($this), function ($item) {
            return !in_array($item, array(
                'postFields',
                'postType',
                'postStatus',
                'forbiddenKeys'
            ));
        }, ARRAY_FILTER_USE_KEY);

        $post = array();
        $meta = array();

        foreach ($data as $key => $value) {
            if (in_array($key, $this->postFields)) {
                $post[$key] = $value;
                continue;
            }

            $meta[$key] = $value;
        }

        $post['post_type'] = $this->postType;
        $post['post_status'] = $this->postStatus;
        $post['meta_input'] = $meta;

        // Check if duplicate by matching "_event_manager_uid" meta value
        $duplicate = self::get(
            1,
            array(
                'relation' => 'OR',
                array(
                    'key' => '_event_manager_uid',
                    'value' => $meta['_event_manager_uid'],
                    'compare' => '='
                )
            ),
            $this->postType
        );

        // Update if duplicate
        if (isset($duplicate->ID)) {
            $post['ID'] = $duplicate->ID;
            return wp_update_post($post);
        }

        // Create if not duplicate
        return wp_insert_post($post);
    }
}
