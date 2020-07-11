<?php

namespace Stillat\Meerkat\Core\Helpers;

/**
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
     * @param  mixed $value The value to convert.
     * @return boolean
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

}
