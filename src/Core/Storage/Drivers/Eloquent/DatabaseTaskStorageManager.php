<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Eloquent;

use Carbon\Carbon;
use Statamic\Facades\Data;
use Stillat\Meerkat\Core\Contracts\Storage\TaskStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Tasks\TaskContract;
use Stillat\Meerkat\Core\Storage\Drivers\Eloquent\Models\DatabaseTask;
use Stillat\Meerkat\Core\Tasks\Task;

class DatabaseTaskStorageManager implements TaskStorageManagerContract
{


    /**
     * Attempts to locate a task record with the provided identifier.
     *
     * @param string $taskId The task's system identifier.
     * @return DatabaseTask|null
     */
    private function getDatabaseTask($taskId)
    {
        return DatabaseTask::where('system_id', $taskId)->first();
    }

    /**
     * Checks if the identified task was canceled.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function isTaskCanceledById($taskId)
    {
        $task = $this->getDatabaseTask($taskId);

        if ($task === null) {
            return false;
        }

        return $task->was_canceled;
    }

    /**
     * Checks if the task was canceled.
     *
     * @param TaskContract $task The task.
     * @return bool
     */
    public function isTaskCanceled(TaskContract $task)
    {
        return $this->isTaskCanceledById($task->getInstanceId());
    }

    /**
     * Checks if the identified task is complete.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function isTaskCompleteById($taskId)
    {
        $task = $this->getDatabaseTask($taskId);

        if ($task === null) {
            return false;
        }

        return $task->is_complete;
    }

    /**
     * Checks if the identified task is complete.
     *
     * @param TaskContract $task The task.
     * @return bool
     */
    public function isTaskComplete(TaskContract $task)
    {
        return $this->isTaskCompleteById($task->getInstanceId());
    }

    /**
     * Attempts to mark the task as complete.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function markCompleteById($taskId)
    {
        $task = $this->getDatabaseTask($taskId);

        if ($task === null) {
            return false;
        }

        $task->is_complete = true;
        $task->completed_on = Carbon::now();

        return $task->save();
    }

    /**
     * Attempts to mark the task as complete.
     *
     * @param TaskContract $task The task.
     * @return bool
     */
    public function markComplete(TaskContract $task)
    {
        return $this->markCompleteById($task->getInstanceId());
    }

    /**
     * Attempts to mark the task as canceled.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function markCanceledById($taskId)
    {
        $task = $this->getDatabaseTask($taskId);

        if ($task === null) {
            return false;
        }

        $task->was_canceled = true;

        return $task->save();
    }

    /**
     * Attempts to mark the task as canceled.
     *
     * @param TaskContract $task The task.
     * @return bool
     */
    public function markCanceled(TaskContract $task)
    {
        return $this->markCanceledById($task->getInstanceId());
    }

    /**
     * Attempts to get the current task execution time, in seconds.
     *
     * @param string $taskId The task identifier.
     * @return int
     */
    public function getCurrentRunTimeById($taskId)
    {
        $databaseTask = $this->getDatabaseTask($taskId);

        if ($databaseTask === null) {
            return 0;
        }

        $currentTime = time();

        $taskTimestamp = $databaseTask->created_at->timestamp;

        if ($databaseTask->was_canceled === true) {
            $taskTimestamp = $databaseTask->updated_at->timestamp;
        } else {
            if ($databaseTask->completed_on !== null) {
                $taskTimestamp = $databaseTask->completed_on->timestamp;
            }
        }

        return $currentTime - $taskTimestamp;
    }

    /**
     * Attempts to get the current task execution time, in seconds.
     *
     * @param TaskContract $task The task.
     * @return int
     */
    public function getCurrentRunTime(TaskContract $task)
    {
        return $this->getCurrentRunTimeById($task->getInstanceId());
    }

    /**
     * Attempts to remove the provided task.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function removeTaskById($taskId)
    {
        $task = $this->getDatabaseTask($taskId);

        $result = $task->forceDelete();

        if ($result === true) {
            return true;
        }

        return false;
    }

    /**
     * Attempts to remove the provided task.
     *
     * @param TaskContract $task The task.
     * @return bool
     */
    public function removeTask(TaskContract $task)
    {
        return $this->removeTaskById($task->getInstanceId());
    }

    /**
     * Saves the provided task to storage.
     *
     * @param TaskContract $task The task to save.
     * @return bool
     */
    public function saveTask(TaskContract $task)
    {
        $databaseTask = $this->getDatabaseTask($task->getInstanceId());

        if ($databaseTask === null) {
            $newTask = new DatabaseTask();

            $newTask->system_id = $task->getInstanceId();
            $newTask->task_name = $task->getTaskName();
            $newTask->task_status = $task->getStatus();
            $newTask->task_code = $task->getTaskCode();
            $newTask->task_args = json_encode($task->getArguments());

            return $newTask->save();
        }

        $databaseTask->system_id = $task->getInstanceId();
        $databaseTask->task_name = $task->getTaskName();
        $databaseTask->task_status = $task->getStatus();
        $databaseTask->task_code = $task->getTaskCode();
        $databaseTask->task_args = json_encode($task->getArguments());

        return $databaseTask->save();
    }

    /**
     * Tests if a task with the provided identifier exists.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function existsById($taskId)
    {
        $task = $this->getDatabaseTask($taskId);

        if ($task === null) {
            return false;
        }

        return true;
    }

    /**
     * Attempts to locate the task instance with the provided identifier.
     *
     * @param string $taskId The task's identifier.
     * @return TaskContract|null
     */
    public function findById($taskId)
    {
        $databaseTask = $this->getDatabaseTask($taskId);

        if ($databaseTask === null) {
            return null;
        }

        $task = new Task();

        $task->setInstanceId($databaseTask->system_id);
        $task->setArguments(json_decode($databaseTask->task_args));
        $task->setCreateDateTimeUtc($databaseTask->created_at->timestamp);
        $task->setStatus($databaseTask->task_status);
        $task->setTaskCode($databaseTask->task_code);
        $task->setTaskName($databaseTask->task_name);

        return $task;
    }

}
