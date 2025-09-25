<?php

namespace EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData;

interface IOrganizerData
{
    public function getName(): string;
    public function getEmail(): string;
    public function getContact(): string;
    public function getTelephone(): string;
    public function getAddress(): string;
    public function getUrl(): string;
}
