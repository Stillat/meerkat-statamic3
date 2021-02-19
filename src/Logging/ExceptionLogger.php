<?php

namespace Stillat\Meerkat\Logging;

use Exception;
use Illuminate\Support\Facades\Log;
use Stillat\Meerkat\Core\Contracts\Logging\ExceptionLoggerContract;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Logging\ErrorReporterFactory;
use Stillat\Meerkat\Core\Logging\LocalErrorCodeRepository;

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
        ErrorReporterFactory::report($exception);
        LocalErrorCodeRepository::logCodeMessage(Errors::HANDLER_GENERAL_EXCEPTION, $exception->getMessage());

        Log::error($exception->getMessage(), [
            self::KEY_EXCEPTION => $exception
        ]);
    }

}
