<?php

namespace EventManager\Services\AcfService\Functions;

interface AddOptionsPage
{
    /**
     * Adds an options page using the Advanced Custom Fields (ACF) plugin.
     *
     * This function is a wrapper for the `acf_add_options_page` function provided by ACF.
     * It allows you to easily add an options page to the WordPress admin dashboard.
     *
     * @param array $options An array of options for the options page.
     *                       The options array should include the following keys:
     *                       - 'page_title' (string): The title of the options page.
     *                       - 'menu_title' (string): The title of the options page in the admin menu.
     *                       - 'menu_slug' (string): The slug of the options page.
     *                       - 'capability' (string): The capability required to access the options page.
     *                       - 'position' (int): The position of the options page in the admin menu.
     *                       - 'icon_url' (string): The URL of the icon to be displayed in the admin menu.
     *                       - 'redirect' (bool): Whether to redirect to the first child page (default: true).
     *
     * @return void
     */
    public function addOptionsPage(array $options): void;
}
