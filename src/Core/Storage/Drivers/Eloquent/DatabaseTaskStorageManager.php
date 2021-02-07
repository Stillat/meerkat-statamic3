<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Eloquent;

use Stillat\Meerkat\Core\Contracts\Storage\TaskStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Tasks\TaskContract;

class DatabaseTaskStorageManager implements TaskStorageManagerContract
{

    /**
     * Checks if the identified task was canceled.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function isTaskCanceledById($taskId)
    {
        // TODO: Implement isTaskCanceledById() method.
    }

    /**
     * Checks if the task was canceled.
     *
     * @param TaskContract $task The task.
     * @return bool
     */
    public function isTaskCanceled(TaskContract $task)
    {
        // TODO: Implement isTaskCanceled() method.
    }

    /**
     * Checks if the identified task is complete.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function isTaskCompleteById($taskId)
    {
        // TODO: Implement isTaskCompleteById() method.
    }

    /**
     * Checks if the identified task is complete.
     *
     * @param TaskContract $task The task.
     * @return bool
     */
    public function isTaskComplete(TaskContract $task)
    {
        // TODO: Implement isTaskComplete() method.
    }

    /**
     * Attempts to mark the task as complete.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function markCompleteById($taskId)
    {
        // TODO: Implement markCompleteById() method.
    }

    /**
     * Attempts to mark the task as complete.
     *
     * @param TaskContract $task The task.
     * @return bool
     */
    public function markComplete(TaskContract $task)
    {
        // TODO: Implement markComplete() method.
    }

    /**
     * Attempts to mark the task as canceled.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function markCanceledById($taskId)
    {
        // TODO: Implement markCanceledById() method.
    }

    /**
     * Attempts to mark the task as canceled.
     *
     * @param TaskContract $task The task.
     * @return bool
     */
    public function markCanceled(TaskContract $task)
    {
        // TODO: Implement markCanceled() method.
    }

    /**
     * Attempts to get the current task execution time, in seconds.
     *
     * @param string $taskId The task identifier.
     * @return int
     */
    public function getCurrentRunTimeById($taskId)
    {
        // TODO: Implement getCurrentRunTimeById() method.
    }

    /**
     * Attempts to get the current task execution time, in seconds.
     *
     * @param TaskContract $task The task.
     * @return int
     */
    public function getCurrentRunTime(TaskContract $task)
    {
        // TODO: Implement getCurrentRunTime() method.
    }

    /**
     * Attempts to remove the provided task.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function removeTaskById($taskId)
    {
        // TODO: Implement removeTaskById() method.
    }

    /**
     * Attempts to remove the provided task.
     *
     * @param TaskContract $task The task.
     * @return bool
     */
    public function removeTask(TaskContract $task)
    {
        // TODO: Implement removeTask() method.
    }

    /**
     * Saves the provided task to storage.
     *
     * @param TaskContract $task The task to save.
     * @return bool
     */
    public function saveTask(TaskContract $task)
    {
        // TODO: Implement saveTask() method.
    }

    /**
     * Tests if a task with the provided identifier exists.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function existsById($taskId)
    {
        // TODO: Implement existsById() method.
    }

    /**
     * Attempts to locate the task instance with the provided identifier.
     *
     * @param string $taskId The task's identifier.
     * @return TaskContract|null
     */
    public function findById($taskId)
    {
        // TODO: Implement findById() method.
    }
}