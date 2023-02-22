<?php

namespace Stillat\Meerkat\Core\Contracts\Logging;

/**
 * Interface ErrorReporterManagerContract
 *
 * Represents a system that can manage multiple ErrorReporterContract instances.
 *
 * @since 2.3.0
 */
interface ErrorReporterManagerContract
{
    /**
     * Registers a new reporter instance with the manager.
     *
     * @param  ErrorReporterContract  $reporter The reporter instance.
     */
    public function registerReporter(ErrorReporterContract $reporter);

    /**
     * Instructs the manager to report the error to all registered reporters.
     *
     * @param  mixed  $errorObject The error object.
     */
    public function report($errorObject);
}
