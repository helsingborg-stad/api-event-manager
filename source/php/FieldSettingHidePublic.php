<?php

namespace EventManager;

use EventManager\Helper\Hookable;
use EventManager\Services\WPService\WPService;

class FieldSettingHidePublic implements Hookable
{
    private WPService $wpService;

    public function __construct(WPService $wpService)
    {
        $this->wpService = $wpService;
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('acf/render_field_settings', [$this, 'addPublicFieldOption']);
        $this->wpService->addAction('acf/prepare_field', [$this, 'hideFieldFromFrontendForms']);
    }

    public function addPublicFieldOption($field) {
        acf_render_field_setting($field, array(
            'label'        => __('Hide in frontend forms', 'api-event-manager'),
            'instructions' => 'Wheter to display this field in frontend forms or not.',
            'name'         => 'is_publicly_hidden',
            'type'         => 'true_false',
            'ui'           => 1,
        ), true );
    }

    public function hideFieldFromFrontendForms($field) {


        //Set default
        if (!isset($field['is_publicly_hidden'])) {
            $field['is_publicly_hidden'] = 0;
        }

        // Do not hide fields in admin
        if(is_admin()) {
            return $field;
        }

        //Hide field from frontend forms
        if ($field['is_publicly_hidden'] == 1) {

            //Will generate offset error
            //return false;


            //Make a impossible statement to test if the field is hidden? 
            $field['conditional_logic'] = [
                [
                    "field" => "field_fake_field_id",
                    "operator" => "!=",
                    "value" => "fake_value"
                ]
            ];
            var_dump($field);

        }

        return $field;
    }
}
