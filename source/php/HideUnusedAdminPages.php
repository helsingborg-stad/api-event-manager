<?php

namespace EventManager;

use EventManager\Helper\Hookable;

class HideUnusedAdminPages implements Hookable
{
    public function addHooks(): void
    {
        add_action('admin_menu', [$this, 'hideUnusedAdminPages']);
    }

    public function hideUnusedAdminPages()
    {
        remove_menu_page('edit.php');
        remove_menu_page('edit.php?post_type=page');
        remove_menu_page('link-manager.php');
        remove_menu_page('edit-comments.php');
        remove_menu_page('themes.php');
        remove_menu_page('tools.php');
        remove_menu_page('index.php');

        remove_submenu_page('options-general.php', 'options-discussion.php');
        remove_submenu_page('options-general.php', 'options-writing.php');
        remove_submenu_page('options-general.php', 'options-privacy.php');
    }
}
