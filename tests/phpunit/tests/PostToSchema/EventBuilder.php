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
        $post       = $this->getMockedPost(['ID' => 123]);
        $wpService  = Mockery::mock(WPService::class);
        $acfService = Mockery::mock(AcfService::class);
        $wpService->shouldReceive('getPostMeta')->with($post->ID, 'location', true)->andReturn(['street_name' => 'TestLocation']);
        $event = new EventBuilder($post, $wpService, $acfService);

        $event->setLocation();

        $this->assertEquals('Place', $event->toArray()['location']['@type']);
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
     * @testdox Event gets start and end date for simple occation.
     */
    public function testEventGetsStartAndEndDateForSimpleOccation()
    {
        $occasions = [
            [
                'repeat'    => 'no',
                'startDate' => '2021-03-02',
                'startTime' => '22:00',
                'endDate'   => '2021-03-02',
                'endTime'   => '23:00'
            ]
        ];

        $post       = $this->getMockedPost(['ID' => 123]);
        $wpService  = Mockery::mock(WPService::class);
        $acfService = Mockery::mock(AcfService::class);
        $acfService->shouldReceive('getField')->with('occasions', $post->ID)->andReturn($occasions);

        $event = new EventBuilder($post, $wpService, $acfService);

        $event->setStartDate();
        $event->setEndDate();

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
                'startDate' => '2021-03-02',
                'startTime' => '22:00',
                'endDate'   => '2021-03-02',
                'endTime'   => '23:00'
            ]
        ];

        $post       = $this->getMockedPost(['ID' => 123]);
        $wpService  = Mockery::mock(WPService::class);
        $acfService = Mockery::mock(AcfService::class);
        $acfService->shouldReceive('getField')->with('occasions', 123)->andReturn($occasions);

        $eventBuilder = new EventBuilder($post, $wpService, $acfService);
        $eventBuilder->setStartDate();
        $eventBuilder->setEndDate();
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
                'endDate'   => '2021-03-02',
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
            'address'   => ['street_name' => 'TestLocation']
        ];
        $wpService
            ->shouldReceive('wpGetPostTerms')
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
        $this->assertEquals('TestLocation', $schemaArray['organizer']['location']['address']['streetAddress']);
    }
}
