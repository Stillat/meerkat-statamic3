<?php

namespace Stillat\Meerkat\Core\Contracts\Logging;

use Exception;

/**
 * Interface ExceptionLoggerContract
 *
 * Provides an API to allow Meerkat Core to log exceptions in a host-system's log.
 *
 * @package Stillat\Meerkat\Core\Contracts\Logging
 * @since 2.0.0
 */
interface ExceptionLoggerContract
{

    /**
     * Logs an exception.
     *
     * @param Exception $exception The exception.
     */
    public function log(Exception $exception);

}
