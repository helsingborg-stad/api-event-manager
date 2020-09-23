<?php

namespace HbgEventImporter\Api;

class InteractiveGuideFields extends Fields
{
    private $postType = 'interactive-guide';

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'registerRestFields'));
    }

    public function registerRestFields()
    {
        register_rest_field(
            $this->postType,
            'open_guide_title',
            array(
                'get_callback' => array($this, 'stringGetCallBack'),
                'schema' => array(
                    'description' => 'Field containing string value with "start guide label".',
                    'type' => 'string',
                )
            )
        );

        register_rest_field(
            $this->postType,
            'steps',
            array(
                'get_callback' => array($this, 'objectGetCallBack'),
                'schema' => array(
                    'description' => 'Field containing object value with interactive guide steps.',
                    'type' => 'string',
                )
            )
        );
    }
}
