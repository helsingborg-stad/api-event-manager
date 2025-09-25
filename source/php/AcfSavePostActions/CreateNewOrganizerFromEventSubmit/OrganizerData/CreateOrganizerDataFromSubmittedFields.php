<?php

namespace EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData;

class CreateOrganizerDataFromSubmittedFields implements ICreateOrganizerDataFromSubmittedFields
{
    public function tryCreate(array $fields): ?IOrganizerData
    {
        if (!$this->canCreate($fields)) {
            return null;
        }

        return new OrganizerData(
            name: $fields['organizerName'],
            email: $fields['organizerEmail'],
            contact: $fields['organizerContact'],
            telephone: $fields['organizerTelephone'],
            address: $fields['organizerAddress'],
            url: $fields['organizerUrl']
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
