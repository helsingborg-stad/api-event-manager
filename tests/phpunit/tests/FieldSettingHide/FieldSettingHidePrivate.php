<?php

namespace EventManager\Tests\FieldSettingHide;

use EventManager\FieldSettingHidePrivate;
use EventManager\Services\WPService\WPService;
use EventManager\Services\AcfService\AcfService;
use Mockery;
use WP_Mock;
use WP_Mock\Tools\TestCase;

class FieldSettingHidePrivateTest extends TestCase
{
    public function setUp(): void
    {
        WP_Mock::setUp();
    }

    public function testIsPrivatelyHiddenWhenUserIsAdmin()
    {
        $field = [];
        $field['is_privately_hidden'] = 1;

        $wpService = Mockery::mock(WPService::class);
        $wpService->shouldReceive('isAdmin')->once()->andReturn(true);

        $acfService = Mockery::mock(AcfService::class);

        $FieldSettingHidePrivate = new FieldSettingHidePrivate($wpService, $acfService);

        $result = $FieldSettingHidePrivate->hideFieldFromBackendForms($field);

        $this->assertFalse($result);
    }

    public function testIsNotPrivatelyHiddenWhenUserIsAdmin()
    {
        $field = [];
        $field['is_privately_hidden'] = 0;

        $wpService = Mockery::mock(WPService::class);
        $wpService->shouldReceive('isAdmin')->once()->andReturn(true);

        $acfService = Mockery::mock(AcfService::class);

        $FieldSettingHidePrivate = new FieldSettingHidePrivate($wpService, $acfService);

        $result = $FieldSettingHidePrivate->hideFieldFromBackendForms($field);

        $this->assertSame($result, $field);
    }

    public function testIsNotPrivatelyHiddenWhenUserIsFrontendWhenHidden()
    {
        $field = [];
        $field['is_privately_hidden'] = 1;

        $wpService = Mockery::mock(WPService::class);
        $wpService->shouldReceive('isAdmin')->once()->andReturn(false);

        $acfService = Mockery::mock(AcfService::class);

        $FieldSettingHidePrivate = new FieldSettingHidePrivate($wpService, $acfService);

        $result = $FieldSettingHidePrivate->hideFieldFromBackendForms($field);

        $this->assertSame($result, $field);
    }

    public function testIsNotPrivatelyHiddenWhenUserIsFrontendWhenNotHidden()
    {
        $field = [];
        $field['is_privately_hidden'] = 0;

        $wpService = Mockery::mock(WPService::class);
        $wpService->shouldReceive('isAdmin')->once()->andReturn(false);

        $acfService = Mockery::mock(AcfService::class);

        $FieldSettingHidePrivate = new FieldSettingHidePrivate($wpService, $acfService);

        $result = $FieldSettingHidePrivate->hideFieldFromBackendForms($field);

        $this->assertSame($result, $field);
    }

}