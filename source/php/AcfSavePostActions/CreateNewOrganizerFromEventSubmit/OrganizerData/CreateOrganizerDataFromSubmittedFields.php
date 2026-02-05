<?php

namespace EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData;

use WpService\Contracts\EscUrlRaw;
use WpService\Contracts\SanitizeEmail;
use WpService\Contracts\SanitizeTextField;

class CreateOrganizerDataFromSubmittedFields implements ICreateOrganizerDataFromSubmittedFields
{
    public function __construct(private SanitizeTextField&SanitizeEmail&EscUrlRaw $wpService) {}

    public function tryCreate(array $fields): ?array
    {
        if (!$this->hasNewOrganizersData($fields)) {
            return null;
        }

        $createdOrganizers = [];
        foreach ($fields['newOrganizers'] as $organizer) {
            if (!$this->canCreateOrganizer($organizer)) {
                continue;
            }

            $sanitizedOrganizer = $this->sanitizeOrganizerFields($organizer);

            $createdOrganizers[] = new OrganizerData(
                name: $sanitizedOrganizer['organizerName'],
                email: $sanitizedOrganizer['organizerEmail'],
                contact: $sanitizedOrganizer['organizerContact'],
                telephone: $sanitizedOrganizer['organizerTelephone'],
                address: $sanitizedOrganizer['organizerAddress'],
                url: $sanitizedOrganizer['organizerUrl']
            );
        }

        return !empty($createdOrganizers) ? $createdOrganizers : null;
    }

    private function sanitizeOrganizerFields(array $organizer): array
    {
        $organizer['organizerName'] = $this->wpService->sanitizeTextField($organizer['organizerName']);
        $organizer['organizerEmail'] = $this->wpService->sanitizeEmail($organizer['organizerEmail']);
        $organizer['organizerContact'] = $this->wpService->sanitizeTextField($organizer['organizerContact']);
        $organizer['organizerTelephone'] = $this->wpService->sanitizeTextField($organizer['organizerTelephone']);
        $organizer['organizerAddress'] = $this->wpService->sanitizeTextField($organizer['organizerAddress']);
        $organizer['organizerUrl'] = $this->wpService->escUrlRaw($organizer['organizerUrl']);

        return $organizer;
    }

    private function canCreateOrganizer(mixed $organizer): bool
    {
        return
            is_array($organizer) &&
            !empty($organizer['organizerName']) && is_string($organizer['organizerName']) &&
            !empty($organizer['organizerEmail']) && is_string($organizer['organizerEmail']) &&
            !empty($organizer['organizerContact']) && is_string($organizer['organizerContact']) &&
            !empty($organizer['organizerTelephone']) && is_string($organizer['organizerTelephone']) &&
            !empty($organizer['organizerAddress']) && is_string($organizer['organizerAddress']) &&
            !empty($organizer['organizerUrl']) && is_string($organizer['organizerUrl']);
    }

    private function hasNewOrganizersData(array $fields): bool
    {
        return
            isset($fields['submitNewOrganization']) &&
            $fields['submitNewOrganization'] === true &&
            !empty($fields['newOrganizers']);
    }
}
