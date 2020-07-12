<?php

namespace Stillat\Meerkat\Core\Paths;

/**
 * Class PathUtilities
 *
 * Provides helper utilities for working with system paths.
 *
 * @package Stillat\Meerkat\Core\Paths
 * @since 2.0.0
 */
class PathUtilities
{

    /**
     * Replaces all back-slashes with forward-slashes in the provided path.
     *
     * @param string $path The path to normalize.
     * @return string|string[]
     */
    public static function normalize($path)
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * Converts a path to the Windows back-slash format.
     *
     * @param string $path The path to convert to the Windows format.
     * @return string|string[]
     */
    public static function winPath($path)
    {
        return str_replace('/', '\\', PathUtilities::normalize($path));
    }

}
