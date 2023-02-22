<?php

namespace Stillat\Meerkat\Core\Tasks;

use Stillat\Meerkat\Core\Contracts\Storage\TaskStorageManagerContract;

/**
 * Class TaskManagerFactory
 *
 * Allows Meerkat Core internals to reference a global implementation of TaskStorageManagerContract.
 *
 * @since 2.0.0
 */
class TaskManagerFactory
{
    /**
     * A reference to the TaskStorageManagerContract instance.
     *
     * @var TaskStorageManagerContract
     */
    public static $instance = null;

    /**
     * Returns a value that indicates if a comment change set manager instance was set.
     *
     * @return bool
     */
    public static function hasInstance()
    {
        if (TaskManagerFactory::$instance === null) {
            return false;
        }

        if ((TaskManagerFactory::$instance instanceof TaskStorageManagerContract) == false) {
            return false;
        }

        return true;
    }
}
