<?php

namespace EventManager\PostToSchema;

use EventManager\PostToSchema\Schedule\ScheduleByDayFactory;
use EventManager\PostToSchema\Schedule\ScheduleByMonthFactory;
use EventManager\PostToSchema\Schedule\ScheduleByWeekFactory;
use EventManager\Services\AcfService\AcfService;
use EventManager\Services\WPService\WPService;
use Spatie\SchemaOrg\BaseType;
use WP_Post;

class EventBuilder implements BaseTypeBuilder
{
    protected BaseType $event;
    protected WP_Post $post;
    protected WPService $wp;
    protected AcfService $acf;

    public function __construct(
        WP_Post $post,
        WPService $wp,
        AcfService $acf
    ) {
        $this->post  = $post;
        $this->wp    = $wp;
        $this->acf   = $acf;
        $this->event = new \Spatie\SchemaOrg\Event();
    }

    public function build(): BaseType
    {
        $this
            ->setIdentifier()
            ->setName()
            ->setDescription()
            ->setAbout()
            ->setImage()
            ->setIsAccessibleForFree()
            ->setLocation()
            ->setUrl()
            ->setAudience()
            ->setTypicalAgeRange()
            ->setOrganizer()
            ->setStartDate()
            ->setEndDate()
            ->setDuration()
            ->setKeywords()
            ->setSchedule()
            ->setSuperEvent()
            ->setSubEvents();

        return $this->event;
    }

    public function toArray(): array
    {
        return $this->event->toArray();
    }

    public function setIdentifier(): EventBuilder
    {
        $this->event->identifier($this->post->ID);
        return $this;
    }

    public function setName(): EventBuilder
    {
        $this->event->name($this->post->post_title);
        return $this;
    }

    public function setDescription(): EventBuilder
    {
        $this->event->description($this->wp->getPostMeta($this->post->ID, 'description', true) ?: null);
        return $this;
    }

    public function setAbout(): EventBuilder
    {
        $this->event->about($this->wp->getPostMeta($this->post->ID, 'about', true) ?: null);
        return $this;
    }

    public function setImage(): EventBuilder
    {
        $this->event->image($this->wp->getThePostThumbnailUrl($this->post->ID) ?: null);
        return $this;
    }

    public function setUrl(): EventBuilder
    {
        $occasions = $this->acf->getField('occasions', $this->post->ID) ?: [];

        if (empty($occasions) || count($occasions) !== 1) {
            return $this;
        }

        $occasionsUrl = $occasions[0]['url'] ?: null;

        if ($occasionsUrl && filter_var($occasionsUrl, FILTER_VALIDATE_URL)) {
            $this->event->url($occasionsUrl);
        }

        return $this;
    }

    public function setIsAccessibleForFree(): EventBuilder
    {
        $this->event->isAccessibleForFree((bool)$this->wp->getPostMeta($this->post->ID, 'isAccessibleForFree', true));
        return $this;
    }

    public function setLocation(): EventBuilder
    {
        $location = $this->wp->getPostMeta($this->post->ID, 'location', true) ?: null;

        if (!$location) {
            return $this;
        }

        $place = new \Spatie\SchemaOrg\Place();
        $place->address($location['address'] ?? null);
        $place->latitude($location['latitude'] ?? null);
        $place->longitude($location['longitude'] ?? null);

        $this->event->location($place);

        return $this;
    }

    public function setOrganizer(): EventBuilder
    {
        $organizationTerms = $this->wp->getPostTerms($this->post->ID, 'organization', []);

        if (empty($organizationTerms) || $this->wp->isWPError($organizationTerms)) {
            return $this;
        }

        $organizationTerm = $organizationTerms[0];
        $url              = $this->wp->getTermMeta($organizationTerm->term_id, 'url', true) ?: null;
        $email            = $this->wp->getTermMeta($organizationTerm->term_id, 'email', true) ?: null;
        $telephone        = $this->wp->getTermMeta($organizationTerm->term_id, 'telephone', true) ?: null;
        $location         = $this->wp->getTermMeta($organizationTerm->term_id, 'address', true) ?: null;

        $organization = new \Spatie\SchemaOrg\Organization();
        $organization->name($organizationTerm->name);
        $organization->url($url);
        $organization->email($email);
        $organization->telephone($telephone);

        if ($location) {
            $place = new \Spatie\SchemaOrg\Place();
            $place->address($location['address'] ?? null);
            $place->latitude($location['latitude'] ?? null);
            $place->longitude($location['longitude'] ?? null);
            $organization->location($place);
        }

        $this->event->organizer($organization);
        return $this;
    }

