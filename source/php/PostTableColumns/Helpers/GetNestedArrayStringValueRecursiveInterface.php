<?php

namespace EventManager\PostTableColumns\Helpers;

interface GetNestedArrayStringValueRecursiveInterface
{
    public function getNestedArrayStringValueRecursive(array $keys, array $array): string;
}
