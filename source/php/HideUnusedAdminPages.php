<?php

namespace EventManager;

use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\AddAction;
use WpService\Contracts\WpGetEnvironmentType;
use WpService\Contracts\RemoveMenuPage;
use WpService\Contracts\RemoveSubmenuPage;

class HideUnusedAdminPages implements Hookable
{
    public function __construct(private AddAction&WpGetEnvironmentType&RemoveMenuPage&RemoveSubmenuPage $wpService)
    {
        $this->wpService = $wpService;
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('admin_menu', [$this, 'hideUnusedAdminPages']);
    }

    public function hideUnusedAdminPages()
    {
        //Do not hide admin pages on local environment
        if ($this->wpService->WpGetEnvironmentType() === 'local') {
            return;
        }

        //Hide unused admin pages
        $this->wpService->removeMenuPage('edit.php');
        $this->wpService->removeMenuPage('edit.php?post_type=page');
        $this->wpService->removeMenuPage('link-manager.php');
        $this->wpService->removeMenuPage('edit-comments.php');
        $this->wpService->removeMenuPage('themes.php');
        $this->wpService->removeMenuPage('index.php');

        $this->wpService->removeSubmenuPage('options-general.php', 'options-discussion.php');
        $this->wpService->removeSubmenuPage('options-general.php', 'options-writing.php');
        $this->wpService->removeSubmenuPage('options-general.php', 'options-privacy.php');
    }
}
