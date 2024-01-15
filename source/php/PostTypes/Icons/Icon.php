<?php

namespace EventManager\PostTypes\Icons;

class Icon
{
    private string $icon;

    public function __construct($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    public function getIcon(): string
    {
        $file = __DIR__ . "/{$this->icon}.svg";

        if (!file_exists($file)) {
            return '';
        }

        $iconString = file_get_contents($file);
        return 'data:image/svg+xml;base64,' . base64_encode($iconString);
    }
}
