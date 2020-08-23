<?php

namespace Stillat\Meerkat\Core\Data\Validators;

/**
 * Class StringValidator
 *
 * Provides utilities for testing string values.
 *
 * @package Stillat\Meerkat\Core\Data\Validators
 * @since 2.0.0
 */
class StringValidator
{

    /**
     * Tests whether a given value is a string.
     *
     * @param mixed $inputValue The value to check.
     * @return bool
     */
    public static function check($inputValue)
    {
       return is_string($inputValue);
    }

}
