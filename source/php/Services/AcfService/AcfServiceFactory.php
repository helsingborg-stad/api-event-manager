<?php

namespace EventManager\Services\AcfService;

class AcfServiceFactory
{
    public static function create(): AcfService
    {
        return new class implements AcfService {
            public function getField(
                string $selector,
                int|false $postId = false,
                bool $formatValue = true,
                bool $escapeHtml = false
            ) {
                return get_field($selector, $postId, $formatValue, $escapeHtml);
            }
        };
    }
}
