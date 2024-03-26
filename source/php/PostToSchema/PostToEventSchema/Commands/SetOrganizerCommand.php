<?php

namespace EventManager\PostToSchema\PostToEventSchema\Commands;

use EventManager\PostToSchema\PostToEventSchema\Commands\Helpers\MapOpenStreetMapDataToPlace;
use EventManager\Services\AcfService\Functions\GetFields;
use EventManager\Services\WPService\GetPostTerms;
use Spatie\SchemaOrg\BaseType;
use WP_Error;

class SetOrganizerCommand implements CommandInterface
{
    public function __construct(
        private BaseType $event,
        private int $postId,
        private GetPostTerms $wpService,
        private GetFields $acfService,
        private MapOpenStreetMapDataToPlace $commandHelpers
    ) {
    }

    public function execute(): void
    {
        $organizationTerms = $this->wpService->getPostTerms($this->postId, 'organization', []);

        if (empty($organizationTerms) || $organizationTerms instanceof WP_Error) {
            return;
        }

        $organizationTerm = $organizationTerms[0];
        $termFields       = $this->acfService->getFields($organizationTerm->taxonomy . '_' . $organizationTerm->term_id) ?: [];

        $organization = new \Spatie\SchemaOrg\Organization();
        $organization->name($organizationTerm->name);
        $organization->url($termFields['url'] ?? null);
        $organization->email($termFields['email'] ?? null);
        $organization->telephone($termFields['telephone'] ?? null);

        if (isset($termFields['address'])) {
            if (is_string($termFields['address'])) {
                $termFields['address'] = unserialize($termFields['address']);
            }

            if (is_array($termFields['address'])) {
                $place = $this->commandHelpers->mapOpenStreetMapDataToPlace($termFields['address']);
                $organization->location($place);
            }
        }

        $this->event->organizer($organization);
    }
}
