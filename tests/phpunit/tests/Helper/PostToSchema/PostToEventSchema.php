<?php

namespace EventManager\Tests\Help\PostToSchema;

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

    public function testOffersNotSetIfEventIsFree()
    {
        $offerMeta              = ['offers' => 1, 'offers_0_name' => 'TestOffer'];
        $meta                   = ['isAccessibleForFree' => '1'] + $offerMeta;
        [$post, $wpServiceMock] = $this->getBasicPropertiesTestDependencies($meta);
        $postToEventSchema      = new PostToEventSchema($wpServiceMock, $post);
        $schemaArray            = $postToEventSchema->toArray();

        $this->assertArrayNotHasKey('offers', $schemaArray);
    }

    public function testOffersIsSetIfEventIsNotFree()
    {
        $offerMeta              = ['offers' => 1, 'offers_0_name' => 'TestOffer'];
        $meta                   = ['isAccessibleForFree' => '0'] + $offerMeta;
        [$post, $wpServiceMock] = $this->getBasicPropertiesTestDependencies($meta);
        $postToEventSchema      = new PostToEventSchema($wpServiceMock, $post);
        $schemaArray            = $postToEventSchema->toArray();

        $this->assertArrayHasKey('offers', $schemaArray);
        $this->assertEquals('TestOffer', $schemaArray['offers'][0]['name']);
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
