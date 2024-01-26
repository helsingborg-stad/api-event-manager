<?php

namespace EventManager\Tests\PostToSchema;

use EventManager\PostToSchema\PostToEventSchema;
use EventManager\Services\WPService\WPService;
use Mockery;
use WP_Mock\Tools\TestCase;

class PostToEventSchemaTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testClassExists()
    {
        $this->assertTrue(class_exists('EventManager\PostToSchema\PostToEventSchema'));
    }

    public function testIdentifierIsSet()
    {
        [$post, $wpServiceMock] = $this->getBasicPropertiesTestDependencies();
        $postToEventSchema      = new PostToEventSchema($wpServiceMock, $post);
        $schemaArray            = $postToEventSchema->toArray();

        $this->assertEquals('123', $schemaArray['@id']);
    }

    public function testDescriptionIsSet()
    {
        [$post, $wpServiceMock] = $this->getBasicPropertiesTestDependencies();
        $postToEventSchema      = new PostToEventSchema($wpServiceMock, $post);
        $schemaArray            = $postToEventSchema->toArray();

        $this->assertEquals('TestDescription', $schemaArray['description']);
    }

    public function testAboutIsSet()
    {
        [$post, $wpServiceMock] = $this->getBasicPropertiesTestDependencies();
        $postToEventSchema      = new PostToEventSchema($wpServiceMock, $post);
        $schemaArray            = $postToEventSchema->toArray();

        $this->assertEquals('TestAbout', $schemaArray['about']);
    }

    public function testImageIsSet()
    {
        [$post, $wpServiceMock] = $this->getBasicPropertiesTestDependencies();
        $postToEventSchema      = new PostToEventSchema($wpServiceMock, $post);
        $schemaArray            = $postToEventSchema->toArray();

        $this->assertEquals('TestImageUrl', $schemaArray['image']);
    }

    public function testUrlIsSet()
    {
        [$post, $wpServiceMock] = $this->getBasicPropertiesTestDependencies();
        $postToEventSchema      = new PostToEventSchema($wpServiceMock, $post);
        $schemaArray            = $postToEventSchema->toArray();

        $this->assertEquals('TestUrl', $schemaArray['url']);
    }

    public function testLocationIsSet()
    {
        $locationMeta           = ['location' => ['street_name' => 'TestLocation']];
        [$post, $wpServiceMock] = $this->getBasicPropertiesTestDependencies($locationMeta);
        $postToEventSchema      = new PostToEventSchema($wpServiceMock, $post);
        $schemaArray            = $postToEventSchema->toArray();

        $this->assertEquals('Place', $schemaArray['location']['@type']);
    }

    public function testAudienceIsSet()
    {
        $audienceTermId         = 1;
        $audienceTerm           = Mockery::mock(\WP_Term::class);
        $audienceTerm->term_id  = $audienceTermId;
        $audienceTerm->name     = 'TestAudience';
        $locationMeta           = ['audience' => $audienceTermId];
        [$post, $wpServiceMock] = $this->getBasicPropertiesTestDependencies($locationMeta);
        $wpServiceMock->shouldReceive('getTerm')->andReturn($audienceTerm);
        $wpServiceMock->shouldReceive('getTermMeta')->andReturn(null);
        $postToEventSchema = new PostToEventSchema($wpServiceMock, $post);
        $schemaArray       = $postToEventSchema->toArray();

        $this->assertEquals('Audience', $schemaArray['audience']['@type']);
        $this->assertEquals('TestAudience', $schemaArray['audience']['name']);
    }

    public function testAudienceSetsTypicalAgeRange()
    {
        $audienceTermId         = 1;
        $audienceTerm           = Mockery::mock(\WP_Term::class);
        $audienceTerm->term_id  = $audienceTermId;
        $audienceTerm->name     = 'TestAudience';
        $locationMeta           = ['audience' => $audienceTermId];
        [$post, $wpServiceMock] = $this->getBasicPropertiesTestDependencies($locationMeta);
        $wpServiceMock->shouldReceive('getTerm')->andReturn($audienceTerm);
        $wpServiceMock->shouldReceive('getTermMeta')->with(1, 'typicalAgeRangeStart', true)->andReturn('1');
        $wpServiceMock->shouldReceive('getTermMeta')->with(1, 'typicalAgeRangeEnd', true)->andReturn('2');
        $postToEventSchema = new PostToEventSchema($wpServiceMock, $post);
        $schemaArray       = $postToEventSchema->toArray();

        $this->assertEquals('1-2', $schemaArray['typicalAgeRange']);
    }

    /**
     * @testdox Event gets start and end date for simple occation.
     */
    public function testEventGetsStartAndEndDateForSimpleOccation()
    {

        $occasionsMeta = [
            'occasions'             => 1,
            'occasions_0_repeat'    => 'no',
            'occasions_0_startDate' => '2021-03-02',
            'occasions_0_startTime' => '22:00',
            'occasions_0_endDate'   => '2021-03-02',
            'occasions_0_endTime'   => '23:00'
        ];

        [$post, $wpServiceMock] = $this->getBasicPropertiesTestDependencies($occasionsMeta);
        $postToEventSchema      = new PostToEventSchema($wpServiceMock, $post);
        $schemaArray            = $postToEventSchema->toArray();

        $this->assertEquals('2021-03-02 22:00', $schemaArray['startDate']);
        $this->assertEquals('2021-03-02 23:00', $schemaArray['endDate']);
    }

    /**
     * @testdox Event gets duration from start and end date if present.
     */
    public function testEventHasDurationIfSimpleOccasion()
    {
        $occasionsMeta = [
            'occasions'             => 1,
            'occasions_0_repeat'    => 'no',
            'occasions_0_startDate' => '2021-03-02',
            'occasions_0_startTime' => '22:00',
            'occasions_0_endDate'   => '2021-03-02',
            'occasions_0_endTime'   => '23:00'
        ];

        [$post, $wpServiceMock] = $this->getBasicPropertiesTestDependencies($occasionsMeta);
        $postToEventSchema      = new PostToEventSchema($wpServiceMock, $post);
        $schemaArray            = $postToEventSchema->toArray();

        $this->assertEquals('P0Y0M0DT1H0M0S', $schemaArray['duration']);
    }

    public function testEventScheduleByDay()
    {
        $occasionsMeta = [
            'occasions'                => 1,
            'occasions_0_repeat'       => 'byDay',
            'occasions_0_daysInterval' => '1',
            'occasions_0_startDate'    => '2021-03-01',
            'occasions_0_startTime'    => '13:00',
            'occasions_0_endDate'      => '2021-03-10',
            'occasions_0_endTime'      => '14:00',
        ];

        [$post, $wpServiceMock] = $this->getBasicPropertiesTestDependencies($occasionsMeta);
        $postToEventSchema      = new PostToEventSchema($wpServiceMock, $post);
        $schemaArray            = $postToEventSchema->toArray();
        $schedule               = $schemaArray['eventSchedule'][0];

        $this->assertEquals('2021-03-01', $schedule['startDate']);
        $this->assertEquals('2021-03-10', $schedule['endDate']);
        $this->assertEquals('13:00', $schedule['startTime']);
        $this->assertEquals('14:00', $schedule['endTime']);
    }

    /**
     * @testdox Event gets duration from start and end date if present.
     */
    public function testEventScheduleByEverySecondDay()
    {
        $occasionsMeta = [
            'occasions'                => 1,
            'occasions_0_repeat'       => 'byDay',
            'occasions_0_daysInterval' => '2',
            'occasions_0_startDate'    => '2021-03-01',
            'occasions_0_startTime'    => '13:00',
            'occasions_0_endDate'      => '2021-03-11',
            'occasions_0_endTime'      => '14:00',
        ];

        [$post, $wpServiceMock] = $this->getBasicPropertiesTestDependencies($occasionsMeta);
        $postToEventSchema      = new PostToEventSchema($wpServiceMock, $post);
        $schemaArray            = $postToEventSchema->toArray();
        $schedule               = $schemaArray['eventSchedule'][0];

        $this->assertEquals('P2D', $schedule['repeatFrequency']);
        $this->assertEquals(6, $schedule['repeatCount']);
    }

    public function testEventScheduleByWeek()
    {
        $occasionsMeta = [
            'occasions'                 => 1,
            'occasions_0_repeat'        => 'byWeek',
            'occasions_0_weeksInterval' => '1',
            'occasions_0_weekDays'      => [
                'https://schema.org/Monday',
                'https://schema.org/Wednesday',
            ],
            'occasions_0_startDate'     => '2024-01-01',
            'occasions_0_startTime'     => '13:00',
            'occasions_0_endDate'       => '2024-01-17',
            'occasions_0_endTime'       => '14:00',
        ];

        [$post, $wpServiceMock] = $this->getBasicPropertiesTestDependencies($occasionsMeta);
        $postToEventSchema      = new PostToEventSchema($wpServiceMock, $post);
        $schemaArray            = $postToEventSchema->toArray();
        $schedule               = $schemaArray['eventSchedule'][0];

        $this->assertCount(2, $schedule['byDay']);
        $this->assertContains('https://schema.org/Monday', $schedule['byDay']);
        $this->assertContains('https://schema.org/Wednesday', $schedule['byDay']);
        $this->assertEquals('P1W', $schedule['repeatFrequency']);
        $this->assertEquals(6, $schedule['repeatCount']);
    }

    private function getBasicPropertiesTestDependencies(array $additionalMeta = []): array
    {
        /** @var \WP_Post $post */
        $post          = $this->mockPost(['ID' => '123', 'post_title' => 'Test']);
        $meta          = $additionalMeta + ['description' => 'TestDescription', 'about' => 'TestAbout'];
        $wpServiceMock = $this->getMockedWpService();
        $wpServiceMock->shouldReceive('getPostMeta')->andReturnUsing(fn($postId, $key) => $meta[$key] ?? null);
        $wpServiceMock->shouldReceive('getThePostThumbnailUrl')->andReturn('TestImageUrl');
        $wpServiceMock->shouldReceive('getPermalink')->andReturn('TestUrl');
        $wpServiceMock->shouldReceive('getPostParent')->andReturn(null);
        $wpServiceMock->shouldReceive('getPosts')->andReturn([]);

        return [$post, $wpServiceMock];
    }

    private function getMockedWpService()
    {
        return Mockery::mock(WPService::class);
    }
}
