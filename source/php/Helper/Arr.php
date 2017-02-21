<?php

namespace HbgEventImporter\Helper;

class Arr
{
    public static function arrayKeysExist(array $array, $keys)
    {
        $count = 0;

        if (!is_array($keys)) {
            $keys = func_get_args();
            array_shift($keys);
        }

        foreach ($keys as $key) {
            if (array_key_exists($key, $array)) {
                $count ++;
            }
        }

        return count($keys) === $count;
    }
}
