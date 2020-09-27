<?php

namespace Stillat\Meerkat\Core\Data;

/**
 * Class ValueTransformer
 *
 * Provides utilities for transforming object values.
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

}