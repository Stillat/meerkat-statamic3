<?php

namespace Stillat\Meerkat\Core\Data;

/**
 * Class ValueTransformer
 *
 * Provides utilities for transforming object values.
 *
 * Used by the export processes.
 *
 * @package Stillat\Meerkat\Core\Data
 * @since 2.0.0
 */
class ValueTransformer
{

    /**
     * Transforms the provided value.
     *
     * @param mixed $value The value to transform.
     * @return int|string
     */
    public static function transform($value)
    {
        if ($value === true) {
            return '1';
        }

        if ($value === false) {
            return 0;
        }

        return $value;
    }

    /**
     * Transforms the provided value.
     *
     * @param mixed $value The value to transform.
     * @param string $trueText The text to use for true values.
     * @param string $falseText The text to use for false values.
     * @return int|string
     */
    public static function transformText($value, $trueText, $falseText)
    {
        if ($value === true) {
            return $trueText;
        }

        if ($value === false) {
            return $falseText;
        }

        return $value;
    }

}
