<?php

namespace EventManager;

class AppConfig implements AppConfigInterface
{
    public function getTextDomain(): string
    {
        return 'api-event-manager';
    }

    public function getEventPostType(): string
    {
        return 'event';
    }

    public function getOrganizationTaxonomy(): string
    {
        return 'organization';
    }
}
