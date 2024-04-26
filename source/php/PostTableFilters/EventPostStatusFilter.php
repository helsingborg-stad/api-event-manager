<?php

namespace EventManager\PostTableFilters;

use WpService\Contracts\AddFilter;
use WpService\Contracts\GetPostStatuses;

class EventPostStatusFilter implements IPostTableFilter
{
    public function __construct(private GetPostStatuses&AddFilter $wpService)
    {
    }

    public function hideViewsFromEventTable()
    {
        $this->wpService->addFilter('views_edit-event', function () {
            return null;
        });
    }

    public function outputFilterMarkup(string $postType): void
    {
        if ($postType !== "event") {
            return;
        }

        $selectedValue = $_GET['post_status'] ?? 'any';
        $statuses      = $this->wpService->getPostStatuses();
        unset($statuses['private']);

        echo '<select name="post_status" id="post_status">';
        echo '<option value="any" ' . ($selectedValue === 'any' ? 'selected' : '') . '>Any status</option>';

        foreach ($statuses as $status => $label) {
            echo '<option value="' . $status . '" ' . ($selectedValue === $status ? 'selected' : '') . '>' . $label . '</option>';
        }

        echo '</select>';
    }
}
