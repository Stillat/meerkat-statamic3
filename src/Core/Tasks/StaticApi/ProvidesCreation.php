<?php

namespace Stillat\Meerkat\Core\Tasks\StaticApi;

use Stillat\Meerkat\Core\Contracts\Tasks\TaskContract;
use Stillat\Meerkat\Core\Logging\Tracing\Tracer;
use Stillat\Meerkat\Core\Tasks\Task;

/**
 * Trait ProvidesCreation
 *
 * Provides helper utilities for constructing Task instances statically.
 *
 * @since 2.0.0
 */
trait ProvidesCreation
{
    /**
     * Creates a new task and returns it.
     *
     * @param  string  $taskCode The internal task code.
     * @return TaskContract
     */
    public static function taskFromMethod($taskCode)
    {
        return self::newTask(Tracer::getCallingMethod(), $taskCode);
    }

    /**
     * Creates a new task and returns it.
     *
     * @param  string  $taskName The task name.
     * @param  string  $taskCode The internal task code.
     * @return TaskContract
     */
    public static function newTask($taskName, $taskCode)
    {
        $newTask = new Task();

        $newTask->setTaskCode($taskCode);
        $newTask->setTaskName($taskName);

        return $newTask;
    }
}
