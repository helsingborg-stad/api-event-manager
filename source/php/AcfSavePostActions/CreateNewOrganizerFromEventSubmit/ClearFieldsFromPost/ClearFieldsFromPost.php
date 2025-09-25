<?php

namespace EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\ClearFieldsFromPost;

use AcfService\Contracts\DeleteField;

class ClearFieldsFromPost implements IClearFieldsFromPost
{
    public function __construct(private DeleteField $acfService)
    {
    }

    public function clearFields(int|string $postId): void
    {
        foreach ($this->getFields() as $field) {
            $this->acfService->deleteField($field, $postId);
        }
    }

    private function getFields(): array
    {
        return [
            'submitNewOrganization',
            'organizerName',
            'organizerEmail',
            'organizerContact',
            'organizerTelephone',
            'organizerAddress',
            'organizerUrl',
        ];
    }
}
