<?php

namespace EventManager\PostToSchema\PostToEventSchema;

use EventManager\PostToSchema\IPostToSchemaAdapter;
use EventManager\PostToSchema\Mappers\IStringToSchemaMapper;
use EventManager\PostToSchema\PostToEventSchema\Commands\Helpers\MapOpenStreetMapDataToPlace;
use AcfService\Contracts\GetField;
use AcfService\Contracts\GetFields;
use WpService\Contracts\GetPostParent;
use WpService\Contracts\GetPosts;
use WpService\Contracts\GetPostTerms;
use WpService\Contracts\GetTerm;
use WpService\Contracts\GetThePostThumbnailUrl;
use Spatie\SchemaOrg\BaseType;
use WP_Post;

class PostToEventSchema implements IPostToSchemaAdapter
{
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

    public function getSchema(WP_Post $post): BaseType
    {
        $this->fields = $this->acfService->getFields($post->ID) ?: [];
        $this->event  = $this->stringToSchemaMapper->map($this->fields['type'] ?? 'Event');
        $childPosts   = $this->wpService->getPosts(['post_parent' => $post->ID, 'post_type' => $post->post_type]);

        $commands   = [];
        $commands[] = new Commands\SetIdentifier($this->event, $post);
        $commands[] = new Commands\SetName($this->event, $post);
        $commands[] = new Commands\SetDescription($this->event, $this->fields);
        $commands[] = new Commands\SetAbout($this->event, $this->fields);
        $commands[] = new Commands\SetAccessabilityInformation($this->event, $this->fields);
        $commands[] = new Commands\SetImage($this->event, $post->ID, $this->wpService);
        $commands[] = new Commands\SetIsAccessibleForFree($this->event, $this->fields);
        $commands[] = new Commands\SetOffers($this->event, $this->fields);
        $commands[] = new Commands\SetLocation($this->event, $this->fields, $this->commandHelpers);
        $commands[] = new Commands\SetUrl($this->event, $this->fields);
        $commands[] = new Commands\SetOrganizer($this->event, $post->ID, $this->wpService, $this->acfService, $this->commandHelpers);
        $commands[] = new Commands\SetAudience($this->event, $this->fields, $this->wpService);
        $commands[] = new Commands\SetTypicalAgeRange($this->event, $this->acfService);
        $commands[] = new Commands\SetKeywords($this->event, $post->ID, $this->wpService);

        if ($this->allowSubAndSuperEvents) {
            $commands[] = new Commands\SetSubEvents(
                $this->event,
                $post->ID,
                $this->wpService,
                $this->acfService,
                new self($this->stringToSchemaMapper, $this->wpService, $this->acfService, $this->commandHelpers, false)
            );
            $commands[] = new Commands\SetSuperEvent(
                $this->event,
                $post->ID,
                $this->wpService,
                new self($this->stringToSchemaMapper, $this->wpService, $this->acfService, $this->commandHelpers, false)
            );
        }

        if (!empty($childPosts)) {
            // This is a SuperEvent
            $commands[] = new Commands\SetDatesFromSubEvents($this->event);
        } else {
            $commands[] = new Commands\SetSchedule($this->event, $this->fields);
            $commands[] = new Commands\SetDates($this->event, $this->fields);
            $commands[] = new Commands\SetDuration($this->event);
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
