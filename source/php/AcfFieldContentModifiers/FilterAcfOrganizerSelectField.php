<?php

namespace EventManager\AcfFieldContentModifiers;

use AcfService\Contracts\GetField;
use WpService\Contracts\WpGetCurrentUser;
use WpService\Contracts\GetTerms;

class FilterAcfOrganizerSelectField implements IAcfFieldContentModifier
{
    private const TAXONOMY      = 'organization';
    private const USER_META_KEY = 'organizations';

    public function __construct(
        private string $fieldKey,
        private GetTerms&WpGetCurrentUser $wpService,
        private GetField $acfService
    ) {
    }

    public function getFieldKey(): string
    {
        return $this->fieldKey;
    }

    public function modifyFieldContent(array $field): array
    {
        $userOrganizations = $this->getUserOrganizations();

        $terms = $this->wpService->getTerms([
            'taxonomy'   => self::TAXONOMY,
            'hide_empty' => false,
            'fields'     => 'id=>name',
            'include'    => $userOrganizations
        ]);

        if (is_array($terms) && !empty($terms)) {
            $field['choices'] = $terms;
        }

        return $field;
    }

    public function getUserOrganizations(): array
    {
        $user          = $this->wpService->wpGetCurrentUser();
        $organizations = $this->acfService->getField(self::USER_META_KEY, "user_{$user->ID}");

        return is_array($organizations) ? $organizations : [];
    }
}
