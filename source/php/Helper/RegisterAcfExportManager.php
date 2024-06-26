<?php

namespace EventManager\Helper;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\AddAction;

class RegisterAcfExportManager implements Hookable
{
    public function __construct(private AddAction $wpService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('acf/init', array($this, 'registerAcfExportManager'));
    }

    public function registerAcfExportManager(): void
    {
        $path = defined('EVENT_MANAGER_PATH') ? constant('EVENT_MANAGER_PATH') . 'source/php/AcfFields/' : '';

        $acfExportManager = new \AcfExportManager\AcfExportManager();
        $acfExportManager->setTextdomain('api-event-manager');
        $acfExportManager->setExportFolder($path);
        $acfExportManager->autoExport(array(
            'event-fields'        => 'group_65a115157a046',
            'organization-fields' => 'group_65a4f5a847d62',
            'audience-fields'     => 'group_65ae1b865887a',
            'plugin-settings'     => 'group_660cec468b833',
            'user-fields'         => 'group_660d1667d32a0',
            'test-field-step-1'   => 'group_661e41bb1781f',
            'test-field-step-2'   => 'group_661e425070deb',
            'multistep-form'      => 'group_6627a5e16d84f',
        ));

        $acfExportManager->import();
    }
}
