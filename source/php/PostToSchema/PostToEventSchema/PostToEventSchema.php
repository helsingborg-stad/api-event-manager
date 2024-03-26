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
            new Commands\SetIdentifierCommand($this->event, $this->post),
            new Commands\SetNameCommand($this->event, $this->post),
            new Commands\SetDescriptionCommand($this->event, $this->fields),
            new Commands\SetAboutCommand($this->event, $this->fields),
            new Commands\SetAccessabilityInformationCommand($this->event, $this->fields),
            new Commands\SetImageCommand($this->event, $this->post->ID, $this->wpService),
            new Commands\SetIsAccessibleForFreeCommand($this->event, $this->fields),
            new Commands\SetLocationCommand($this->event, $this->fields, $this->commandHelpers),
            new Commands\SetUrlCommand($this->event, $this->fields),
            new Commands\SetAudienceCommand($this->event, $this->fields, $this->wpService),
            new Commands\SetTypicalAgeRangeCommand($this->event, $this->acfService),
            new Commands\SetDatesCommand($this->event, $this->fields),
            new Commands\SetDurationCommand($this->event),
            new Commands\SetKeywordsCommand($this->event, $this->post->ID, $this->wpService),
            new Commands\SetScheduleCommand($this->event, $this->fields),
            new Commands\SetOrganizerCommand($this->event, $this->post->ID, $this->wpService, $this->acfService, $this->commandHelpers),
        ];

        if ($this->allowSubAndSuperEvents) {
            $commands[] = new Commands\SetSubEventsCommand(
                $this->event,
                $this->post->ID,
                $this->wpService,
                $this->acfService,
                new self($this->stringToSchemaMapper, $this->wpService, $this->acfService, $this->commandHelpers, false)
            );
            $commands[] = new Commands\SetSuperEventCommand(
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
