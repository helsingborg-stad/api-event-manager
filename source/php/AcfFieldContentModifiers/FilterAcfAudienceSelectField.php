<?php

namespace EventManager\AcfFieldContentModifiers;

use WpService\Contracts\GetTerms;

class FilterAcfAudienceSelectField implements IAcfFieldContentModifier
{
    public function __construct(private string $fieldKey, private GetTerms $wpService)
    {
    }

    public function getFieldKey(): string
    {
        return $this->fieldKey;
    }

    public function modifyFieldContent(array $field): array
    {
        $terms = $this->wpService->getTerms(array(
            'taxonomy'   => 'audience',
            'hide_empty' => false,
            'fields'     => 'id=>name'
        ));

        if (is_array($terms) && !empty($terms)) {
            $field['choices'] = $terms;
        }

        return $field;
    }
}
