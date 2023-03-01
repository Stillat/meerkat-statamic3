<?php

namespace Stillat\Meerkat\Core\Data\Validators;

/**
 * Class IntegerValidator
 *
 * Provides utilities for validating integer/number values.
 *
 * @since 2.0.0
 */
class IntegerValidator
{
    /**
     * Tests whether a value is an integer.
     *
     * @param  mixed  $inputValue The value to check.
     * @return bool
     */
    public static function check($inputValue)
    {
        return is_int($inputValue);
    }
}
