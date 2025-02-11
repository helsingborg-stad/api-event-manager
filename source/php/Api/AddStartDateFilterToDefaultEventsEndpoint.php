<?php

namespace HbgEventImporter\Api;

class AddStartDateFilterToDefaultEventsEndpoint
{
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

        $response['meta_query'][] = array(
            'key' => "_start_date",
            'compare_key' => 'LIKE',
            'value' => $startDate->format('Y-m-d'),
            'compare' => '>=',
            'type' => 'DATE'
        );

        return $response;
    }
}