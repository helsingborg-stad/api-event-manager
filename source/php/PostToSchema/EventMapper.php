<?php

namespace EventManager\PostToSchema;

use EventManager\Services\AcfService\AcfService;
use EventManager\Services\WPService\WPService;

class EventMapper
{
    public function __construct(private WPService $wp, private AcfService $acf)
    {
    }

    public function mapToEvent(\WP_Post $post): \Spatie\SchemaOrg\Event
    {
        $event = new \Spatie\SchemaOrg\Event();

        $event->name($post->post_title);
        $event->description($post->post_content);

        $this->setIsAccessibleForFree($event, $post);
        $this->setLocation($event, $post);
        $this->setOrganizer($event, $post);
        $this->setAudience($event, $post);
        $this->setTypicalAgeRange($event, $post);
        $this->setDates($event, $post);
        $this->setDuration($event);
        $this->setKeywords($event, $post);
        $this->setSchedule($event, $post);
        $this->setSuperEvent($event, $post);
        $this->setSubEvents($event, $post);

        return $event;
    }

    private function setIsAccessibleForFree(\Spatie\SchemaOrg\Event $event, \WP_Post $post): void
    {
        $isAccessibleForFree = (bool) $this->wp->getPostMeta($post->ID, 'isAccessibleForFree', true);
        $event->isAccessibleForFree($isAccessibleForFree);
    }

    private function setLocation(\Spatie\SchemaOrg\Event $event, \WP_Post $post): void
    {
        $location = $this->wp->getPostMeta($post->ID, 'location', true) ?: null;

        if (!$location) {
            return;
        }

        $place = new \Spatie\SchemaOrg\Place();
        $place->address($location['address'] ?? null);
        $place->latitude($location['latitude'] ?? null);
        $place->longitude($location['longitude'] ?? null);

        $event->location($place);
    }

    private function setOrganizer(\Spatie\SchemaOrg\Event $event, \WP_Post $post): void
    {
        $organizationTerms = $this->wp->getPostTerms($post->ID, 'organization', []);

        if (empty($organizationTerms) || $this->wp->isWPError($organizationTerms)) {
            return;
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

        $event->organizer($organization);
    }

    private function setAudience(\Spatie\SchemaOrg\Event $event, \WP_Post $post): void
    {
        $audienceId = $this->wp->getPostMeta($post->ID, 'audience', true) ?: null;

        if (!$audienceId) {
            return;
        }

        $audienceTerm = $this->wp->getTerm($audienceId, 'audience');

        if (!is_a($audienceTerm, \WP_Term::class)) {
            return;
        }

        $audience = new \Spatie\SchemaOrg\Audience();
        $audience->identifier((int) $audienceTerm->term_id);
        $audience->name($audienceTerm->name);

        $event->audience($audience);
    }

    private function setTypicalAgeRange(\Spatie\SchemaOrg\Event $event, \WP_Post $post): void
    {
        $audience = $event->getProperty('audience');
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

        $event->typicalAgeRange($range);
    }

    private function setDates(\Spatie\SchemaOrg\Event $event, \WP_Post $post): void
    {
        $occasions = $this->acf->getField('occasions', $post->ID) ?: [];

        if (empty($occasions) || count($occasions) !== 1) {
            return;
        }

        $repeat    = $occasions[0]['repeat'] ?: null;
        $date      = $occasions[0]['date'] ?: null;
        $startTime = $occasions[0]['startTime'] ?: null;
        $endTime   = $occasions[0]['endTime'] ?: null;

        if ($repeat !== 'no') {
            return;
        }

        if ($this->endTimeIsEarlierThanStartTime($startTime, $endTime)) {
            $endTime = null;
        }

        $event->startDate($this->formatDateFromDateAndTime($date, $startTime));
        $event->endDate($this->formatDateFromDateAndTime($date, $endTime));
    }

    private function endTimeIsEarlierThanStartTime(?string $startTime, ?string $endTime): bool
    {
        if (!$startTime || !$endTime) {
            return false;
        }

        $startTimeUnix = strtotime($startTime);
        $endTimeUnix   = strtotime($endTime);

        return $endTimeUnix < $startTimeUnix;
    }

    private function formatDateFromDateAndTime(?string $date, ?string $time): ?string
    {
        if (!$date || !$time) {
            return null;
        }

        $dateTime = new \DateTime("{$date} {$time}");
        return $dateTime->format('Y-m-d H:i');
    }

    private function setDuration(\Spatie\SchemaOrg\Event $event): void
    {
        $startDate = $event->getProperty('startDate');
        $endDate   = $event->getProperty('endDate');
        $duration  = null;

        if ($startDate && $endDate) {
            $startDate = new \DateTime($startDate);
            $endDate   = new \DateTime($endDate);

            $duration = $startDate->diff($endDate)->format('P%yY%mM%dDT%hH%iM%sS');
        }

        $event->duration($duration);
    }

    private function setKeywords(\Spatie\SchemaOrg\Event $event, \WP_Post $post): void
    {
        $keywordTerms = $this->wp->getPostTerms($post->ID, 'keyword', []);

        if (is_array($keywordTerms) && !empty($keywordTerms)) {
            $terms = array_map(fn ($term) => $term->name, $keywordTerms);
            $event->keywords($terms);
        }
    }

    private function setSchedule(\Spatie\SchemaOrg\Event $event, \WP_Post $post): void
    {
        $schedules         = [];
        $numberOfOccasions = $this->wp->getPostMeta($post->ID, 'occasions', true) ?: [];

        if (!is_numeric($numberOfOccasions) || (int) $numberOfOccasions < 1) {
            return;
        }

        $getMetaRow = fn ($i, $key) => $this->wp->getPostMeta($post->ID, "occasions_{$i}_{$key}", true) ?: null;

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
                default:
                    return null;
            }
        }, range(0, $numberOfOccasions - 1));

        $schedules = array_filter($schedules); // Remove null values

        $event->eventSchedule($schedules ?: null);
    }

    private function setSuperEvent(\Spatie\SchemaOrg\Event $event, \WP_Post $post): void
    {
        $superEventPost = $this->wp->getPostParent($post->ID);

        if (!$superEventPost) {
            return;
        }

        $superEvent = $this->mapToEvent($superEventPost);

        $event->superEvent($superEvent);
    }

    private function setSubEvents(\Spatie\SchemaOrg\Event $event, \WP_Post $post): void
    {
        $subEventPosts = $this->wp->getPosts([
            'post_parent' => $post->ID,
            'post_type'   => 'event',
            'numberposts' => -1
        ]);

        if (empty($subEventPosts)) {
            return;
        }

        $subEvents = array_map(function ($subPost) {
            return $this->mapToEvent($subPost);
        }, $subEventPosts);

        $event->subEvents($subEvents);
    }
}
