<?php

namespace EventManager\PostToSchema;

use EventManager\PostToSchema\Mappers\IStringToSchemaMapper;
use EventManager\Services\AcfService\AcfService;
use EventManager\Services\WPService\WPService;
use WP_Post;

class EventFactory implements IEventFactory
{
    public function __construct(
        private WPService $wPService,
        private AcfService $acfService,
        private IStringToSchemaMapper $stringToSchemaMapper
    ) {
    }

    public function create(WP_Post $post): EventBuilder
    {
        return new EventBuilder($post, $this->wPService, $this->acfService, $this->stringToSchemaMapper);
    }
}
