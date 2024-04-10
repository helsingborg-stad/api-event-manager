<?php

namespace EventManager;

use EventManager\Helper\Hookable;
use EventManager\Services\WPService\WPService;
use EventManager\Services\AcfService\AcfService;

class FieldSettingHidePublic implements Hookable
{
    public function __construct(private WPService $wpService, private AcfService $acfService){}

    public function addHooks(): void
    {
        $this->wpService->addAction('acf/render_field_settings', [$this, 'addPublicFieldOption']);
        $this->wpService->addAction('acf/prepare_field', [$this, 'hideFieldFromFrontendForms']);
    }

    public function addPublicFieldOption($field) {
        $this->acfService->renderFieldSetting($field, array(
                'label'        => __('Hide in frontend forms', 'api-event-manager'),
                'instructions' => 'Wheter to display this field in frontend forms or not.',
                'name'         => 'is_publicly_hidden',
                'type'         => 'true_false',
                'ui'           => 1,
            ),
            true
        );
    }

    public function hideFieldFromFrontendForms($field) {

        // Do not hide fields in admin
        if($this->wpService->isAdmin()) {
            return $field;
        }

        //Hide field from backend forms
        if (isset($field['is_publicly_hidden']) && $field['is_publicly_hidden'] == 1) {
            return false;
        }

        return $field;
    }
}
