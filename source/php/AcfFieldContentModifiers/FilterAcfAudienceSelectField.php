<?php

namespace EventManager\AcfFieldContentModifiers;

use EventManager\AcfFieldContentModifiers\AcfSelectFieldContentModifier;

class FilterAcfAudienceSelectField extends AcfSelectFieldContentModifier
{
    public function getFieldKey(): string {
        return 'field_65a52a6374b0c';
    }
    public function getFieldValue(): array {
        $terms = get_terms( array(
            'taxonomy'   => 'audience',
            'hide_empty' => false,
            'fields'     => 'id=>name'
        ));

        if (is_wp_error($terms)) {
            return [];
        }

        if (empty($terms)) {
            return [];
        }
        
        return $terms;
    }
}