    public function setAudience(): EventBuilder
    {
        $audienceId = $this->wp->getPostMeta($this->post->ID, 'audience', true) ?: null;

        if (!$audienceId) {
            return $this;
        }

        // Get audience term
        $audienceTerm = $this->wp->getTerm($audienceId);
        $audience     = new \Spatie\SchemaOrg\Audience();
        $audience->identifier((int)$audienceTerm->term_id);
        $audience->name($audienceTerm->name);

        $this->event->audience($audience);
        return $this;
    }

    public function setTypicalAgeRange(): EventBuilder
    {
        $audience = $this->event->getProperty('audience');
        $range    = null;

        if (!$audience || !$audience->getProperty('identifier')) {
            return $this;
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

        return $this;
    }

    public function setStartDate(): EventBuilder
    {
        $occasions = $this->acf->getField('occasions', $this->post->ID) ?: [];

        if (empty($occasions) || count($occasions) !== 1) {
            return $this;
        }

        $dateTime = null;
        $repeat   = $occasions[0]['repeat'] ?: null;
        $date     = $occasions[0]['startDate'] ?: null;
        $time     = $occasions[0]['startTime'] ?: null;

        if ($repeat !== 'no') {
            return $this;
        }

        // Combine date and time
        if ($date && $time) {
            $date     = new \DateTime("{$date} {$time}");
            $dateTime = $date->format('Y-m-d H:i');
        }

        $this->event->startDate($dateTime);

        return $this;
    }

    public function setEndDate(): EventBuilder
    {
        $occasions = $this->acf->getField('occasions', $this->post->ID) ?: [];
        $dateTime  = null;

        if (empty($occasions) || count($occasions) !== 1) {
            return $this;
        }

        $repeat = $occasions[0]['repeat'] ?: null;
        $date   = $occasions[0]['endDate'] ?: null;
        $time   = $occasions[0]['endTime'] ?: null;

        if ($repeat !== 'no') {
            return $this;
        }

        // Combine date and time
        if ($date && $time) {
            $date     = new \DateTime("{$date} {$time}");
            $dateTime = $date->format('Y-m-d H:i');
        }

        $this->event->endDate($dateTime);
        return $this;
    }

    public function setDuration(): EventBuilder
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
        return $this;
    }

    public function setKeywords(): EventBuilder
    {
        $keywordTerms = $this->wp->getPostTerms($this->post->ID, 'keyword', []);

        if (is_array($keywordTerms) && !empty($keywordTerms)) {
            $terms = array_map(fn ($term) => $term->name, $keywordTerms);
            $this->event->keywords($terms);
        }

        return $this;
    }

    public function setSchedule(): EventBuilder
    {
        $schedules         = [];
        $numberOfOccasions = $this->wp->getPostMeta($this->post->ID, 'occasions', true) ?: [];

        if (!is_numeric($numberOfOccasions) || (int)$numberOfOccasions < 1) {
            return $this;
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
        return $this;
    }

    public function setSuperEvent(): EventBuilder
    {
        $superEventPost = $this->wp->getPostParent($this->post->ID);

        if (!$superEventPost) {
            return $this;
        }

        $superEvent = new self($this->wp, $superEventPost, false);

        $this->event->superEvent($superEvent->toArray());

        return $this;
    }

    public function setSubEvents(): EventBuilder
    {
        $subEventPosts = $this->wp->getPosts([
            'post_parent' => $this->post->ID,
            'post_type'   => 'event',
            'numberposts' => -1
        ]);

        if (empty($subEventPosts)) {
            return $this;
        }

        $subEvents = array_map(function ($subPost) {
            $subEvent = new self($this->wp, $subPost, false);

            return $subEvent->toArray();
        }, $subEventPosts);

        $this->event->subEvents($subEvents);

        return $this;
    }
}
