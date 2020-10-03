<?php

namespace Stillat\Meerkat\Core\Support;

/**
 * Class Str
 *
 * Common string related helper functions and utilities.
 *
 * @package Stillat\Meerkat\Core\Support
 * @since 2.0.0
 */
class Str
{

    /**
     * Tests if the input value matches the provided pattern.
     *
     * @param string $pattern The search pattern.
     * @param string $value The value to test.
     * @return bool
     */
    public static function isLike($pattern, $value)
    {
        $pattern = str_replace('%', '.*', preg_quote($pattern, '/'));

        return (bool)preg_match("/^{$pattern}$/i", $value);
    }

    /**
     * Tests if the input string is null or all whitespace.
     *
     * @param string $val The value to test.
     * @return bool
     */
    public static function isNullOrEmpty($val)
    {
        if ($val === null) {
            return true;
        }

        if (mb_strlen(trim($val)) === 0) {
            return true;
        }

        return false;
    }


    /**
     * Returns a version of the input value without spaces.
     *
     * @param string $value The input value.
     * @return string
     */
    public static function withoutSpaces($value)
    {
        return preg_replace("/\s+/", '', $value);
    }

    /**
     * Determine if a given string contains a given substring.
     *
     * Origin: Laravel Framework 6.x
     * https://github.com/laravel/framework/blob/6.x/src/Illuminate/Support/Str.php#L139-L154
     *
     * @param string $haystack
     * @param string|array $needles
     * @return bool
     */
    public static function contains($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * Origin: Laravel Framework 6.x
     * https://github.com/laravel/framework/blob/6.x/src/Illuminate/Support/Str.php#L567-L583
     *
     * @param string $haystack
     * @param string|string[] $needles
     * @return bool
     */
    public static function startsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle !== '' && substr($haystack, 0, strlen($needle)) === (string)$needle) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param string $haystack
     * @param string|string[] $needles
     * @return bool
     */
    public static function endsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if (substr($haystack, -strlen($needle)) === (string)$needle) {
                return true;
            }
        }

        return false;
    }

}
