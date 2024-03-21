<?php

namespace EventManager\PostToSchema;

use EventManager\Services\AcfService\Functions\GetField;
use EventManager\Services\AcfService\Functions\GetFields;
use EventManager\Services\WPService\GetPostParent;
use EventManager\Services\WPService\GetPosts;
use EventManager\Services\WPService\GetPostTerms;
use EventManager\Services\WPService\GetTerm;
use EventManager\Services\WPService\GetThePostThumbnailUrl;
use Mockery\MockInterface;
use WP_Mock\Tools\TestCase;
use Mockery;
use WP_Error;
use WP_Post;
use WP_Term;

class EventBuilderTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private function getMockedPost(array $args): WP_Post|MockInterface
    {
        return $this->mockPost($args);
    }

    public function testIdentifierIsSet()
    {
        $wpService  = $this->getWpService();
        $acfService = $this->getAcfService();
        $post       = $this->getMockedPost(['ID' => 123]);
        $event      = new EventBuilder($post, $wpService, $acfService);

        $event->setIdentifier();

        $this->assertEquals('123', $event->toArray()['@id']);
    }

    public function testDescriptionIsSet()
    {
        $post       = $this->getMockedPost(['ID' => 123]);
        $acfService = $this->getAcfService([
            'getFields' => [$post->ID => ['description' => 'testdescription']]
        ]);
        $wpService  = $this->getWpService();

        $event = new EventBuilder($post, $wpService, $acfService);
        $event->setDescription();

        $this->assertEquals('testdescription', $event->toArray()['description']);
    }

    public function testAboutIsSet()
    {
        $post       = $this->getMockedPost(['ID' => 123]);
        $acfService = $this->getAcfService(['getFields' => [$post->ID => ['about' => 'testabout']]]);
        $wpService  = $this->getWpService();

        $event = new EventBuilder($post, $wpService, $acfService);
        $event->setAbout();

        $this->assertEquals('testabout', $event->toArray()['about']);
    }

    public function testImageIsSet()
    {
        $post       = $this->getMockedPost(['ID' => 123]);
        $acfService = $this->getAcfService();
        $wpService  = $this->getWpService([
            'getThePostThumbnailUrl' => [$post->ID => 'http://images.com/image.jpg']
        ]);

        $event = new EventBuilder($post, $wpService, $acfService);
        $event->setImage();

        $this->assertEquals('http://images.com/image.jpg', $event->toArray()['image']);
    }

    public function testLocationIsSet()
    {
        $location = ['lat' => 1.2, 'lng' => 3.4, 'address' => 'TestAddress'];

        $post       = $this->getMockedPost(['ID' => 123]);
        $wpService  = $this->getWpService();
        $acfService = $this->getAcfService(['getFields' => [$post->ID => ['location' => $location]]]);

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
        $acfService            = $this->getAcfService([
            'getFields' => [$post->ID => ['audience' => $audienceTerm->term_id]]
        ]);
        $wpService             = $this->getWpService([
            'getTerm'     => ['audience' => [$audienceTerm->term_id => $audienceTerm]],
            'getTermMeta' => [$audienceTerm->term_id => []]
        ]);

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

        $wpService  = $this->getWpService([
            'getTerm' => ['audience' => [$audienceTerm->term_id => $audienceTerm]],
        ]);
        $acfService = $this->getAcfService([
            'getFields' => [
                $post->ID      => ['audience' => $audienceTerm->term_id],
                'audience_321' => [
                    'typicalAgeRangeStart' => '18',
                    'typicalAgeRangeEnd'   => '35'
                ]
            ]
        ]);
        $event      = new EventBuilder($post, $wpService, $acfService);

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
        $wpService  = $this->getWpService();
        $acfService = $this->getAcfService(['getField' => [123 => ['occasions' => $occasions]]]);

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
        $wpService  = $this->getWpService();
        $acfService = $this->getAcfService(['getField' => [123 => ['occasions' => $occasions]]]);
        $event      = new EventBuilder($post, $wpService, $acfService);

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
        $wpService  = $this->getWpService();
        $acfService = $this->getAcfService([ 'getField' => [123 => ['occasions' => $occasions]]]);

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
        $wpService  = $this->getWpService();
        $acfService = $this->getAcfService(['getField' => [123 => ['occasions' => $occasions]]]);

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
        $wpService  = $this->getWpService();
        $acfService = $this->getAcfService(['getField' => [123 => ['occasions' => $occasions]]]);

        $eventBuilder = new EventBuilder($post, $wpService, $acfService);
        $eventBuilder->setUrl();
        $schemaArray = $eventBuilder->toArray();

        $this->assertEquals('https://www.example.com', $schemaArray['url']);
    }

    public function testEventGetsOrganizationFromTerm()
    {
        $post                       = $this->getMockedPost(['ID' => 123]);
        $organizationTerm           = Mockery::mock(\WP_Term::class);
        $organizationTerm->taxonomy = 'organization';
        $organizationTerm->term_id  = 1;
        $organizationTerm->name     = 'TestOrganization';
        $termMeta                   = [
            'url'       => 'https://www.example.com',
            'email'     => 'organizer@event.foo',
            'telephone' => '123',
            'address'   => ['address' => 'TestAddress']
        ];
        $wpServiceData              = [
            'getPostTerms' => ['organization' => [123 => [$organizationTerm]]],
        ];
        $acfData                    = [
            'getFields' => ['organization_1' => [
                'url'       => 'https://www.example.com',
                'email'     => 'organizer@event.foo',
                'telephone' => '123',
                'address'   => ['address' => 'TestAddress']
            ]]
        ];

        $wpService  = $this->getWpService($wpServiceData);
        $acfService = $this->getAcfService($acfData);

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
        $wpService  = $this->getWpService(['getPostTerms' => ['keyword' => [$post->ID => [$term]]]]);
        $acfService = $this->getAcfService();
        $event      = new EventBuilder($post, $wpService, $acfService);

        $event->setKeywords();

        $this->assertEquals(['tag1'], $event->toArray()['keywords']);
    }

    /**
     * @testdox setAbout() appends accessibility information if available
     */
    public function testSetAboutAppendsAccessabilityInformationIfAvailable()
    {
        $post       = $this->getMockedPost(['ID' => 123]);
        $fields     = ['about' => 'testabout', 'accessabilityInformation' => 'testaccessability'];
        $acfService = $this->getAcfService(['getFields' => [$post->ID => $fields]]);
        $wpService  = $this->getWpService();
        $event      = new EventBuilder($post, $wpService, $acfService);

        $event->setAbout();
        $event->setAccessabilityInformation();

        $this->assertStringContainsString("testabout", $event->toArray()['about']);
        $this->assertStringContainsString("testaccessability", $event->toArray()['about']);
    }

    private function getWpService(
        array $args = []
    ): GetThePostThumbnailUrl&GetPostTerms&GetTerm&GetPosts&GetPostParent {
        $defaultArgs = [
            'getPostTerms'           => [],
            'getThePostThumbnailUrl' => false,
            'getTermMeta'            => null,
            'getTerm'                => null,
            'getPostParent'          => null,
            'getPosts'               => []
        ];

        $data = array_merge($defaultArgs, $args);

        return new class ($data) implements
            GetThePostThumbnailUrl,
            GetPostTerms,
            GetTerm,
            GetPosts,
            GetPostParent
        {
            public function __construct(private array $data)
            {
            }

            public function getThePostThumbnailUrl(int|WP_Post $postId, string|array $size = 'post-thumbnail'): string|false
            {
                if (is_int($postId)) {
                    return $this->data['getThePostThumbnailUrl'][$postId] ?? false;
                }

                return $this->data[$postId->ID]['getThePostThumbnailUrl'] ?? false;
            }

            public function getPostTerms(int $post_id, string|array $taxonomy = 'post_tag', array $args = array()): array|WP_Error
            {
                return $this->data['getPostTerms'][$taxonomy][$post_id] ?? [];
            }

            public function getTermMeta(int $term_id, string $key = '', bool $single = false): mixed
            {
                if (!empty($key)) {
                    return $this->data['getTermMeta'][$term_id][$key] ?? [];
                }

                return $this->data['getTermMeta'][$term_id] ?? [];
            }

            public function getTerm(int|object $term, string $taxonomy = '', string $output = OBJECT, string $filter = 'raw'): WP_Term|array|WP_Error|null
            {
                if (is_int($term) && !empty($taxonomy)) {
                    return $this->data['getTerm'][$taxonomy][$term] ?? null;
                } elseif (is_int($term)) {
                    return $this->data['getTerm'][$term] ?? null;
                } elseif (!empty($taxonomy)) {
                    return $this->data['getTerm'][$taxonomy][$term->term_id] ?? null;
                }

                return $this->data['getTerm'][$term->term_id] ?? null;
            }

            public function getPostParent(int|WP_Post|null $postId): ?WP_Post
            {
                return null;
            }

            public function getPosts(array $args): array
            {
                return [];
            }
        };
    }

    private function getAcfService(array $args = []): GetField&GetFields
    {
        $defaultArgs = [
            'getField'  => [],
            'getFields' => []
        ];

        $data = array_merge($defaultArgs, $args);

        return new class ($data) implements GetField, GetFields {
            public function __construct(private array $data)
            {
            }

            public function getField(string $selector, int|false|string $postId = false, bool $formatValue = true, bool $escapeHtml = false)
            {
                return $this->data['getField'][$postId][$selector] ?? [];
            }

            public function getFields(mixed $postId = false, bool $formatValue = true, bool $escapeHtml = false): array
            {
                return $this->data['getFields'][$postId] ?? [];
            }
        };
    }
}
