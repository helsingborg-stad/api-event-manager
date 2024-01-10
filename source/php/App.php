<?php

namespace EventManager;

use EventManager\PostTypeRegistrar\PostTypeRegistrar;

class App
{
    protected PostTypeRegistrar $postTypeRegistrar;

    public function __construct(PostTypeRegistrar $postTypeRegistrar)
    {
        $this->postTypeRegistrar = $postTypeRegistrar;
    }

    public function registerPostTypes()
    {
        $this->postTypeRegistrar->register('event', [
            'label'        => __('Events', 'api-event-manager'),
            'show_in_rest' => true,
            'public'       => true,
            'hierarchical' => true,
            'icon'         => 'dashicons-calendar-alt',
        ]);
    }
}
