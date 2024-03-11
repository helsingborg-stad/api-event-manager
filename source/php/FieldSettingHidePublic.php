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
    }

    public function addPublicFieldOption($field) {
        acf_render_field_setting( $field, array(
            'label'        => __('Display in frontend forms', 'api-event-manager'),
            'instructions' => 'Wheter to display this field in frontend forms or not.',
            'name'         => 'is_public',
            'type'         => 'true_false',
            'ui'           => 1,
        ), true );
    }
}
