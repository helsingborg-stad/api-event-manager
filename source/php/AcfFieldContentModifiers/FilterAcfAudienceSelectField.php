<?php

namespace EventManager\AcfFieldContentModifiers;

use EventManager\AcfFieldContentModifiers\AcfSelectFieldContentModifier;

class FilterAcfAudienceSelectField extends AcfSelectFieldContentModifier
{
    public function getFieldKey(): string {
        return 'field_65a52a6374b0c';
    }
    public function getFieldValue(): array {
        return [
            'all' => 'All',
            'members' => 'Members',
            'non-members' => 'Non-Members',
        ];
    }
}
