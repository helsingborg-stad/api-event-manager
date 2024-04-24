<?php

namespace EventManager\Settings;

use EventManager\Helper\Hookable;
use AcfService\Contracts\AddOptionsPage;
use WpService\Contracts\AddAction;

class AdminSettingsPage implements Hookable
{
    public function __construct(private AddAction $wpService, private AddOptionsPage $acfService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('acf/init', [$this, 'registerSettingsPage']);
    }

    public function registerSettingsPage(): void
    {
        $this->acfService->addOptionsPage(array(
            'menu_slug'       => 'event-manager-settings',
            'page_title'      => 'Event Manager Settings',
            'active'          => true,
            'menu_title'      => 'Event Manager',
            'capability'      => 'administrator',
            'parent_slug'     => 'options-general.php',
            'position'        => '',
            'icon_url'        => '',
            'redirect'        => true,
            'post_id'         => 'options',
            'autoload'        => false,
            'update_button'   => 'Update',
            'updated_message' => 'Settings updated',
        ));
    }
}
