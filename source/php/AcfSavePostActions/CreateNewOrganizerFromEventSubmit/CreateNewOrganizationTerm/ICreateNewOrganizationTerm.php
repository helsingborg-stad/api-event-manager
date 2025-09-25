<?php

namespace EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\CreateNewOrganizationTerm;

use EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData\IOrganizerData;

interface ICreateNewOrganizationTerm
{
    public function createTerm(IOrganizerData $organizerData): int;
}
