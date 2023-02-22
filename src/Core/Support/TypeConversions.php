<?php

namespace Stillat\Meerkat\Core\Support;

use Stillat\Meerkat\Core\Exceptions\ParserException;
use Stillat\Meerkat\Core\Parsing\ArrayParser;

/**
 * Class TypeConversions
 *
 * Contains helpers for converting between run-time types
 *
 * @since 2.0.0
 */
class TypeConversions
{
    /**
     * Converts the provided value to a truthy value.
     *
     * Truthy values are returned without conversion.
     * NULLs are treated as false.
     * Strings are considered true if "true"
     * All other values are false
     *
     * @param  mixed  $value The value to convert.
     * @return bool
     */
    public static function getBooleanValue($value)
    {
        if ($value === null) {
            return false;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value) && trim(mb_strtolower(($value))) == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Converts the provided value to an array.
     *
     * @param  string  $value The input string.
     * @param  string  $delimiter The string that separates values.
     * @return array
     *
     * @throws ParserException
     */
    public static function parseToArray($value, $delimiter = ',')
    {
        if (is_string($value)) {
            $value = ArrayParser::getValues($value, $delimiter);
        }

        return self::getArray($value);
    }

    /**
     * Attempts to convert the value to an array.
     *
     * @param  mixed  $value The value to convert.
     * @return array
     */
    public static function getArray($value)
    {
        if (is_array($value)) {
            return $value;
        }

        return (array) $value;
    }
}
