<?php

namespace HbgEventImporter\Api;

/**
 * Adding meta fields to location post type
 */

class MembershipCardFields extends Fields
{
    private $postType = 'membership-card';

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'registerRestFields'));
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

        // Replace group id with taxonomy name
        register_rest_field($this->postType,
            'user_groups',
            array(
                'get_callback' => array($this, 'userGroups'),
                'update_callback' => array($this, 'objectUpdateCallBack'),
                'schema' => null
            )
        );

    }
}
