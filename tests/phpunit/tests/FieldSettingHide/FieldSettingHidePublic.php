<?php

namespace EventManager\Tests\FieldSettingHide;

use EventManager\FieldSettingHidePublic;
use WpService\WpService;
use AcfService\AcfService;
use Mockery;
use WP_Mock;
use WP_Mock\Tools\TestCase;

class FieldSettingHidePublicTest extends TestCase
{
    public function setUp(): void
    {
        WP_Mock::setUp();
    }

    public function testIsPubliclyHiddenWhenUserIsVisitingFrontend()
    {
        $field                       = [];
        $field['is_publicly_hidden'] = 1;

        $wpService = Mockery::mock(WPService::class);
        $wpService->shouldReceive('isAdmin')->once()->andReturn(false);

        $acfService = Mockery::mock(AcfService::class);

        $fieldSettingHidePublic = new FieldSettingHidePublic($wpService, $acfService);

        $result = $fieldSettingHidePublic->hideFieldFromFrontendForms($field);

        $this->assertFalse($result);
    }

    public function testIsPubliclyHiddenWhenUserIsVisitingAdmin()
    {
        $field                       = [];
        $field['is_publicly_hidden'] = 1;

        $wpService = Mockery::mock(WPService::class);
        $wpService->shouldReceive('isAdmin')->once()->andReturn(true);

        $acfService = Mockery::mock(AcfService::class);

        $fieldSettingHidePublic = new FieldSettingHidePublic($wpService, $acfService);

        $result = $fieldSettingHidePublic->hideFieldFromFrontendForms($field);

        $this->assertEquals($field, $result);
    }

    public function testIsNotPubliclyHiddenWhenUserIsVisitingFrontend()
    {
        $field                       = [];
        $field['is_publicly_hidden'] = 0;

        $wpService = Mockery::mock(WPService::class);
        $wpService->shouldReceive('isAdmin')->once()->andReturn(false);

        $acfService = Mockery::mock(AcfService::class);

        $fieldSettingHidePublic = new FieldSettingHidePublic($wpService, $acfService);

        $result = $fieldSettingHidePublic->hideFieldFromFrontendForms($field);

        $this->assertEquals($field, $result);
    }

    public function testIsNotPubliclyHiddenWhenUserIsVisitingAdmin()
    {
        $field                       = [];
        $field['is_publicly_hidden'] = 0;

        $wpService = Mockery::mock(WPService::class);
        $wpService->shouldReceive('isAdmin')->once()->andReturn(true);

        $acfService = Mockery::mock(AcfService::class);

        $fieldSettingHidePublic = new FieldSettingHidePublic($wpService, $acfService);

        $result = $fieldSettingHidePublic->hideFieldFromFrontendForms($field);

        $this->assertEquals($field, $result);
    }
}
