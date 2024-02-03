<?php

namespace EventManager\Services\WPService\Traits;

trait RemoveSubMenuPage
{
    public function removeSubMenuPage(string $menuSlug, string $submenuSlug): array|false
    {
        return remove_submenu_page($menuSlug, $submenuSlug);
    }
}
