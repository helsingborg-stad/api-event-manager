<?php

namespace EventManager;

use PHPUnit\Framework\TestCase;

class AppConfigTest extends TestCase
{
    public function testGetTextDomain(): void
    {
        $appConfig = new AppConfig();
        static::assertEquals('api-event-manager', $appConfig->getTextDomain());
    }

    public function testGetEventPostType(): void
    {
        $appConfig = new AppConfig();
        static::assertEquals('event', $appConfig->getEventPostType());
    }

    public function getOrganizationTaxonomy(): void
    {
        $appConfig = new AppConfig();
        static::assertEquals('organization', $appConfig->getOrganizationTaxonomy());
    }
}
