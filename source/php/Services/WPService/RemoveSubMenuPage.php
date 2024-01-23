<?php

namespace EventManager\Services\WPService;

interface RemoveSubMenuPage
{
    public function removeSubMenuPage(string $parentSlug, string $menuSlug): array|false;
}
