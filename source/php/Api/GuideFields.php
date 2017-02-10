<?php

namespace HbgEventImporter\Api;

/**
 * Adding meta fields to location post type
 */

class GuideFields extends Fields
{
    private $postType = 'guide';

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

        // Replace category id with taxonomy name
        register_rest_field($this->postType,
            'location_categories',
            array(
                'get_callback' => array($this, 'renameTaxonomies'),
                'schema' => array(
                    'description' => 'Field containing object with taxonomies.',
                    'type' => 'object',
                    'context' => array('view')
                )
            )
        );

    }
}
