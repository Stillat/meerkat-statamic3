<?php

namespace Stillat\Meerkat\Core\Contracts\Logging;

use Stillat\Meerkat\Core\Logging\ErrorLog;

/**
 * Interface ErrorCodeRepositoryContract
 *
 * Provides an API for managing Meerkat Core error code logs.
 *
 * @package Stillat\Meerkat\Core\Contracts\Logging
 * @since 2.0.0
 */
interface ErrorCodeRepositoryContract
{

    /**
     * Logs an error code.
     *
     * @param ErrorLog $log The error information to log.
     *
     * @return bool
     */
    public function logError(ErrorLog $log);

    /**
     * Removes all error code logs.
     *
     * @return bool
     */
    public function removeLogs();

    /**
     * Removes an error log instance.
     *
     * @param string $instanceId The instance to remove.
     * @return bool
     */
    public function removeInstance($instanceId);

    /**
     * Returns a collection of error logs.
     *
     * @return ErrorLog[]
     */
    public function getLogs();

}
