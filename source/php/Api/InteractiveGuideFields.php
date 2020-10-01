<?php

namespace HbgEventImporter\Api;

class InteractiveGuideFields extends Fields
{
    private $postType = 'interactive_guide';

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'registerRestFields'));
    }

    /**
     * Register meta data fields for Interactive Guide rest endpoint
     *
     * @return void
     */
    public function registerRestFields()
    {
        register_rest_field(
            $this->postType,
            'featured_media',
            array(
                'get_callback' => array($this, 'featuredImageData'),
                'schema' => array(
                    'description' => 'Field containing object with featured image data.',
                    'type' => 'string',
                    'context' => array('view', 'edit'),
                ),
            )
        );

        register_rest_field(
            $this->postType,
            'guidegroup',
            array(
                'get_callback' => array($this, 'getTaxonomyCallback'),
                'schema' => array(
                    'type' => 'object',
                    'context' => array('view', 'embed')
                )
            )
        );

        register_rest_field(
            $this->postType,
            'steps',
            array(
                'get_callback' => array($this, 'interactiveGuideStepsGetCallBack'),
                'schema' => array(
                    'type' => 'object',
                    'description' => 'Field containing object value with interactive guide steps.',
                )
            )
        );
    }



    /**
     * Ads a better field name for step type
     *
     * @param   object  $object      The response object.
     * @param   string  $field_name  The name of the field to add.
     * @param   object  $request     The WP_REST_Request object.
     *
     * @return  object|null
     */
    public function interactiveGuideStepsGetCallBack($object, $fieldName, $request)
    {
        $stepMetaData = $this->objectGetCallBack($object, $fieldName, $request);
        if (!is_array($stepMetaData) || empty($stepMetaData)) {
            return $stepMetaData;
        }

        foreach ($stepMetaData as &$step) {
            $type = isset($step['acf_fc_layout']) ? array('type' => $step['acf_fc_layout']) : array('type' => null);
            $step = array_merge($type, $step);
            unset($step['acf_fc_layout']);
        }

        return $stepMetaData;
    }
}
