<?php

namespace Stillat\Meerkat\Core\Data\Validators;

/**
 * Class BitValidator
 *
 * Provides utilities to validate true/false values.
 *
 * @since 2.0.0
 */
class BitValidator
{
    /**
     * Tests whether a given input value can be converted to a true/false value.
     *
     * @param  mixed  $inputValue The value to check.
     * @return bool
     */
    public static function check($inputValue)
    {
        return is_bool($inputValue);
    }
}
