<?php

namespace EventManager\Services\AcfService\Functions;

interface GetField
{
    public function getField(
        string $selector,
        int|false $postId = false,
        bool $formatValue = true,
        bool $escapeHtml = false
    );
}
