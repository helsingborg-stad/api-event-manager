<?php

namespace EventManager\AcfFieldContentModifiers;

use EventManager\AcfFieldContentModifiers\AcfSelectFieldContentModifier;

class FilterAcfOrganizerSelectField extends AcfSelectFieldContentModifier
{
    public function getFieldKey(): string {
        return 'field_65a4f6af50302';
    }
    public function getFieldValue(): array {
        $terms = get_terms([
            'taxonomy'   => 'organization',
            'hide_empty' => false,
            'fields'     => 'id=>name'
        ]);

        if (is_wp_error($terms)) {
            return [];
        }

        if (empty($terms)) {
            return [];
        }

        return $terms;
    }
}
