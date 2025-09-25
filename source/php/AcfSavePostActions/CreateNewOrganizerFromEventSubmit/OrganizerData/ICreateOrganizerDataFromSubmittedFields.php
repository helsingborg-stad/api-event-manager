<?php

namespace EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData;

interface ICreateOrganizerDataFromSubmittedFields
{
    public function tryCreate(array $fields): ?IOrganizerData;
}
