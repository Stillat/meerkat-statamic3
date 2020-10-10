<?php

namespace Stillat\Meerkat\Core\Parsing;

use Stillat\Meerkat\Core\Contracts\Parsing\DateParserContract;

/**
 * Class DateParserFactory
 *
 * Allows Meerkat Core internals to access a shared datetime parser instance.
 *
 * @package Stillat\Meerkat\Core\Parsing
 * @since 2.0.4
 */
class DateParserFactory
{

    /**
     * A shared DateParserContract implementation instance.
     *
     * @var null|DateParserContract
     */
    public static $instance = null;

    /**
     * Indicates if a shared DateParserContract implementation exists.
     *
     * @return bool
     */
    public static function hasInstance()
    {
        if (DateParserFactory::$instance != null) {
            return true;
        }

        return false;
    }

    /**
     * Attempts to retrieve a UNIX timestamp from the input string.
     *
     * @param string $input The input string.
     * @return int
     */
    public static function parse($input)
    {
        if (self::hasInstance()) {
            return self::$instance->getTimestamp($input);
        }

        return $input;
    }

}