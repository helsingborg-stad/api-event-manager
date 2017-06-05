<?php

namespace HbgEventImporter\Api;

/**
 * Adding meta fields to organizer post type
 */

class OrganizerFields extends Fields
{
    private $postType = 'organizer';

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'registerRestFields'));
        add_action('rest_api_init', array($this, 'registerRestRoute'));
    }

    public static function registerRestRoute()
    {
        $response = register_rest_route('wp/v2', '/'.$this->postType.'/'.'complete', array(
            'methods'  => \WP_REST_Server::READABLE,
            'callback' => array($this, 'getAllOrganizers'),
        ));
    }

    /**
     * End point to get all organizers, with id and title
     * @return WP_REST_Response / WP_Error
     */
    public function getAllOrganizers($request)
    {
        global $wpdb;

        $post_status = 'publish';
        $query =
        "
        SELECT      ID as id, post_title as title
        FROM        $wpdb->posts
        WHERE       $wpdb->posts.post_type = %s
                    AND $wpdb->posts.post_status = %s
        ORDER BY post_title ASC
        ";

        $completeQuery = $wpdb->prepare($query, $this->postType, $post_status);
        $allOrganizers = $wpdb->get_results($completeQuery);

        if (empty($allOrganizers)) {
            return new \WP_Error('Error', 'There are no Organizers', array( 'status' => 404 ));
        } else {
            return new \WP_REST_Response($allOrganizers, 200);
        }
    }

    /**
     * Register rest fields to consumer api
     * @return  void
     * @version 0.3.2 creating consumer accessable meta values.
     */
    public static function registerRestFields()
    {
        // Title as plain text
        register_rest_field($this->postType,
            'title',
            array(
                'get_callback'    => array($this, 'addPlaintextField'),
                'update_callback' => null,
                'schema'          => null,
            )
        );

        // Content as plain text
        register_rest_field($this->postType,
            'content',
            array(
                'get_callback'    => array($this, 'addPlaintextField'),
                'update_callback' => null,
                'schema'          => null,
            )
        );

        // Add more data to Featured Media field
        register_rest_field($this->postType,
            'featured_media',
            array(
                'get_callback' => array($this, 'featuredImageData'),
                'schema' => array(
                    'description' => 'Field containing object with featured image data.',
                    'type' => 'object',
                    'context' => array('view', 'edit')
                )
            )
        );

        // Replace group id with taxonomy name
        register_rest_field($this->postType,
            'user_groups',
            array(
                'get_callback' => array($this, 'getTaxonomyCallback'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => null
            )
        );

        // Phone number
        register_rest_field($this->postType,
            'phone',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field contianing string value with contact phone number.',
                    'type' => 'string',
                    'context' => array('view', 'edit', 'embed')
                )
            )
        );

        // Email
        register_rest_field($this->postType,
            'email',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => array($this, 'stringUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field contianing string value with contact email.',
                    'type' => 'string',
                    'context' => array('view', 'edit', 'embed')
                )
            )
        );

        // Contact persons
        register_rest_field($this->postType,
            'contact_persons',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => array(
                    'description' => 'Field containing array with contact persons.',
                    'type' => 'object',
                    'context' => array('view', 'edit')
                )
            )
        );

    }
}
