<?php

namespace EventManager\Services\WPService;

interface RegisterPostType
{
    public function registerPostType(string $postType, array $args = []): void;
}
