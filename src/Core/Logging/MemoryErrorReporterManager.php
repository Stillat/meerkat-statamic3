<?php

namespace Stillat\Meerkat\Core\Logging;

use Stillat\Meerkat\Core\Contracts\Logging\ErrorReporterContract;
use Stillat\Meerkat\Core\Contracts\Logging\ErrorReporterManagerContract;

/**
 * Class MemoryErrorReporterManager
 *
 * Manages reported errors in-memory.
 *
 * @since 2.3.0
 */
class MemoryErrorReporterManager implements ErrorReporterManagerContract
{
    /**
     * A list of all registered ErrorReporterContract instances.
     *
     * @var ErrorReporterContract[]
     */
    protected $reporters = [];

    /**
     * Registers a new reporter instance with the manager.
     *
     * @param  ErrorReporterContract  $reporter The reporter instance.
     */
    public function registerReporter(ErrorReporterContract $reporter)
    {
        $this->reporters[] = $reporter;
    }

    /**
     * Instructs the manager to report the error to all registered reporters.
     *
     * @param  mixed  $errorObject The error object.
     */
    public function report($errorObject)
    {
        // If the application is not in a debug environment, simply return.
        if (GlobalLogState::$isApplicationInDebugMode === false) {
            return;
        }

        foreach ($this->reporters as $reporter) {
            $reporter->log($errorObject);
        }
    }
}
