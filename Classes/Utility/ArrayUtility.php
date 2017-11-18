<?php

namespace Tollwerk\TwBase\Utility;

/**
 * Array utility
 */
class ArrayUtility
{
    /**
     * Recursively convert empty values in an array to FALSE
     *
     * @param array $array Array
     * @return array Modified array
     */
    public static function recursivelyFalsify(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::recursivelyFalsify($value);
            } else {
                $array[$key] = empty($value) ? false : $value;
            }
        }
        return $array;
    }
}
