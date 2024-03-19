<?php

namespace EventManager\PostTableColumns\Helpers;

class GetNestedArrayStringValueRecursive implements GetNestedArrayStringValueRecursiveInterface
{
    public function getNestedArrayStringValueRecursive($keys, $array): string
    {
        $key = array_shift($keys);

        if (empty($keys)) {
            return $array[$key] ?? '';
        }

        if (isset($array[$key])) {
            return $this->getNestedArrayStringValueRecursive($keys, $array[$key]);
        }

        return '';
    }
}
