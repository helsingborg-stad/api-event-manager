<?php

namespace EventManager\Services\AcfService\Functions;

interface GetFields
{
    /**
     * This function will return an array containing all the custom field values for a specific post_id.
     * The function is not very elegant and wastes a lot of PHP memory / SQL queries if you are not using all the values.
     *
     * @param mixed   $postId      The post_id of which the value is saved against.
     * @param boolean $formatValue Whether or not to format the field value.
     * @param boolean $escapeHtml  Should the field return a HTML safe formatted value if $format_value is true.
     *
     * @return array associative array where field name => field value
     */
    public function getFields(mixed $postId = false, bool $formatValue = true, bool $escapeHtml = false): array;
}
