<?php

namespace EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData;

use WpService\Contracts\EscUrlRaw;
use WpService\Contracts\SanitizeEmail;
use WpService\Contracts\SanitizeTextField;

class CreateOrganizerDataFromSubmittedFields implements ICreateOrganizerDataFromSubmittedFields
{
    public function __construct(private SanitizeTextField&SanitizeEmail&EscUrlRaw $wpService)
    {
    }

    public function tryCreate(array $fields): ?IOrganizerData
    {
        if (!$this->canCreate($fields)) {
            return null;
        }

        // Sanitize all fields
        $name      = isset($fields['organizerName']) ? $this->wpService->sanitizeTextField($fields['organizerName']) : '';
        $email     = isset($fields['organizerEmail']) ? $this->wpService->sanitizeEmail($fields['organizerEmail']) : '';
        $contact   = isset($fields['organizerContact']) ? $this->wpService->sanitizeTextField($fields['organizerContact']) : '';
        $telephone = isset($fields['organizerTelephone']) ? $this->wpService->sanitizeTextField($fields['organizerTelephone']) : '';
        $address   = isset($fields['organizerAddress']) ? $this->wpService->sanitizeTextField($fields['organizerAddress']) : '';
        $url       = isset($fields['organizerUrl']) ? $this->wpService->escUrlRaw($fields['organizerUrl']) : '';

        return new OrganizerData(
            name: $name,
            email: $email,
            contact: $contact,
            telephone: $telephone,
            address: $address,
            url: $url
        );
    }

    private function canCreate(array $fields): bool
    {
        return
            isset($fields['submitNewOrganization']) && $fields['submitNewOrganization'] === true &&
            !empty($fields['organizerName']) &&
            !empty($fields['organizerEmail']) &&
            !empty($fields['organizerContact']) &&
            !empty($fields['organizerTelephone']) &&
            !empty($fields['organizerAddress']) &&
            !empty($fields['organizerUrl']);
    }
}
