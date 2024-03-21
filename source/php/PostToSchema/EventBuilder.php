<?php

namespace EventManager\PostToSchema;

use EventManager\PostToSchema\Schedule\ScheduleByDayFactory;
use EventManager\PostToSchema\Schedule\ScheduleByMonthFactory;
use EventManager\PostToSchema\Schedule\ScheduleByWeekFactory;
use EventManager\Services\AcfService\Functions\GetField;
use EventManager\Services\AcfService\Functions\GetFields;
use EventManager\Services\WPService\GetPostParent;
use EventManager\Services\WPService\GetPosts;
use EventManager\Services\WPService\GetPostTerms;
use EventManager\Services\WPService\GetTerm;
use EventManager\Services\WPService\GetThePostThumbnailUrl;
use Spatie\SchemaOrg\BaseType;
use WP_Error;
use WP_Post;

class EventBuilder implements BaseTypeBuilder
{
    protected BaseType $event;
    protected array $fields;

    public function __construct(
        protected WP_Post $post,
        protected GetThePostThumbnailUrl&GetPostTerms&GetTerm&GetPosts&GetPostParent $wpService,
        protected GetField&GetFields $acf
    ) {
        $this->fields = $this->acf->getFields($this->post->ID) ?: [];
        $this->event  = new \Spatie\SchemaOrg\Event();
    }

    public function build(): BaseType
    {
        $this
            ->setIdentifier()
            ->setName()
            ->setDescription()
            ->setAbout()
            ->setAccessabilityInformation()
            ->setImage()
            ->setIsAccessibleForFree()
            ->setLocation()
            ->setUrl()
            ->setAudience()
            ->setTypicalAgeRange()
            ->setOrganizer()
            ->setDates()
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
        $this->event->description($this->fields['description'] ?: null);
        return $this;
    }

    public function setAbout(): EventBuilder
    {
        $this->event->about($this->fields['about'] ?: null);
        return $this;
    }

    public function setAccessabilityInformation(): EventBuilder
    {

        $accessabilityInformation = $this->fields['accessabilityInformation'] ?: null;
        $about                    = $this->event->getProperty('about');

        if ($accessabilityInformation && $about) {
            $this->event->about("{$about}\n\n{$accessabilityInformation}");
        }

        return $this;
    }

    public function setImage(): EventBuilder
    {
        $this->event->image($this->wpService->getThePostThumbnailUrl($this->post->ID) ?: null);
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
        $this->event->isAccessibleForFree((bool)$this->fields['isAccessibleForFree'] ?? null);
        return $this;
    }

    public function setLocation(): EventBuilder
    {
        $location = $this->fields['location'] ?? null;

        if (!$location || !is_array($location)) {
            return $this;
        }

        $place = $this->mapOpenStreetMapDataToPlace($location);
        $this->event->location($place);

        return $this;
    }

    private function mapOpenStreetMapDataToPlace(array $openStreetMapData): \Spatie\SchemaOrg\Place
    {
        $place = new \Spatie\SchemaOrg\Place();
        $place->address($openStreetMapData['address'] ?? null);
        $place->latitude($openStreetMapData['lat'] ?? null);
        $place->longitude($openStreetMapData['lng'] ?? null);

        return $place;
    }

    public function setOrganizer(): EventBuilder
    {
        $organizationTerms = $this->wpService->getPostTerms($this->post->ID, 'organization', []);

        if (empty($organizationTerms) || $organizationTerms instanceof WP_Error) {
            return $this;
        }

        $organizationTerm = $organizationTerms[0];
        $termFields       = $this->acf->getFields($organizationTerm->taxonomy . '_' . $organizationTerm->term_id) ?: [];

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
                $place = $this->mapOpenStreetMapDataToPlace($termFields['address']);
                $organization->location($place);
            }
        }

        $this->event->organizer($organization);
        return $this;
    }

    public function setAudience(): EventBuilder
    {
        $audienceId = $this->fields['audience'] ?: null;

        if (!$audienceId) {
            return $this;
        }

        // Get audience term
        $audienceTerm = $this->wpService->getTerm($audienceId, 'audience');

        if (!is_a($audienceTerm, \WP_Term::class)) {
            return $this;
        }


        $audience = new \Spatie\SchemaOrg\Audience();
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
        $termFields = $this->acf->getFields("audience_{$termId}") ?: [];
        $rangeStart = $termFields['typicalAgeRangeStart'] ?: null;
        $rangeEnd   = $termFields['typicalAgeRangeEnd'] ?: null;

        if ($rangeStart && $rangeEnd) {
            $range = "{$rangeStart}-{$rangeEnd}";
        } elseif ($rangeStart) {
            $range = "{$rangeStart}-";
        }

        $this->event->typicalAgeRange($range);

        return $this;
    }

    public function setDates(): EventBuilder
    {
        $occasions = $this->acf->getField('occasions', $this->post->ID) ?: [];

        if (empty($occasions) || count($occasions) !== 1) {
            return $this;
        }

        $repeat    = $occasions[0]['repeat'] ?: null;
        $date      = $occasions[0]['date'] ?: null;
        $startTime = $occasions[0]['startTime'] ?: null;
        $endTime   = $occasions[0]['endTime'] ?: null;

        if ($repeat !== 'no') {
            return $this;
        }

        if ($this->endTimeIsEarlierThanStartTime($startTime, $endTime)) {
            $endTime = null;
        }

        $this->event->startDate($this->formatDateFromDateAndTime($date, $startTime));
        $this->event->endDate($this->formatDateFromDateAndTime($date, $endTime));

        return $this;
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
        $keywordTerms = $this->wpService->getPostTerms($this->post->ID, 'keyword', []);

        if (is_array($keywordTerms) && !empty($keywordTerms)) {
            $terms = array_map(fn ($term) => $term->name, $keywordTerms);
            $this->event->keywords($terms);
        }

        return $this;
    }

    public function setSchedule(): EventBuilder
    {
        $schedules         = [];
        $numberOfOccasions = $this->fields['occasions'] ?: null;

        if (!is_numeric($numberOfOccasions) || (int)$numberOfOccasions < 1) {
            return $this;
        }

        $getMetaRow = fn ($i, $key) => $this->fields["occasions_{$i}_{$key}"][0];

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
        $superEventPost = $this->wpService->getPostParent($this->post->ID);

        if (!$superEventPost) {
            return $this;
        }

        $superEvent = new self($superEventPost, $this->wpService, $this->acf);

        $this->event->superEvent($superEvent->toArray());

        return $this;
    }

    public function setSubEvents(): EventBuilder
    {
        $subEventPosts = $this->wpService->getPosts([
            'post_parent' => $this->post->ID,
            'post_type'   => 'event',
            'numberposts' => -1
        ]);

        if (empty($subEventPosts)) {
            return $this;
        }

        $subEvents = array_map(function ($subPost) {
            $subEvent = new self($subPost, $this->wpService, $this->acf);

            return $subEvent->toArray();
        }, $subEventPosts);

        $this->event->subEvents($subEvents);

        return $this;
    }
}
