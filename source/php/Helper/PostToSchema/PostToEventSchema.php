<?php

namespace EventManager\Helper\PostToSchema;

use EventManager\Helper\Arrayable;
use EventManager\Services\WPService\WPService;
use Spatie\SchemaOrg\BaseType;
use WP_Post;

class PostToEventSchema implements Arrayable
{
    protected WPService $wp;
    protected BaseType $event;
    protected WP_Post $post;
    private bool $allowRecurse;

    public function __construct(
        WPService $wp,
        WP_Post $post,
        $allowRecurse = true
    ) {
        $this->wp           = $wp;
        $this->post         = $post;
        $this->allowRecurse = $allowRecurse;
        $this->event        = new \Spatie\SchemaOrg\Event();
        $this->addPropertiesToEvent();
    }

    private function addPropertiesToEvent()
    {
        $this->event->identifier($this->post->ID);
        $this->event->name($this->post->post_title);

        $this->event->startDate($this->getStartDate($this->event));
        $this->event->previousStartDate($this->getPreviousStartDate($this->event));
        $this->event->endDate($this->wp->getPostMeta($this->post->ID, 'endDate', true) ?: null);
        $this->event->duration($this->getDuration($this->event));

        $this->event->description($this->wp->getPostMeta($this->post->ID, 'description', true) ?: null);
        $this->event->about($this->wp->getPostMeta($this->post->ID, 'about', true) ?: null);
        $this->event->image($this->wp->getThePostThumbnailUrl($this->post->ID) ?: null);
        $this->event->isAccessibleForFree($this->getIsAccessibleForFree());
        $this->event->offers($this->getOffers($this->event));

        $this->event->location($this->getLocation());

        $this->event->url($this->wp->getPermalink($this->post->ID));
        $this->event->audience($this->getAudience());
        $this->event->typicalAgeRange($this->getTypicalAgeRange());
        $this->event->organizer($this->getOrganizer());

        if ($this->allowRecurse) {
            $this->event->superEvent($this->getSuperEvent());
            $this->event->subEvents($this->getSubEvents());
        }
    }

    public function toArray(): array
    {
        return $this->event->toArray();
    }

    private function getIsAccessibleForFree(): bool
    {
        return (bool)$this->wp->getPostMeta($this->post->ID, 'isAccessibleForFree', true);
    }

    private function getLocation(): ?\Spatie\SchemaOrg\Place
    {
        $locationMeta = $this->wp->getPostMeta($this->post->ID, 'location', true) ?: null;

        if (!$locationMeta) {
            return null;
        }

        // Address
        $address      = new \Spatie\SchemaOrg\PostalAddress();
        $streetName   = $locationMeta['street_name'] ?? '';
        $streetNumber = $locationMeta['street_number'] ?? '';
        $address->streetAddress("{$streetName} {$streetNumber}");
        $address->addressLocality($locationMeta['city'] ?? null);
        $address->postalCode($locationMeta['post_code'] ?? null);
        $address->addressCountry($locationMeta['country_short'] ?? null);

        // Location
        $location = new \Spatie\SchemaOrg\Place();
        $location->address($address);
        $location->longitude($locationMeta['lng'] ?? null);
        $location->latitude($locationMeta['lat'] ?? null);

        return $location;
    }

    /**
     * Get the duration of the event in ISO 8601 duration format.
     *
     * @param \Spatie\SchemaOrg\Event $event
     *
     * @return string|null
     */
    private function getDuration(): ?string
    {
        $startDate = $this->event->getProperty('startDate');
        $endDate   = $this->event->getProperty('endDate');

        if ($startDate && $endDate) {
            $startDate = new \DateTime($startDate);
            $endDate   = new \DateTime($endDate);

            return $startDate->diff($endDate)->format('P%yY%mM%dDT%hH%iM%sS');
        }

        return null;
    }

    private function getOrganizer(): ?\Spatie\SchemaOrg\Organization
    {
        $organizationId = $this->wp->getPostMeta($this->post->ID, 'organizer', true) ?: null;

        if (!$organizationId || !is_numeric($organizationId)) {
            return null;
        }

        $organization = new \Spatie\SchemaOrg\Organization();
        $organization->identifier((int)$organizationId);
        $organization->name(get_the_title($organizationId));
        $organization->url($this->wp->getPostMeta($organizationId, 'url', true) ?: null);
        $organization->email($this->wp->getPostMeta($organizationId, 'email', true) ?: null);
        $organization->telephone($this->wp->getPostMeta($organizationId, 'telephone', true) ?: null);

        return $organization;
    }

