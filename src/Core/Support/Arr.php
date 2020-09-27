<?php

namespace Stillat\Meerkat\Core\Support;

/**
 * Class Arr
 *
 * Provides simple array helpers.
 *
 * @package Stillat\Meerkat\Core\Support
 * @since 2.0.0
 */
class Arr
{

    /**
     * Attempts to retrieve a value from array using "dot" notation.
     *
     * @param string $key The key.
     * @param array $data The data to traverse.
     * @param null $default The default value.
     * @see https://selvinortiz.com/blog/traversing-arrays-using-dot-notation
     * @return array|mixed|null
     */
    public static function getValue($key, array $data, $default = null)
    {
        if (!is_string($key) || empty($key) || !count($data)) {
            return $default;
        }

        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);

            foreach ($keys as $innerKey) {
                if (!array_key_exists($innerKey, $data)) {
                    return $default;
                }

                $data = $data[$innerKey];
            }

            return $data;
        }

        return array_key_exists($key, $data) ? $data[$key] : $default;
    }

}