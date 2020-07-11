<?php

namespace Stillat\Meerkat\Core\Helpers;

/**
 * Contains helper functions for working with PHP strings
 *
 * @since 2.0.0
 */
class Str
{

    /**
     * Determine if a given string contains a given substring.
     *
     * Origin: Laravel Framework 6.x
     * https://github.com/laravel/framework/blob/6.x/src/Illuminate/Support/Str.php#L139-L154
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    public static function contains($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
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
     * @param  string  $haystack
     * @param  string|string[]  $needles
     * @return bool
     */
    public static function startsWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && substr($haystack, 0, strlen($needle)) === (string) $needle) {
                return true;
            }
        }

        return false;
    }
    
}
