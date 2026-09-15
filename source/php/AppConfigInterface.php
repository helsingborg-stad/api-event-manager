<?php

namespace EventManager;

interface AppConfigInterface
{
    public function getTextDomain(): string;
    public function getEventPostType(): string;
    public function getOrganizationTaxonomy(): string;
}
