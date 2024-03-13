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

            public function renderFieldSetting(array $field, array $configuration, bool $global = false): void
            {
                acf_render_field_setting($field, $configuration, $global);
            }
        };
    }
    
}
