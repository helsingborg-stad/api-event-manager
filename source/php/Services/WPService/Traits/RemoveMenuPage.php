<?php

namespace EventManager\Services\WPService\Traits;

trait RemoveMenuPage
{
    public function removeMenuPage(string $menuSlug): array|false
    {
        return remove_menu_page($menuSlug);
    }
}
