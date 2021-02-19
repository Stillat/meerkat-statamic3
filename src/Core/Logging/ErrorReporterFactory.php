<?php

namespace Stillat\Meerkat\Core\Logging;

use Stillat\Meerkat\Core\Contracts\Logging\ErrorReporterManagerContract;

/**
 * Class ErrorReporterFactory
 *
 * Provides a central location for Meerkat internals to access a global ErrorReporterManagerContract instance.
 *
 * @package Stillat\Meerkat\Core\Logging
 * @since 2.3.0
 */
class ErrorReporterFactory
{

    /**
     * The global ErrorReporterManagerContract instance, if any.
     *
     * @var ErrorReporterManagerContract|null
     */
    public static $instance = null;

    /**
     * Reports the error to the global error report manager, if one exists.
     *
     * @param mixed $errorObject The error object.
     */
    public static function report($errorObject)
    {
        if (self::$instance !== null && self::$instance instanceof  ErrorReporterManagerContract) {
            self::$instance->report($errorObject);
        }
    }

}
