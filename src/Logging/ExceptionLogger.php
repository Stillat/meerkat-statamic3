<?php

namespace Stillat\Meerkat\Logging;

use Exception;
use Illuminate\Support\Facades\Log;
use Stillat\Meerkat\Core\Contracts\Logging\ExceptionLoggerContract;

/**
 * Class ExceptionLogger
 *
 * Provides a bridge between Meerkat Core's exception logger and Laravel logging system.
 *
 * @package Stillat\Meerkat\Logging
 * @since 2.0.0
 */
class ExceptionLogger implements ExceptionLoggerContract
{
    const KEY_EXCEPTION = 'exception';

    /**
     * Logs an exception.
     *
     * @param Exception $exception The exception.
     */
    public function log(Exception $exception)
    {
        Log::error($exception->getMessage(), [
            self::KEY_EXCEPTION => $exception
        ]);
    }

}
