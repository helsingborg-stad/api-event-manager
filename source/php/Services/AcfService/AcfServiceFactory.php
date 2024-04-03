<?php

namespace EventManager\Services\AcfService;

class AcfServiceFactory
{
    public static function create(): AcfService
    {
        return new class implements AcfService {
            public function getField(
                string $selector,
                int|false|string $postId = false,
                bool $formatValue = true,
                bool $escapeHtml = false
            ) {
                return get_field($selector, $postId, $formatValue, $escapeHtml);
            }

            public function getFields(mixed $postId = false, bool $formatValue = true, bool $escapeHtml = false): array
            {
                return get_fields($postId, $formatValue, $escapeHtml);
            }

            public function addOptionsPage(array $options): void
            {
                acf_add_options_page($options);
            }
        };
    }
}
