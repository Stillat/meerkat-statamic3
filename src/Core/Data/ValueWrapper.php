<?php

namespace Stillat\Meerkat\Core\Data;

use Stillat\Meerkat\Core\Support\Str;

/**
 * Class ValueWrapper
 *
 * Provides utilities for converting values to their appropriate run-time types from filter strings.
 *
 * @package Stillat\Meerkat\Core\Data
 * @since 2.0.0
 */
class ValueWrapper
{

    /**
     * Wraps the provided value in a DataQuery package based on it's type.
     *
     * @param mixed $value The input value.
     * @return string
     */
    public static function wrap($value)
    {
        if ($value === true) {
            return 'dq:{true}';
        } elseif ($value === false) {
            return 'dq:{false}';
        } elseif ($value === null) {
            return 'dq:{null}';
        } elseif (is_string($value)) {
            $value = str_replace(',', 'dq{SEP}', $value);

            return 'dq:string{' . $value . '}';
        } elseif (is_array($value) || is_object($value)) {
            return 'dq:object{'.serialize($value).'}';
        }

        return $value;
    }

    /**
     * Converts the wrapped value back to it's runtime type.
     *
     * @param string $value The wrapped type.
     * @return bool|string|string[]|null
     */
    public static function unwrap($value)
    {
        if ($value === 'true') {
            return true;
        }

        if ($value === 'false') {
            return false;
        }

        if (is_array($value)) {
            return $value;
        }

        if ($value === 'null') {
            return null;
        }

        if (Str::startsWith($value, 'dq:object{') && Str::endsWith($value, '}')) {
            $temp = mb_substr($value, 10);
            $temp = mb_substr($temp, 0, -1);

            return unserialize($temp);
        } elseif (Str::startsWith($value, 'dq:string{') && Str::endsWith($value, '}')) {
            $temp = mb_substr($value, 10);
            $temp = mb_substr($temp, 0, -1);

            $temp = str_replace('dq{SEP}', ',', $temp);

            return $temp;
        } elseif (Str::startsWith($value, 'dq:{') && Str::endsWith($value, '}')) {
            $temp = mb_substr($value, 4);
            $temp = mb_substr($temp, 0, -1);

            if ($temp === 'false') {
                return false;
            } elseif ($temp === 'true') {
                return true;
            } elseif ($temp === 'null') {
                return null;
            }
        }

        return $value;
    }

}
