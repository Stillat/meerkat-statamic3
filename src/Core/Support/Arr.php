<?php

namespace Stillat\Meerkat\Core\Support;

/**
 * Class Arr
 *
 * Provides simple array helpers.
 *
 * @since 2.0.0
 */
class Arr
{
    /**
     * Tests if all provided fields exist in the target array.
     *
     * @param  string[]  $fields The fields to test.
     * @param  array  $array The array to check.
     * @return bool
     */
    public static function matches($fields, $array)
    {
        $nonMatchCount = 0;

        foreach ($fields as $field) {
            if (array_key_exists($field, $array) === false) {
                $nonMatchCount += 1;
            }
        }

        return $nonMatchCount === 0;
    }

    /**
     * Attempts to retrieve a value from array using "dot" notation.
     *
     * @param  string  $key The key.
     * @param  array  $data The data to traverse.
     * @param  null  $default The default value.
     *
     * @see https://selvinortiz.com/blog/traversing-arrays-using-dot-notation
     *
     * @return array|mixed|null
     */
    public static function getValue($key, array $data, $default = null)
    {
        if (! is_string($key) || empty($key) || ! count($data)) {
            return $default;
        }

        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);

            foreach ($keys as $innerKey) {
                if (! array_key_exists($innerKey, $data)) {
                    return $default;
                }

                $data = $data[$innerKey];
            }

            return $data;
        }

        return array_key_exists($key, $data) ? $data[$key] : $default;
    }
}
