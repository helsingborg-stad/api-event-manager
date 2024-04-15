<?php

namespace EventManager;

use EventManager\Helper\Hookable;
use EventManager\Services\WPService\WPService;
use EventManager\Services\AcfService\AcfService;

class FieldSettingHidePrivate implements Hookable
{
    public function __construct(private WPService $wpService, private AcfService $acfService){}

    public function addHooks(): void
    {
        $this->wpService->addAction('acf/render_field_settings', [$this, 'addPublicFieldOption']);
        $this->wpService->addAction('acf/prepare_field', [$this, 'hideFieldFromBackendForms']);
    }

    public function addPublicFieldOption($field) {
        $this->acfService->renderFieldSetting($field, array(
                'label'        => __('Hide in backend forms', 'api-event-manager'),
                'instructions' => 'Wheter to display this field in backend forms or not.',
                'name'         => 'is_privately_hidden',
                'type'         => 'true_false',
                'ui'           => 1,
            ),
            true
        );
    }

    public function hideFieldFromBackendForms($field) {

        // Do not hide fields publicly
        if(!$this->wpService->isAdmin()) {
            return $field;
        }

        //Hide field from backend forms
        if (isset($field['is_privately_hidden']) && $field['is_privately_hidden'] == 1) {
            return false;
        }

        return $field;
    }
}
