<?php

namespace EventManager\Helper;

use WpService\WpService;

class RegisterAcfExportManager implements Hookable
{
    public function __construct(private WPService $wpService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('acf/init', array($this, 'registerAcfExportManager'));
    }

    public function registerAcfExportManager(): void
    {
        $acfExportManager = new \AcfExportManager\AcfExportManager();
        $acfExportManager->setTextdomain('api-event-manager');
        $acfExportManager->setExportFolder(EVENT_MANAGER_PATH . 'source/php/AcfFields/');
        $acfExportManager->autoExport(array(
            'event-fields'        => 'group_65a115157a046',
            'organization-fields' => 'group_65a4f5a847d62',
            'audience-fields'     => 'group_65ae1b865887a',
            'plugin-settings'     => 'group_660cec468b833',
            'user-fields'         => 'group_660d1667d32a0'
        ));

        $acfExportManager->import();
    }
}
