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
            'event-organizer-fields'    => 'group_68d28e231bbaa',
            'event-presentation-fields' => 'group_68d28e8bc6408',
            'event-time-place-fields'   => 'group_68d28f0c82ff4',
            'event-audience-category'   => 'group_68d28f55ef8cf',
            'plugin-settings'           => 'group_660cec468b833',
            'user-fields'               => 'group_660d1667d32a0',
        ));

        $acfExportManager->import();
    }
}
