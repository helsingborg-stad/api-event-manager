<?php

namespace EventManager\AcfFieldContentModifiers;

use EventManager\AcfFieldContentModifiers\AcfSelectFieldContentModifier;

class FilterAcfOrganizerSelectField extends AcfSelectFieldContentModifier
{
    public function getFieldKey(): string {
        return 'field_65a4f6af50302';
    }
    public function getFieldValue(): array {
        return [
            'org-a' => 'Organization A',
            'org-b' => 'Organization B',
            'org-c' => 'Organization C',
        ];
    }
}
