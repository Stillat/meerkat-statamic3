<?php

namespace Stillat\Meerkat\Core\Contracts\Storage;

use Stillat\Meerkat\Core\Contracts\Tasks\TaskContract;

/**
 * Interface TaskStorageManagerContract
 *
 * Provides a consistent API for task and storage interactions.
 *
 * @package Stillat\Meerkat\Core\Contracts\Storage
 * @since 2.0.0
 */
interface TaskStorageManagerContract
{

    /**
     * Checks if the identified task was canceled.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function isTaskCanceledById($taskId);

    /**
     * Checks if the task was canceled.
     *
     * @param TaskContract $task The task.
     * @return bool
     */
    public function isTaskCanceled(TaskContract $task);

    /**
     * Checks if the identified task is complete.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function isTaskCompleteById($taskId);

    /**
     * Checks if the identified task is complete.
     *
     * @param TaskContract $task The task.
     * @return bool
     */
    public function isTaskComplete(TaskContract $task);

    /**
     * Attempts to mark the task as complete.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function markCompleteById($taskId);

    /**
     * Attempts to mark the task as complete.
     *
     * @param TaskContract $task The task.
     * @return bool
     */
    public function markComplete(TaskContract $task);

    /**
     * Attempts to mark the task as canceled.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function markCanceledById($taskId);

    /**
     * Attempts to mark the task as canceled.
     *
     * @param TaskContract $task The task.
     * @return bool
     */
    public function markCanceled(TaskContract $task);

    /**
     * Attempts to get the current task execution time, in seconds.
     *
     * @param string $taskId The task identifier.
     * @return int
     */
    public function getCurrentRunTimeById($taskId);

    /**
     * Attempts to get the current task execution time, in seconds.
     *
     * @param TaskContract $task The task.
     * @return int
     */
    public function getCurrentRunTime(TaskContract $task);

    /**
     * Attempts to remove the provided task.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function removeTaskById($taskId);

    /**
     * Attempts to remove the provided task.
     *
     * @param TaskContract $task The task.
     * @return bool
     */
    public function removeTask(TaskContract $task);

    /**
     * Saves the provided task to storage.
     *
     * @param TaskContract $task The task to save.
     * @return bool
     */
    public function saveTask(TaskContract $task);

    /**
     * Tests if a task with the provided identifier exists.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function existsById($taskId);

    /**
     * Attempts to locate the task instance with the provided identifier.
     *
     * @param string $taskId The task's identifier.
     * @return TaskContract|null
     */
    public function findById($taskId);

}
