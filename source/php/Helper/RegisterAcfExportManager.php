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
        $this->wpService->addAction('init', array($this, 'registerAcfExportManager'));
    }

    public function registerAcfExportManager(): void
    {
        $path = defined('EVENT_MANAGER_PATH') ? constant('EVENT_MANAGER_PATH') . 'source/php/AcfFields/' : '';

        $acfExportManager = new \AcfExportManager\AcfExportManager();
        $acfExportManager->setTextdomain('api-event-manager');
        $acfExportManager->setExportFolder($path);
        $acfExportManager->autoExport(array(
            'event-organizer-fields'    => 'group_68d28e231bbaa',
            'event-presentation-fields' => 'group_68d28e8bc6408',
            'event-time-fields'         => 'group_68d28f0c82ff4',
            'event-place-fields'        => 'group_696e24e564997',
            'event-prices-fields'       => 'group_6970c90e6e6ba',
            'event-audience-category'   => 'group_68d28f55ef8cf',
            'organization-fields'       => 'group_65a4f5a847d62',
            'plugin-settings'           => 'group_660cec468b833',
            'user-fields'               => 'group_660d1667d32a0',
        ));

        $acfExportManager->import();
    }
}
