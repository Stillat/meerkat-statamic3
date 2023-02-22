<?php

namespace Stillat\Meerkat\Core\Logging\Reporters;

use Exception;
use Stillat\Meerkat\Core\Contracts\Logging\ErrorReporterContract;
use Throwable;

/**
 * Class ExceptionReporter
 *
 * Rethrows exception objects observed by the reporter.
 *
 * @since 2.3.0
 */
class ExceptionReporter implements ErrorReporterContract
{
    /**
     * Rethrows the error object, if it is an Exception.
     *
     * @param  mixed  $errorObject The error object.
     *
     * @throws Exception|Throwable
     */
    public function log($errorObject)
    {
        if ($errorObject instanceof Exception) {
            throw $errorObject;
        }

        if ($errorObject instanceof  Throwable) {
            throw $errorObject;
        }
    }
}
