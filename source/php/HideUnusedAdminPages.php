<?php

namespace EventManager;

use EventManager\Helper\Hookable;
use EventManager\Services\WPService\WPService;

class HideUnusedAdminPages implements Hookable
{
    private WPService $wpService;

    public function __construct(WPService $wpService)
    {
        $this->wpService = $wpService;
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('admin_menu', [$this, 'hideUnusedAdminPages']);
    }

    public function hideUnusedAdminPages()
    {
        $this->wpService->removeMenuPage('edit.php');
        $this->wpService->removeMenuPage('edit.php?post_type=page');
        $this->wpService->removeMenuPage('link-manager.php');
        $this->wpService->removeMenuPage('edit-comments.php');
        $this->wpService->removeMenuPage('themes.php');
        $this->wpService->removeMenuPage('tools.php');
        $this->wpService->removeMenuPage('index.php');

        $this->wpService->removeSubMenuPage('options-general.php', 'options-discussion.php');
        $this->wpService->removeSubMenuPage('options-general.php', 'options-writing.php');
        $this->wpService->removeSubMenuPage('options-general.php', 'options-privacy.php');
    }
}