<?php

namespace HbgEventImporter\Parser;

class Xcap extends \HbgEventImporter\Parser
{
    public function __construct($url)
    {
        parent::__construct($url);
    }

    public function start()
    {
        $xml = simplexml_load_file($this->url);
        $xml = json_decode(json_encode($xml));
        $events = $xml->iCal->vevent;

        $index = 0;


        //var_dump($events);
        //die();
        foreach ($events as $event) {
            var_dump($event);
            die();

            if (!isset($event->uid) || empty($event->uid)) {
                continue;
            }

            $address = isset($event->{'x-xcap-address'}) && !empty($event->{'x-xcap-address'}) ? $event->{'x-xcap-address'} : null;
            $alternateName = isset($event->uid) && !empty($event->uid) ? $event->uid : null;
            $categories = isset($event->categories) && !empty($event->categories) ? explode(',', $event->categories) : null;
            $description = isset($event->description) && !empty($event->description) ? $event->description : null;
            $doorTime = isset($event->dtstart) && !empty($event->dtstart) ? $event->dtstart : null;
            $duration = isset($event->dtstart) && isset($event->dtend) ? $this->getSecondsFromDates($event->dtstart, $event->dtend) : 0;
            $endDate = isset($event->dtend) && !empty($event->dtend) ? $event->dtend : null;
            $image = isset($event->{'x-xcap-imageid'}) && !empty($event->{'x-xcap-imageid'}) ? $event->{'x-xcap-imageid'} : null;
            $location = isset($event->location) && !empty($event->location) ? $event->location : null;
            $name = isset($event->summary) && !empty($event->summary) ? $event->summary : null;
            $startDate = isset($event->dtstart) && !empty($event->dtstart) ? $event->dtstart : null;
            $ticketUrl = isset($event->{'x-xcap-ticketlink'}) && !empty($event->{'x-xcap-ticketlink'}) ? $event->{'x-xcap-ticketlink'} : null;
            //$ticketUrl2 = iset($event->);
            $url = null;

            //var_dump($duration);
            die();
            //

            if ($name === null || is_object($name)) {
                continue;
            }

            // Check if the event passes the filter
            if (!$this->filter($categories)) {
                continue;
            }

            \HbgEventImporter\Event::add(array(
                'address'       => $address,
                'alternateName' => $alternateName,
                'categories'    => $categories,
                'description'   => $description,
                'doorTime'      => $this->formatDate($doorTime),
                'duration'      => $duration,
                'endDate'       => $this->formatDate($endDate),
                'image'         => $image,
                'location'      => $location,
                'name'          => $name,
                'startDate'     => $this->formatDate($startDate),
                'ticketUrl'     => $ticketUrl,
                'url'           => $url,
            ));
            ++$index;

            if($index == 1)
                die();
        }
    }

    /**
     * Filter, if add or not to add
     * @param  array $categories All categories
     * @return bool
     */
    public function filter($categories)
    {
        $passes = true;
        if (get_field('xcap_filter_categories', 'options')) {
            $filters = array_map('trim', explode(',', get_field('xcap_filter_categories', 'options')));
            $categoriesLower = array_map('strtolower', $categories);
            $passes = false;

            foreach ($filters as $filter) {
                if (in_array(strtolower($filter), $categoriesLower)) {
                    $passes = true;
                }
            }
        }

        return $passes;
    }

    /**
     * Get how long the event is in seconds
     * @param  string $startDate Date event starts
     * @param  string $endDate   Date event ends
     * @return string  Example return 3600; (1 hour)
     */
    public function getSecondsFromDates($startDate, $endDate)
    {
        $startParts = explode("T", $startDate);
        $endParts = explode("T", $endDate);
        $totalSeconds = (strtotime($endParts[0] . '-' . substr($endParts[1], 0, 4)) - strtotime($startParts[0] . '-' . substr($startParts[1], 0, 4)));
        return $totalSeconds;
    }

    public function formatDate($date)
    {
        // Format the date string corretly
        $dateParts = explode("T", $date);
        $dateString = substr($dateParts[0], 0, 4) . '-' . substr($dateParts[0], 4, 2) . '-' . substr($dateParts[0], 6, 2);
        $timeString = substr($dateParts[1], 0, 4);
        $timeString = substr($timeString, 0, 2) . ':' . substr($timeString, 2, 2);
        $dateString = $dateString . ' ' . $timeString;

        // Create UTC date object
        $date = new \DateTime(date('Y-m-d H:i', strtotime($dateString)));
        $timeZone = new \DateTimeZone('Europe/Stockholm');
        $date->setTimezone($timeZone);

        return $date->format('Y-m-d H:i:s');
    }
}
