<?php

namespace EventManager\Tests\FieldSettingHidePublic;

use EventManager\FieldSettingHidePublic;
use EventManager\Services\WPService\WPService;
use Mockery;
use WP_Mock;
use WP_Mock\Tools\TestCase;

class FieldSettingHidePublicTest extends TestCase
{
    /**
     * @testdox addHooks() adds action hooks for rendering field settings and preparing field
     */
    public function testAddHooksAddsActionHooksForRenderingFieldSettingsAndPreparingField()
    {
        $wpService = $this->createMock(WPService::class);
        $sutInstance = new FieldSettingHidePublic($wpService);

        $wpService->expects($this->once())
            ->method('addAction')
            ->withConsecutive(
                ['acf/render_field_settings', [$sutInstance, 'addPublicFieldOption']],
                ['acf/prepare_field', [$sutInstance, 'hideFieldFromFrontendForms']]
            );

        $sutInstance->addHooks();

        $this->assertConditionsMet();
    }

    /**
     * @testdox addPublicFieldOption() renders the "Hide in frontend forms" field setting
     */
    public function testAddPublicFieldOptionRendersHideInFrontendFormsFieldSetting()
    {
        $field = [
            'label' => __('Hide in frontend forms', 'api-event-manager'),
            'instructions' => 'Whether to display this field in frontend forms or not.',
            'name' => 'is_publicly_hidden',
            'type' => 'true_false',
            'ui' => 1,
        ];

        $wpService = Mockery::mock(WPService::class);
        $sutInstance = new FieldSettingHidePublic($wpService);

        $wpService->shouldReceive('acf_render_field_setting')
            ->once()
            ->with($field, $field, true);

        $sutInstance->addPublicFieldOption($field);

        $this->assertConditionsMet();
    }

    /**
     * @testdox hideFieldFromFrontendForms() sets default value for is_publicly_hidden if not set
     */
    public function testHideFieldFromFrontendFormsSetsDefaultValueForIsPubliclyHiddenIfNotSet()
    {
        $field = [];

        $wpService = Mockery::mock(WPService::class);
        $sutInstance = new FieldSettingHidePublic($wpService);

        $wpService->shouldReceive('isAdmin')
            ->once()
            ->andReturn(false);

        $result = $sutInstance->hideFieldFromFrontendForms($field);

        $this->assertEquals(0, $field['is_publicly_hidden']);
        $this->assertEquals($field, $result);

        $this->assertConditionsMet();
    }

    /**
     * @testdox hideFieldFromFrontendForms() does not hide fields in admin
     */
    public function testHideFieldFromFrontendFormsDoesNotHideFieldsInAdmin()
    {
        $field = ['is_publicly_hidden' => 1];

        $wpService = Mockery::mock(WPService::class);
        $sutInstance = new FieldSettingHidePublic($wpService);

        $wpService->shouldReceive('isAdmin')
            ->once()
            ->andReturn(true);

        $result = $sutInstance->hideFieldFromFrontendForms($field);

        $this->assertEquals($field, $result);

        $this->assertConditionsMet();
    }

    /**
     * @testdox hideFieldFromFrontendForms() hides field from frontend forms if is_publicly_hidden is set to 1
     */
    public function testHideFieldFromFrontendFormsHidesFieldFromFrontendFormsIfIsPubliclyHiddenIsSetToOne()
    {
        $field = ['is_publicly_hidden' => 1];

        $wpService = Mockery::mock(WPService::class);
        $sutInstance = new FieldSettingHidePublic($wpService);

        $wpService->shouldReceive('isAdmin')
            ->once()
            ->andReturn(false);

        $result = $sutInstance->hideFieldFromFrontendForms($field);

        $this->assertFalse($result);

        $this->assertConditionsMet();
    }

    /**
     * @testdox hideFieldFromFrontendForms() does not hide field from frontend forms if is_publicly_hidden is set to 0
     */
    public function testHideFieldFromFrontendFormsDoesNotHideFieldFromFrontendFormsIfIsPubliclyHiddenIsSetToZero()
    {
        $field = ['is_publicly_hidden' => 0];

        $wpService = Mockery::mock(WPService::class);
        $sutInstance = new FieldSettingHidePublic($wpService);

        $wpService->shouldReceive('isAdmin')
            ->once()
            ->andReturn(false);

        $result = $sutInstance->hideFieldFromFrontendForms($field);

        $this->assertEquals($field, $result);

        $this->assertConditionsMet();
    }
}