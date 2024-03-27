<?php

namespace EventManager\PostToSchema\PostToEventSchema;

use EventManager\PostToSchema\IPostToSchemaAdapter;
use EventManager\PostToSchema\Mappers\IStringToSchemaMapper;
use EventManager\PostToSchema\PostToEventSchema\Commands\Helpers\MapOpenStreetMapDataToPlace;
use EventManager\Services\AcfService\Functions\GetField;
use EventManager\Services\AcfService\Functions\GetFields;
use EventManager\Services\WPService\GetPostParent;
use EventManager\Services\WPService\GetPosts;
use EventManager\Services\WPService\GetPostTerms;
use EventManager\Services\WPService\GetTerm;
use EventManager\Services\WPService\GetThePostThumbnailUrl;
use Spatie\SchemaOrg\BaseType;
use WP_Post;

class PostToEventSchema implements IPostToSchemaAdapter
{
    protected WP_Post $post;
    public ?BaseType $event = null;
    protected array $fields;

    public function __construct(
        protected IStringToSchemaMapper $stringToSchemaMapper,
        protected GetThePostThumbnailUrl&GetPostTerms&GetTerm&GetPosts&GetPostParent $wpService,
        protected GetField&GetFields $acfService,
        protected MapOpenStreetMapDataToPlace $commandHelpers,
        protected bool $allowSubAndSuperEvents = true,
    ) {
    }

    public function setupFields(WP_Post $post): void
    {
        $this->post   = $post;
        $this->fields = $this->acfService->getFields($this->post->ID) ?: [];
        $this->event  = $this->stringToSchemaMapper->map($this->fields['type'] ?? 'Event');
    }

    public function getSchema(WP_Post $post): BaseType
    {
        $this->setupFields($post);

        $commands = [
            new Commands\SetIdentifier($this->event, $this->post),
            new Commands\SetName($this->event, $this->post),
            new Commands\SetDescription($this->event, $this->fields),
            new Commands\SetAbout($this->event, $this->fields),
            new Commands\SetAccessabilityInformation($this->event, $this->fields),
            new Commands\SetImage($this->event, $this->post->ID, $this->wpService),
            new Commands\SetIsAccessibleForFree($this->event, $this->fields),
            new Commands\SetLocation($this->event, $this->fields, $this->commandHelpers),
            new Commands\SetUrl($this->event, $this->fields),
            new Commands\SetAudience($this->event, $this->fields, $this->wpService),
            new Commands\SetTypicalAgeRange($this->event, $this->acfService),
            new Commands\SetDates($this->event, $this->fields),
            new Commands\SetDuration($this->event),
            new Commands\SetKeywords($this->event, $this->post->ID, $this->wpService),
            new Commands\SetSchedule($this->event, $this->fields),
            new Commands\SetOrganizer($this->event, $this->post->ID, $this->wpService, $this->acfService, $this->commandHelpers),
        ];

        if ($this->allowSubAndSuperEvents) {
            $commands[] = new Commands\SetSubEvents(
                $this->event,
                $this->post->ID,
                $this->wpService,
                $this->acfService,
                new self($this->stringToSchemaMapper, $this->wpService, $this->acfService, $this->commandHelpers, false)
            );
            $commands[] = new Commands\SetSuperEvent(
                $this->event,
                $this->post->ID,
                $this->wpService,
                new self($this->stringToSchemaMapper, $this->wpService, $this->acfService, $this->commandHelpers, false)
            );
        }

        $this->executeCommands($commands);

        return $this->event;
    }

    private function executeCommands(array $commands)
    {
        foreach ($commands as $command) {
            $command->execute();
        }
    }
}
