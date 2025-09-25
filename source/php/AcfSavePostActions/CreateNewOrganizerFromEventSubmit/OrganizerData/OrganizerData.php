<?php

namespace EventManager\AcfSavePostActions\CreateNewOrganizerFromEventSubmit\OrganizerData;

class OrganizerData implements IOrganizerData
{
    public function __construct(
        private string $name,
        private string $email,
        private string $contact,
        private string $telephone,
        private string $address,
        private string $url
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function getContact(): string
    {
        return $this->contact;
    }
    public function getTelephone(): string
    {
        return $this->telephone;
    }
    public function getAddress(): string
    {
        return $this->address;
    }
    public function getUrl(): string
    {
        return $this->url;
    }
}
