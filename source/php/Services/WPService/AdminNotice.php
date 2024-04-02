<?php

namespace EventManager\Services\WPService;

interface AdminNotice
{
    /**
     * Outputs an admin notice.
     *
     * @param string $message The message to output.
     * @param array  $args {
     *     Optional. An array of arguments for the admin notice. Default empty array.
     *
     *     @type string   $type               Optional. The type of admin notice.
     *                                        For example, 'error', 'success', 'warning', 'info'.
     *                                        Default empty string.
     *     @type bool     $dismissible        Optional. Whether the admin notice is dismissible. Default false.
     *     @type string   $id                 Optional. The value of the admin notice's ID attribute. Default empty string.
     *     @type string[] $additional_classes Optional. A string array of class names. Default empty array.
     *     @type bool     $paragraph_wrap     Optional. Whether to wrap the message in paragraph tags. Default true.
     * }
     */
    public function adminNotice(string $message, array $args): void;
}
