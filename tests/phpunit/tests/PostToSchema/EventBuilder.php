<?php

namespace EventManager\Tests\PostToSchema;

use EventManager\PostToSchema\EventBuilder;
use EventManager\Services\WPService\WPService;
use Mockery;
use Mockery\MockInterface;
use EventManager\Services\AcfService\AcfService;
use WP_Mock\Tools\TestCase;
use WP_Post;

class EventBuilderTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testClassExists()
    {
        $this->assertTrue(class_exists('EventManager\PostToSchema\EventBuilder'));
    }

    private function getMockedPost(array $args): WP_Post|MockInterface
    {
        return $this->mockPost($args);
    }

    public function testIdentifierIsSet()
    {
        $wpService  = Mockery::mock(WPService::class);
        $acfService = Mockery::mock(AcfService::class);
        $post       = $this->getMockedPost(['ID' => 123]);
        $event      = new EventBuilder($post, $wpService, $acfService);

        $event->setIdentifier();

        $this->assertEquals('123', $event->toArray()['@id']);
    }

    public function testDescriptionIsSet()
    {
        $post       = $this->getMockedPost(['ID' => 123]);
        $wpService  = Mockery::mock(WPService::class);
        $acfService = Mockery::mock(AcfService::class);
        $wpService->shouldReceive('getPostMeta')->with($post->ID, 'description', true)->andReturn('testdescription');
        $event = new EventBuilder($post, $wpService, $acfService);

        $event->setDescription();

        $this->assertEquals('testdescription', $event->toArray()['description']);
    }

    public function testAboutIsSet()
    {
        $post       = $this->getMockedPost(['ID' => 123]);
        $wpService  = Mockery::mock(WPService::class);
        $acfService = Mockery::mock(AcfService::class);
        $wpService->shouldReceive('getPostMeta')->with($post->ID, 'about', true)->andReturn('testabout');
        $event = new EventBuilder($post, $wpService, $acfService);

        $event->setAbout();

        $this->assertEquals('testabout', $event->toArray()['about']);
    }

    public function testImageIsSet()
    {
        $post       = $this->getMockedPost(['ID' => 123]);
        $wpService  = Mockery::mock(WPService::class);
        $acfService = Mockery::mock(AcfService::class);
        $wpService->shouldReceive('getThePostThumbnailUrl')->with($post->ID)->andReturn('http://images.com/image.jpg');
        $event = new EventBuilder($post, $wpService, $acfService);

        $event->setImage();

        $this->assertEquals('http://images.com/image.jpg', $event->toArray()['image']);
    }

    public function testLocationIsSet()
    {
        $location = ['latitude' => 1.2, 'longitude' => 3.4, 'address' => 'TestAddress'];

        $post       = $this->getMockedPost(['ID' => 123]);
        $wpService  = Mockery::mock(WPService::class);
        $acfService = Mockery::mock(AcfService::class);
        $wpService->shouldReceive('getPostMeta')->with($post->ID, 'location', true)->andReturn($location);
        $event = new EventBuilder($post, $wpService, $acfService);

        $event->setLocation();

        $this->assertEquals('Place', $event->toArray()['location']['@type']);
        $this->assertEquals('TestAddress', $event->toArray()['location']['address']);
        $this->assertEquals(1.2, $event->toArray()['location']['latitude']);
        $this->assertEquals(3.4, $event->toArray()['location']['longitude']);
    }

    public function testAudienceIsSet()
    {
        $post                  = $this->getMockedPost(['ID' => 123]);
        $audienceTerm          = Mockery::mock(\WP_Term::class);
        $audienceTerm->term_id = 321;
        $audienceTerm->name    = 'TestAudience';

        $wpService  = Mockery::mock(WPService::class);
        $acfService = Mockery::mock(AcfService::class);
        $wpService->shouldReceive('getPostMeta')->with($post->ID, 'audience', true)->andReturn($audienceTerm->term_id);
        $wpService->shouldReceive('getTerm')->andReturn($audienceTerm);
        $wpService->shouldReceive('getTermMeta')->andReturn(null);
        $event = new EventBuilder($post, $wpService, $acfService);

        $event->setAudience();

        $this->assertEquals('Audience', $event->toArray()['audience']['@type']);
        $this->assertEquals('TestAudience', $event->toArray()['audience']['name']);
    }


    /**
     * @testdox Event gets typical age range from audience if audience is set
     */
    public function testEventGetsTypicalAgeRangeFromAudienceIfAudienceIsSet()
    {
        $post                  = $this->getMockedPost(['ID' => 123]);
        $audienceTerm          = Mockery::mock(\WP_Term::class);
        $audienceTerm->term_id = 321;
        $audienceTerm->name    = 'TestAudience';

        $wpService  = Mockery::mock(WPService::class);
        $acfService = Mockery::mock(AcfService::class);
        $wpService->shouldReceive('getPostMeta')->with($post->ID, 'audience', true)->andReturn($audienceTerm->term_id);
        $wpService->shouldReceive('getTerm')->andReturn($audienceTerm);
        $wpService->shouldReceive('getTermMeta')->with($audienceTerm->term_id, 'typicalAgeRangeStart', true)->andReturn('18');
        $wpService->shouldReceive('getTermMeta')->with($audienceTerm->term_id, 'typicalAgeRangeEnd', true)->andReturn('35');
        $event = new EventBuilder($post, $wpService, $acfService);

        $event->setAudience();
        $event->setTypicalAgeRange();

        $this->assertEquals('18-35', $event->toArray()['typicalAgeRange']);
    }

    /**
     * @testdox endDate gets the same value as startDate to avoid events spanning multiple days
     */
    public function testEndDateGetsTheSameValueAsStartDateToAvoidEventsSpanningMultipleDays()
    {
        $occasions = [
            [
                'repeat'    => 'no',
                'date'      => '2021-03-02',
                'startTime' => '22:00',
                'endTime'   => '23:00'
            ]
        ];

        $post       = $this->getMockedPost(['ID' => 123]);
        $wpService  = Mockery::mock(WPService::class);
        $acfService = Mockery::mock(AcfService::class);
        $acfService->shouldReceive('getField')->with('occasions', $post->ID)->andReturn($occasions);

        $event = new EventBuilder($post, $wpService, $acfService);

        $event->setDates();

        $this->assertEquals('2021-03-02 23:00', $event->toArray()['endDate']);
    }

    /**
     * @testdox endDate can never be earlier than startDate
     */
    public function testEndDateCanNeverBeEarlierThanStartDate()
    {
        $occasions = [
            [
                'repeat'    => 'no',
                'date'      => '2021-03-02',
                'startTime' => '23:00',
                'endTime'   => '22:00'
            ]
        ];

        $post       = $this->getMockedPost(['ID' => 123]);
        $wpService  = Mockery::mock(WPService::class);
        $acfService = Mockery::mock(AcfService::class);
        $acfService->shouldReceive('getField')->with('occasions', $post->ID)->andReturn($occasions);

        $event = new EventBuilder($post, $wpService, $acfService);

        $event->setDates();

        $this->assertArrayNotHasKey('endDate', $event->toArray());
    }

    /**
     * @testdox Event gets start and end date for simple occation, and uses same date for start and end date.
     */
    public function testEventGetsStartAndEndDateForSimpleOccation()
    {
        $occasions = [
            [
                'repeat'    => 'no',
                'date'      => '2021-03-02',
                'startTime' => '22:00',
                'endTime'   => '23:00'
            ]
        ];

        $post       = $this->getMockedPost(['ID' => 123]);
        $wpService  = Mockery::mock(WPService::class);
        $acfService = Mockery::mock(AcfService::class);
        $acfService->shouldReceive('getField')->with('occasions', $post->ID)->andReturn($occasions);

        $event = new EventBuilder($post, $wpService, $acfService);

        $event->setDates();

        $this->assertEquals('2021-03-02 22:00', $event->toArray()['startDate']);
        $this->assertEquals('2021-03-02 23:00', $event->toArray()['endDate']);
    }

    /**
     * @testdox Event gets duration from start and end date if present.
     */
    public function testEventHasDurationIfSimpleOccasion()
    {
        $occasions = [
            [
                'repeat'    => 'no',
                'date'      => '2021-03-02',
                'startTime' => '22:00',
                'endTime'   => '23:00'
            ]
        ];

        $post       = $this->getMockedPost(['ID' => 123]);
        $wpService  = Mockery::mock(WPService::class);
        $acfService = Mockery::mock(AcfService::class);
        $acfService->shouldReceive('getField')->with('occasions', 123)->andReturn($occasions);

        $eventBuilder = new EventBuilder($post, $wpService, $acfService);
        $eventBuilder->setDates();
        $eventBuilder->setDuration();
        $schemaArray = $eventBuilder->toArray();

        $this->assertEquals('P0Y0M0DT1H0M0S', $schemaArray['duration']);
    }

    public function testEventOccasionUrlSetsEventUrl()
    {
        $occasions = [
            [
                'repeat'    => 'no',
                'startDate' => '2021-03-02',
                'startTime' => '22:00',
                'endTime'   => '23:00',
                'url'       => 'https://www.example.com'
            ]
        ];

        $post       = $this->getMockedPost(['ID' => 123]);
        $wpService  = Mockery::mock(WPService::class);
        $acfService = Mockery::mock(AcfService::class);
        $acfService->shouldReceive('getField')->with('occasions', 123)->andReturn($occasions);

        $eventBuilder = new EventBuilder($post, $wpService, $acfService);
        $eventBuilder->setUrl();
        $schemaArray = $eventBuilder->toArray();

        $this->assertEquals('https://www.example.com', $schemaArray['url']);
    }

    public function testEventGetsOrganizationFromTerm()
    {
        $organizationTerm          = Mockery::mock(\WP_Term::class);
        $organizationTerm->term_id = 1;
        $organizationTerm->name    = 'TestOrganization';
        $post                      = $this->getMockedPost(['ID' => 123]);
        $wpService                 = Mockery::mock(WPService::class);
        $acfService                = Mockery::mock(AcfService::class);

        $termMeta = [
            'url'       => 'https://www.example.com',
            'email'     => 'organizer@event.foo',
            'telephone' => '123',
            'address'   => ['address' => 'TestAddress']
        ];
        $wpService
            ->shouldReceive('getPostTerms')
            ->andReturn([$organizationTerm]);
        $wpService
            ->shouldReceive('isWPError')
            ->andReturn(false);
        $wpService
            ->shouldReceive('getTermMeta')
            ->with($organizationTerm->term_id, 'url', true)
            ->andReturn($termMeta['url']);
        $wpService
            ->shouldReceive('getTermMeta')
            ->with($organizationTerm->term_id, 'email', true)
            ->andReturn($termMeta['email']);
        $wpService
            ->shouldReceive('getTermMeta')
            ->with($organizationTerm->term_id, 'telephone', true)
            ->andReturn($termMeta['telephone']);
        $wpService
            ->shouldReceive('getTermMeta')
            ->with($organizationTerm->term_id, 'address', true)
            ->andReturn($termMeta['address']);

        $eventBuilder = new EventBuilder($post, $wpService, $acfService);
        $eventBuilder->setOrganizer();
        $schemaArray = $eventBuilder->toArray();

        $this->assertEquals('Organization', $schemaArray['organizer']['@type']);
        $this->assertEquals($organizationTerm->name, $schemaArray['organizer']['name']);
        $this->assertEquals($termMeta['url'], $schemaArray['organizer']['url']);
        $this->assertEquals($termMeta['email'], $schemaArray['organizer']['email']);
        $this->assertEquals($termMeta['telephone'], $schemaArray['organizer']['telephone']);
        $this->assertEquals('Place', $schemaArray['organizer']['location']['@type']);
        $this->assertEquals('TestAddress', $schemaArray['organizer']['location']['address']);
    }

    /**
     * @testdox setKeywords() sets keywords from post tags
     */
    public function testSetKeywordsSetsKeywordsFromPostTags()
    {
        $post       = $this->getMockedPost(['ID' => 123]);
        $term       = Mockery::mock(\WP_Term::class);
        $term->name = 'tag1';
        $wpService  = Mockery::mock(WPService::class);
        $acfService = Mockery::mock(AcfService::class);
        $wpService->shouldReceive('getPostTerms')->with($post->ID, 'keyword', [])->andReturn([$term]);
        $event = new EventBuilder($post, $wpService, $acfService);

        $event->setKeywords();

        $this->assertEquals(['tag1'], $event->toArray()['keywords']);
    }

    /**
     * @testdox setAbout() appends accessibility information if available
     */
    public function testSetAboutAppendsAccessabilityInformationIfAvailable()
    {
        $post       = $this->getMockedPost(['ID' => 123]);
        $wpService  = Mockery::mock(WPService::class);
        $acfService = Mockery::mock(AcfService::class);
        $wpService->shouldReceive('getPostMeta')->with($post->ID, 'about', true)->andReturn('testabout');
        $wpService->shouldReceive('getPostMeta')->with($post->ID, 'accessabilityInformation', true)->andReturn('testaccessability');
        $event = new EventBuilder($post, $wpService, $acfService);

        $event->setAbout();
        $event->setAccessabilityInformation();

        $this->assertStringContainsString("testabout", $event->toArray()['about']);
        $this->assertStringContainsString("testaccessability", $event->toArray()['about']);
    }
}
