<?php

namespace Stillat\Meerkat\Core\Contracts\Logging;

/**
 * Interface ErrorReporterContract
 *
 * Represents a mechanism that can communicate errors with developers.
 *
 * @since 2.3.0
 */
interface ErrorReporterContract
{
    /**
     * Performs some action on the provided error object.
     *
     * @param  mixed  $errorObject The error object.
     */
    public function log($errorObject);
}
