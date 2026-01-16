<?php

namespace EventManager\AcfFieldContentModifiers;

use AcfService\Contracts\GetField;
use EventManager\HooksRegistrar\Hookable;
use WpService\Contracts\AddFilter;
use WpService\Contracts\WpGetCurrentUser;

class FilterAcfOrganizerSelectField implements Hookable
{
    private const USER_META_KEY = 'organizations';

    public function __construct(
        private string $fieldKey,
        private WpGetCurrentUser&AddFilter $wpService,
        private GetField $acfService
    ) {
    }

    public function addHooks(): void
    {
        $this->wpService->addFilter('acf/fields/taxonomy/query', [$this, 'modifyFieldOptions'], 10, 2);
    }

    public function modifyFieldOptions(array $args, array $field): array
    {
        if ($field['key'] !== $this->fieldKey) {
            return $args;
        }

        $userOrganizations = $this->getUserOrganizations();

        if (count($userOrganizations) > 0) {
            $args['include'] = $userOrganizations;
        }

        return $args;
    }

    public function getUserOrganizations(): array
    {
        $user          = $this->wpService->wpGetCurrentUser();
        $organizations = $this->acfService->getField(self::USER_META_KEY, "user_{$user->ID}");

        return is_array($organizations) ? $organizations : [];
    }
}
