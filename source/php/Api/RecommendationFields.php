<?php

namespace HbgEventImporter\Api;

/**
 * Adding meta fields to location post type
 */

class RecommendationFields extends Fields
{
    private $postType = 'recommendation';

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'registerRestFields'));
    }

    /**
     * Register rest fields to consumer api
     * @return  void
     * @version 0.3.2 creating consumer accessable meta values.
     */
    public function registerRestFields()
    {
        // Title as plain text
        register_rest_field($this->postType,
            'title',
            array(
                'get_callback' => array($this, 'addPlaintextField'),
                'update_callback' => null,
                'schema' => null,
            )
        );

        // Content as plain text
        register_rest_field($this->postType,
            'content',
            array(
                'get_callback' => array($this, 'addPlaintextField'),
                'update_callback' => null,
                'schema' => null,
            )
        );

        // Navigation taxonomy
        register_rest_field($this->postType,
            'navigation',
            array(
                'get_callback' => array($this, 'getTaxonomyCallback'),
                'update_callback' => null,
                'schema' => null
            )
        );

        // Guide type taxonomy
        register_rest_field($this->postType,
            'guidetype',
            array(
                'get_callback' => array($this, 'getTaxonomyCallback'),
                'update_callback' => null,
                'schema' => null
            )
        );

        // Point Property taxonomy
        register_rest_field($this->postType,
            'property',
            array(
                'get_callback' => array($this, 'getTaxonomyCallback'),
                'update_callback' => null,
                'schema' => null
            )
        );

        // User groups
        register_rest_field($this->postType,
            'user_groups',
            array(
                'get_callback' => array($this, 'getTaxonomyCallback'),
                'update_callback' => null,
                'schema' => null
            )
        );

        /* General tab */

        // Tagline
        register_rest_field($this->postType,
            'tagline',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => null,
                'schema' => null
            )
        );

        // Location for the recommendation
        register_rest_field($this->postType,
            'location',
            array(
                'get_callback' => array($this, 'locationData'),
                'update_callback' => null,
                'schema' => null
            )
        );

        // Child friendly
        register_rest_field($this->postType,
            'child_friendly',
            array(
                'get_callback' => array($this, 'boolGetCallBack'),
                'update_callback' => null,
                'schema' => null
            )
        );

        // Child friendly
        register_rest_field($this->postType,
            'links',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => null,
                'schema' => null
            )
        );

        /* Profile tab */

        // Profile name
        register_rest_field($this->postType,
            'profile_name',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => null,
                'schema' => null
            )
        );

        // Profile quote
        register_rest_field($this->postType,
            'profile_quote',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => null,
                'schema' => null
            )
        );

        // Profile image
        register_rest_field($this->postType,
            'profile_image',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => null,
                'schema' => null
            )
        );

        /* Media tab */

        // Gallery
        register_rest_field($this->postType,
            'gallery',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => null,
                'schema' => null
            )
        );

        // Audio
        register_rest_field($this->postType,
            'audio',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => null,
                'schema' => null
            )
        );

        // Video
        register_rest_field($this->postType,
            'video',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'update_callback' => null,
                'schema' => null
            )
        );

        /* Beacon tab */

        register_rest_field($this->postType,
            'beacon_namespace',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => null,
                'schema' => null
            )
        );

        register_rest_field($this->postType,
            'beacon_id',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => null,
                'schema' => null
            )
        );

        register_rest_field($this->postType,
            'beacon_distance',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'update_callback' => null,
                'schema' => null
            )
        );

        register_rest_field($this->postType,
            'beacon_location',
            array(
                'get_callback' => array($this, 'locationData'),
                'update_callback' => null,
                'schema' => null
            )
        );
    }
}
