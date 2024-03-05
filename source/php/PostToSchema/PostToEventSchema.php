<?php

namespace EventManager\PostToSchema;

use EventManager\Helper\Arrayable;
use EventManager\PostToSchema\Schedule\ScheduleByDayFactory;
use EventManager\PostToSchema\Schedule\ScheduleByMonthFactory;
use EventManager\PostToSchema\Schedule\ScheduleByWeekFactory;
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
        $this->setIdentifier();
        $this->setName();
        $this->setDescription();
        $this->setAbout();
        $this->setImage();
        $this->setIsAccessibleForFree();
        $this->setLocation();
        $this->setUrl();
        $this->setAudience();
        $this->setTypicalAgeRange();
        $this->setOrganizer();

        $this->setStartDate();
        $this->setEndDate();
        $this->setDuration();
        $this->setSchedule();

        if ($this->allowRecurse) {
            $this->setSuperEvent();
            $this->setSubEvents();
        }
    }

    public function toArray(): array
    {
        return $this->event->toArray();
    }

    private function setIdentifier(): void
    {
        $this->event->identifier($this->post->ID);
    }

    private function setName(): void
    {
        $this->event->name($this->post->post_title);
    }

    private function setDescription(): void
    {
        $this->event->description($this->wp->getPostMeta($this->post->ID, 'description', true) ?: null);
    }

    private function setAbout(): void
    {
        $this->event->about($this->wp->getPostMeta($this->post->ID, 'about', true) ?: null);
    }

    private function setImage(): void
    {
        $this->event->image($this->wp->getThePostThumbnailUrl($this->post->ID) ?: null);
    }

    private function setUrl(): void
    {
        $numberOfOccasions = $this->wp->getPostMeta($this->post->ID, 'occasions', true) ?: [];

        if (!is_numeric($numberOfOccasions) || (int)$numberOfOccasions !== 1) {
            return;
        }

        $occasionsUrl = $this->wp->getPostMeta($this->post->ID, 'occasions_0_url', true) ?: null;

        if ($occasionsUrl && filter_var($occasionsUrl, FILTER_VALIDATE_URL)) {
            $this->event->url($occasionsUrl);
        }
    }

    private function setIsAccessibleForFree(): void
    {
        $this->event->isAccessibleForFree((bool)$this->wp->getPostMeta($this->post->ID, 'isAccessibleForFree', true));
    }

    private function setLocation(): void
    {
        $locationMeta = $this->wp->getPostMeta($this->post->ID, 'location', true) ?: null;

        if (!$locationMeta) {
            return;
        }

        $place = $this->getPlaceFromAcfMapField($locationMeta);
        $this->event->location($place);
    }

    private function getPlaceFromAcfMapField(array $acfMapField)
    {
        // Address
        $address       = new \Spatie\SchemaOrg\PostalAddress();
        $streetName    = $acfMapField['street_name'] ?? '';
        $streetNumber  = $acfMapField['street_number'] ?? '';
        $streetAddress = trim("{$streetName} {$streetNumber}");
        $address->streetAddress($streetAddress);
        $address->addressLocality($acfMapField['city'] ?? null);
        $address->postalCode($acfMapField['post_code'] ?? null);
        $address->addressCountry($acfMapField['country_short'] ?? null);

        // Location
        $location = new \Spatie\SchemaOrg\Place();
        $location->address($address);
        $location->longitude($acfMapField['lng'] ?? null);
        $location->latitude($acfMapField['lat'] ?? null);

        return $location->toArray();
    }

    private function setOrganizer(): void
    {
        $organizationTerms = $this->wp->wpGetPostTerms($this->post->ID, 'organization', []);

        if (empty($organizationTerms) || $this->wp->isWPError($organizationTerms)) {
            return;
        }

        $organizationTerm = $organizationTerms[0];
        $url              = $this->wp->getTermMeta($organizationTerm->term_id, 'url', true) ?: null;
        $email            = $this->wp->getTermMeta($organizationTerm->term_id, 'email', true) ?: null;
        $telephone        = $this->wp->getTermMeta($organizationTerm->term_id, 'telephone', true) ?: null;
        $address          = $this->wp->getTermMeta($organizationTerm->term_id, 'address', true) ?: null;

        $organization = new \Spatie\SchemaOrg\Organization();
        $organization->name($organizationTerm->name);
        $organization->url($url);
        $organization->email($email);
        $organization->telephone($telephone);

        if ($address) {
            $place = $this->getPlaceFromAcfMapField($address);
            $organization->location($place);
        }

        $this->event->organizer($organization);
    }

    private function setAudience(): void
    {
        $audienceId = $this->wp->getPostMeta($this->post->ID, 'audience', true) ?: null;

        if (!$audienceId) {
            return;
        }

        // Get audience term
        $audienceTerm = $this->wp->getTerm($audienceId);
        $audience     = new \Spatie\SchemaOrg\Audience();
        $audience->identifier((int)$audienceTerm->term_id);
        $audience->name($audienceTerm->name);

        $this->event->audience($audience);
    }

    private function setTypicalAgeRange(): void
    {
        $audience = $this->event->getProperty('audience');
        $range    = null;

        if (!$audience || !$audience->getProperty('identifier')) {
            return;
        }

        $termId     = $audience->getProperty('identifier');
        $rangeStart = $this->wp->getTermMeta($termId, 'typicalAgeRangeStart', true) ?: null;
        $rangeEnd   = $this->wp->getTermMeta($termId, 'typicalAgeRangeEnd', true) ?: null;

        if ($rangeStart && $rangeEnd) {
            $range = "{$rangeStart}-{$rangeEnd}";
        } elseif ($rangeStart) {
            $range = "{$rangeStart}-";
        }

        $this->event->typicalAgeRange($range);
    }

    private function setStartDate(): void
    {
        $numberOfOccasions = $this->wp->getPostMeta($this->post->ID, 'occasions', true) ?: [];
        $dateTime          = null;

        if (!is_numeric($numberOfOccasions) || (int)$numberOfOccasions !== 1) {
            return;
        }

        $repeat = $this->wp->getPostMeta($this->post->ID, 'occasions_0_repeat', true) ?: null;
        $date   = $this->wp->getPostMeta($this->post->ID, 'occasions_0_startDate', true) ?: null;
        $time   = $this->wp->getPostMeta($this->post->ID, 'occasions_0_startTime', true) ?: null;

        if ($repeat !== 'no') {
            return;
        }

        // Combine date and time
        if ($date && $time) {
            $date     = new \DateTime("{$date} {$time}");
            $dateTime = $date->format('Y-m-d H:i');
        }

        $this->event->startDate($dateTime);
    }

    private function setEndDate(): void
    {
        $numberOfOccasions = $this->wp->getPostMeta($this->post->ID, 'occasions', true) ?: [];
        $dateTime          = null;

        if (!is_numeric($numberOfOccasions) || (int)$numberOfOccasions !== 1) {
            return;
        }

        $repeat = $this->wp->getPostMeta($this->post->ID, 'occasions_0_repeat', true) ?: null;
        $date   = $this->wp->getPostMeta($this->post->ID, 'occasions_0_endDate', true) ?: null;
        $time   = $this->wp->getPostMeta($this->post->ID, 'occasions_0_endTime', true) ?: null;

        if ($repeat !== 'no') {
            return;
        }

        // Combine date and time
        if ($date && $time) {
            $date     = new \DateTime("{$date} {$time}");
            $dateTime = $date->format('Y-m-d H:i');
        }

        $this->event->endDate($dateTime);
    }

    private function setDuration(): void
    {
        $startDate = $this->event->getProperty('startDate');
        $endDate   = $this->event->getProperty('endDate');
        $duration  = null;

        if ($startDate && $endDate) {
            $startDate = new \DateTime($startDate);
            $endDate   = new \DateTime($endDate);

            $duration = $startDate->diff($endDate)->format('P%yY%mM%dDT%hH%iM%sS');
        }

        $this->event->duration($duration);
    }

    private function setSchedule(): void
    {
        $schedules         = [];
        $numberOfOccasions = $this->wp->getPostMeta($this->post->ID, 'occasions', true) ?: [];

        if (!is_numeric($numberOfOccasions) || (int)$numberOfOccasions < 1) {
            return;
        }

        $getMetaRow = fn ($i, $key) => $this->wp->getPostMeta($this->post->ID, "occasions_{$i}_{$key}", true) ?: null;

        $schedules = array_map(function ($i) use ($getMetaRow) {
            $repeat    = $getMetaRow($i, 'repeat');
            $startDate = $getMetaRow($i, 'startDate');
            $startTime = $getMetaRow($i, 'startTime');
            $endDate   = $getMetaRow($i, 'endDate');
            $endTime   = $getMetaRow($i, 'endTime');

            switch ($repeat) {
                case 'byDay':
                    $daysInterval    = $getMetaRow($i, 'daysInterval') ?: 1;
                    $scheduleFactory = new ScheduleByDayFactory(
                        $startDate,
                        $endDate,
                        $startTime,
                        $endTime,
                        $daysInterval
                    );
                    return $scheduleFactory->create();
                case 'byWeek':
                    $daysInterval    = $getMetaRow($i, 'weeksInterval') ?: 1;
                    $weekDays        = $getMetaRow($i, 'weekDays') ?: [];
                    $scheduleFactory = new ScheduleByWeekFactory(
                        $startDate,
                        $endDate,
                        $startTime,
                        $endTime,
                        $daysInterval,
                        $weekDays
                    );
                    return $scheduleFactory->create();
                case 'byMonth':
                    $daysInterval    = $getMetaRow($i, 'monthsInterval') ?: 1;
                    $monthDay        = $getMetaRow($i, 'monthDay') ?: null;
                    $monthDayNumber  = $getMetaRow($i, 'monthDayNumber') ?: null;
                    $monthDayLiteral = $getMetaRow($i, 'monthDayLiteral') ?: null;
                    $scheduleFactory = new ScheduleByMonthFactory(
                        $startDate,
                        $endDate,
                        $startTime,
                        $endTime,
                        $daysInterval,
                        $monthDay,
                        $monthDayNumber,
                        $monthDayLiteral
                    );
                    return $scheduleFactory->create();
                dafault:
                    return null;
            }
        }, range(0, $numberOfOccasions - 1));

        $schedules = array_filter($schedules); // Remove null values

        $this->event->eventSchedule($schedules ?: null);
    }

    private function setSuperEvent(): void
    {
        $superEventPost = $this->wp->getPostParent($this->post->ID);

        if (!$superEventPost) {
            return;
        }

        $superEvent = new self($this->wp, $superEventPost, false);

        $this->event->superEvent($superEvent->toArray());
    }

    private function setSubEvents(): void
    {
        $subEventPosts = $this->wp->getPosts([
            'post_parent' => $this->post->ID,
            'post_type'   => 'event',
            'numberposts' => -1
        ]);

        if (empty($subEventPosts)) {
            return;
        }

        $subEvents = array_map(function ($subPost) {
            $subEvent = new self($this->wp, $subPost, false);

            return $subEvent->toArray();
        }, $subEventPosts);

        $this->event->subEvents($subEvents);
    }
}
