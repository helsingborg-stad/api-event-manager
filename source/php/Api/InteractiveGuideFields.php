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
                'get_callback' => array($this, 'interactiveGuideStepsGetCallBack'),
                'schema' => array(
                    'description' => 'Field containing object value with interactive guide steps.',
                    'type' => 'string',
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
