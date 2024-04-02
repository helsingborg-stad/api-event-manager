<?php

namespace EventManager\Services\WPService;

use WP_Screen;

interface GetCurrentScreen
{
    public function getCurrentScreen(): WP_Screen|null;
}
