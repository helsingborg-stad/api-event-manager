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
            'event-fields'         => 'group_65a115157a046',
            'organization-fields'  => 'group_65a4f5a847d62', //Backend only

            'plugin-settings'      => 'group_660cec468b833',
            'user-fields'          => 'group_660d1667d32a0',

            'multistep-form'       => 'group_6627a5e16d84f', //Module

            'price-booking-fields' => 'group_66436ffb2f075',
            'contact-fields'       => 'group_66436b29cfb4f',
            'descriptions-fields'  => 'group_66436bf782af1',
            'location-time-fields' => 'group_661e425070deb',
            'audience-fields'      => 'group_66436fb1b4f7f',
            'category-fields'      => 'group_66436fdd0f112',
        ));

        $acfExportManager->import();
    }
}
