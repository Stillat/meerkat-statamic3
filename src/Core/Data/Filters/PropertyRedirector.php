<?php

namespace Stillat\Meerkat\Core\Data\Filters;

/**
 * Class PropertyRedirector
 *
 * Provides utilities for redirecting internal properties, if required.
 *
 * @package Stillat\Meerkat\Core\Data\Filters
 * @since 2.0.4
 */
class PropertyRedirector
{

    /**
     * Redirects the requested provider to a different internal property, if required.
     *
     * @param string $property The requested property.
     * @return string
     */
    public static function redirect($property)
    {
        if ($property === 'content' || $property === 'comment') {
            return 'content_raw';
        }

        return $property;
    }

}