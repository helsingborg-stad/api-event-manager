<?php

namespace EventManager\Services\WPService;

interface GetOption
{
    /**
     * Retrieves an option value from the WordPress database using the get_option function.
     *
     * @param string $option The name of the option to retrieve.
     * @param mixed $defaultValue The default value to return if the option does not exist.
     * @return mixed The value of the option if it exists, or the default value if it does not.
     */
    public function getOption(string $option, mixed $defaultValue = false): mixed;
}
