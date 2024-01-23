<?php

namespace EventManager\Services\WPService;

interface RemoveMenuPage
{
    public function removeMenuPage(string $menuSlug): array|false;
}
