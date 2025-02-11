<?php

namespace HbgEventImporter\Api;

class AddStartDateFilterToDefaultEventsEndpoint
{
    const MAX_NUMBER_OF_OCCASIONS = 10;

    public function addHooks() {
        add_filter('rest_event_query', array($this, 'addStartDateFilter'), 10, 2);
    }

    public function addStartDateFilter($response)
    {
        if(!isset($_GET['start_date']) || empty($_GET['start_date'])) {
            return $response;   
        }

        $startDate = sanitize_text_field($_GET['start_date']);

        try {
            $startDate = new \DateTime($startDate);
        } catch (\Exception $e) {
            return $response;
        }

        if (empty($startDate)) {
            return $response;
        }

        $metaQuery = ['relation' => 'OR'];

        for($i = 0; $i < self::MAX_NUMBER_OF_OCCASIONS; $i++) {
            $metaQuery[] = array(
                'key' => "occasions_".$i."_start_date",
                'value' => $startDate->format('Y-m-d'),
                'compare' => '>=',
                'type' => 'DATE'
            );
        }

        $response['meta_query'][] = $metaQuery;

        return $response;
    }
}