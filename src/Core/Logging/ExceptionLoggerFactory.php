<?php

namespace Stillat\Meerkat\Core\Logging;

use Exception;
use Stillat\Meerkat\Core\Contracts\Logging\ExceptionLoggerContract;

/**
 * Class ExceptionLoggerFactory
 *
 * Provides Meerkat Core internals access to a shared ExceptionLoggerContract implementation instance.
 *
 * @package Stillat\Meerkat\Core\Logging
 * @since 2.0.0
 */
class ExceptionLoggerFactory
{

    /**
     * The ExceptionLoggerContract implementation instance.
     *
     * @var ExceptionLoggerContract|null
     */
    public static $instance = null;

    /**
     * Logs an exception if a logger is present, otherwise ignores it.
     *
     * @param Exception $e The exception.
     */
    public static function log(Exception $e)
    {
        if (ExceptionLoggerFactory::hasInstance()) {
            ExceptionLoggerFactory::$instance->log($e);
        }
    }

    /**
     * Tests if an exception logger was registered.
     *
     * @return bool
     */
    public static function hasInstance()
    {
        if (ExceptionLoggerFactory::$instance === null) {
            return false;
        }

        if (ExceptionLoggerFactory::$instance instanceof ExceptionLoggerFactory) {
            return true;
        }

        return false;
    }

}
