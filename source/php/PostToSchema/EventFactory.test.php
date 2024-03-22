<?php

namespace EventManager\PostToSchema;

use Mockery;
use PHPUnit\Framework\TestCase;

class EventFactoryTest extends TestCase
{
    /**
     * @testdox create() returns an instance of EventBuilder
     */
    public function testCreateReturnsEventBuilder(): void
    {
        $post                 = Mockery::mock(\WP_Post::class);
        $wpService            = $this->createMock(\EventManager\Services\WPService\WPService::class);
        $acfService           = $this->createMock(\EventManager\Services\AcfService\AcfService::class);
        $stringToSchemaMapper = $this->createMock(\EventManager\PostToSchema\Mappers\IStringToSchemaMapper::class);

        $factory = new EventFactory($wpService, $acfService, $stringToSchemaMapper);
        $this->assertInstanceOf(EventBuilder::class, $factory->create($post));
    }
}