    /**
     * Get the offers for the event.
     * If the event is free, return null.
     *
     * @param \Spatie\SchemaOrg\Event $event
     *
     * @return \Spatie\SchemaOrg\Offer[]|null
     */
    private function getOffers(): ?array
    {
        if ($this->event->getProperty('isAccessibleForFree') === true) {
            return null;
        }

        $offers      = [];
        $nbrOfOffers = $this->wp->getPostMeta($this->post->ID, 'offers', true) ?: 0;

        for ($i = 0; $i < $nbrOfOffers; $i++) {
            $offer = new \Spatie\SchemaOrg\Offer();
            $offer->name($this->wp->getPostMeta($this->post->ID, "offers_{$i}_name", true) ?: null);
            $offer->url($this->wp->getPostMeta($this->post->ID, "offers_{$i}_url", true) ?: null);
            $offer->price($this->wp->getPostMeta($this->post->ID, "offers_{$i}_price", true) ?: null);

            if ($offer->getProperty('price') !== null) {
                $offer->priceCurrency("SEK");
            }

            $offers[] = $offer;
        }

        return $offers;
    }

    private function getAudience(): ?\Spatie\SchemaOrg\Audience
    {
        $audienceId = $this->wp->getPostMeta($this->post->ID, 'audience', true) ?: null;

        if (!$audienceId) {
            return null;
        }

        // Get audience term
        $audienceTerm = $this->wp->getTerm($audienceId);
        $audience     = new \Spatie\SchemaOrg\Audience();
        $audience->identifier((int)$audienceTerm->term_id);
        $audience->name($audienceTerm->name);

        return $audience;
    }

    private function getTypicalAgeRange(): ?string
    {
        $audience = $this->getAudience();

        if (!$audience || !$audience->getProperty('identifier')) {
            return null;
        }

        $termId     = $audience->getProperty('identifier');
        $rangeStart = $this->wp->getTermMeta($termId, 'typicalAgeRangeStart', true) ?: null;
        $rangeEnd   = $this->wp->getTermMeta($termId, 'typicalAgeRangeEnd', true) ?: null;

        if ($rangeStart && $rangeEnd) {
            return "{$rangeStart}-{$rangeEnd}";
        }

        if ($rangeStart) {
            return "{$rangeStart}-";
        }

        return null;
    }

    private function getPreviousStartDate(): ?string
    {
        $eventStatus          = $this->event->getProperty('eventStatus');
        $previousStartDate    = $this->wp->getPostMeta($this->post->ID, 'startDate', true) ?: null;
        $rescheduledStartDate = $this->wp->getPostMeta($this->post->ID, 'rescheduledStartDate', true) ?: null;

        if ($eventStatus === 'https://schema.org/EventRescheduled') {
            return $previousStartDate;
        }

        if ($previousStartDate && $rescheduledStartDate) {
            return $previousStartDate;
        }

        return null;
    }

    private function getStartDate(): ?string
    {
        $eventStatus          = $this->event->getProperty('eventStatus');
        $previousStartDate    = $this->wp->getPostMeta($this->post->ID, 'startDate', true) ?: null;
        $rescheduledStartDate = $this->wp->getPostMeta($this->post->ID, 'rescheduledStartDate', true) ?: null;

        if ($eventStatus === 'https://schema.org/EventRescheduled') {
            return $rescheduledStartDate;
        }

        return $previousStartDate;
    }

    private function getSuperEvent(): ?array
    {
        $superEventPost = $this->wp->getPostParent($this->post->ID);

        if (!$superEventPost) {
            return null;
        }

        $superEvent = new self($this->wp, $superEventPost, false);

        return $superEvent->toArray();
    }

    private function getSubEvents(): ?array
    {
        $subEventPosts = $this->wp->getPosts([
            'post_parent' => $this->post->ID,
            'post_type'   => 'event',
            'numberposts' => -1
        ]);

        if (empty($subEventPosts)) {
            return null;
        }

        return array_map(function ($subPost) {
            $subEvent = new self($this->wp, $subPost, false);

            return $subEvent->toArray();
        }, $subEventPosts);
    }
}
