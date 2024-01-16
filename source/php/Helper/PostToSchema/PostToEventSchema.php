<?php

namespace EventManager\Helper\PostToSchema;

use Spatie\SchemaOrg\Contracts\EventContract;
use WP_Post;

class PostToEventSchema implements PostToSchemaInterface
{
    public function transform(WP_Post $post, $allowRecurse = true): array
    {
        $event = new \Spatie\SchemaOrg\Event();
        $event->identifier($post->ID);
        $event->name($post->post_title);
        $event->eventStatus(get_post_meta($post->ID, 'eventStatus', true) ?: null);

        $event->doorTime(get_post_meta($post->ID, 'doorTime', true) ?: null);
        $event->startDate($this->getStartDate($event));
        $event->previousStartDate($this->getPreviousStartDate($event));
        $event->endDate(get_post_meta($post->ID, 'endDate', true) ?: null);
        $event->duration($this->getDuration($event));

        $event->description(get_post_meta($post->ID, 'description', true) ?: null);
        $event->about(get_post_meta($post->ID, 'about', true) ?: null);
        $event->image(get_the_post_thumbnail_url($post->ID) ?: null);
        $event->isAccessibleForFree($this->getIsAccessibleForFree($post));
        $event->offers($this->getOffers($event));
        $event->location($this->getLocation($post));
        $event->url(get_permalink($post->ID));
        $event->organizer($this->getOrganizer($post));
        $event->audience($this->getAudience($post));
        $event->typicalAgeRange($this->getTypicalAgeRange($post));

        if ($allowRecurse) {
            $event->superEvent($this->getSuperEvent($post));
            $event->subEvents($this->getSubEvents($post));
        }

        return $event->toArray();
    }

    public function getIsAccessibleForFree(WP_Post $post): bool
    {
        return get_post_meta($post->ID, 'isAccessibleForFree', true) ?: false;
    }

    private function getLocation(WP_Post $post): ?\Spatie\SchemaOrg\Place
    {
        $locationMeta = get_post_meta($post->ID, 'location', true) ?: null;

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
        $location->longitude($locationMeta['lng']);
        $location->latitude($locationMeta['lat']);

        return $location;
    }

    /**
     * Get the duration of the event in ISO 8601 duration format.
     *
     * @param \Spatie\SchemaOrg\Event $event
     *
     * @return string|null
     */
    private function getDuration(\Spatie\SchemaOrg\Event $event): ?string
    {
        $startDate = $event->getProperty('startDate');
        $endDate   = $event->getProperty('endDate');

        if ($startDate && $endDate) {
            $startDate = new \DateTime($startDate);
            $endDate   = new \DateTime($endDate);

            return $startDate->diff($endDate)->format('P%yY%mM%dDT%hH%iM%sS');
        }

        return null;
    }

    private function getOrganizer(WP_Post $post): ?\Spatie\SchemaOrg\Organization
    {
        $organizationId = get_post_meta($post->ID, 'organizer', true) ?: null;

        if (!$organizationId) {
            return null;
        }

        $organization = new \Spatie\SchemaOrg\Organization();
        $organization->identifier((int)$organizationId);
        $organization->name(get_the_title($organizationId));
        $organization->url(get_post_meta($organizationId, 'url', true) ?: null);
        $organization->email(get_post_meta($organizationId, 'email', true) ?: null);
        $organization->telephone(get_post_meta($organizationId, 'telephone', true) ?: null);

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
    private function getOffers(\Spatie\SchemaOrg\Event $event): ?array
    {
        if ($event->getProperty('isAccessibleForFree') === true) {
            return null;
        }

        $eventId     = $event->getProperty('identifier');
        $offers      = [];
        $nbrOfOffers = get_post_meta($eventId, 'offers', true);

        for ($i = 0; $i < $nbrOfOffers; $i++) {
            $offer = new \Spatie\SchemaOrg\Offer();
            $offer->name(get_post_meta($eventId, "offers_{$i}_name", true) ?: null);
            $offer->url(get_post_meta($eventId, "offers_{$i}_url", true) ?: null);
            $offer->price(get_post_meta($eventId, "offers_{$i}_price", true) ?: null);

            if ($offer->getProperty('price') !== null) {
                $offer->priceCurrency("SEK");
            }

            $offers[] = $offer;
        }

        return $offers;
    }

    private function getAudience(WP_Post $post): ?\Spatie\SchemaOrg\Audience
    {
        $audienceId = get_post_meta($post->ID, 'audience', true) ?: null;

        if (!$audienceId) {
            return null;
        }

        // Get audience term
        $audienceTerm = get_term($audienceId);
        $audience     = new \Spatie\SchemaOrg\Audience();
        $audience->identifier((int)$audienceTerm->term_id);
        $audience->name($audienceTerm->name);

        return $audience;
    }

    private function getTypicalAgeRange(WP_Post $post): ?string
    {
        $rangeStart = get_post_meta($post->ID, 'typicalAgeRangeStart', true) ?: null;
        $rangeEnd   = get_post_meta($post->ID, 'typicalAgeRangeEnd', true) ?: null;

        if ($rangeStart && $rangeEnd) {
            return "{$rangeStart}-{$rangeEnd}";
        }

        if ($rangeStart) {
            return "{$rangeStart}-";
        }

        return null;
    }

    private function getPreviousStartDate(\Spatie\SchemaOrg\Event $event): ?string
    {
        $eventStatus          = $event->getProperty('eventStatus');
        $previousStartDate    = get_post_meta($event->getProperty('identifier'), 'startDate', true) ?: null;
        $rescheduledStartDate = get_post_meta($event->getProperty('identifier'), 'rescheduledStartDate', true) ?: null;

        if ($eventStatus === 'https://schema.org/EventRescheduled') {
            return $previousStartDate;
        }

        if ($previousStartDate && $rescheduledStartDate) {
            return $previousStartDate;
        }

        return null;
    }

    private function getStartDate(\Spatie\SchemaOrg\Event $event): ?string
    {
        $eventStatus          = $event->getProperty('eventStatus');
        $previousStartDate    = get_post_meta($event->getProperty('identifier'), 'startDate', true) ?: null;
        $rescheduledStartDate = get_post_meta($event->getProperty('identifier'), 'rescheduledStartDate', true) ?: null;

        if ($eventStatus === 'https://schema.org/EventRescheduled') {
            return $rescheduledStartDate;
        }

        return $previousStartDate;
    }

    private function getSuperEvent(WP_Post $post): ?array
    {
        $superEventPost = get_post_parent($post->ID);

        if (!$superEventPost) {
            return null;
        }

        return $this->transform($superEventPost, false);
    }

    private function getSubEvents(WP_Post $post): ?array
    {
        $subEventPosts = get_posts([
            'post_parent' => $post->ID,
            'post_type'   => 'event',
            'numberposts' => -1
        ]);

        if (empty($subEventPosts)) {
            return null;
        }

        return array_map(fn($subPost) => $this->transform($subPost, false), $subEventPosts);
    }
}
