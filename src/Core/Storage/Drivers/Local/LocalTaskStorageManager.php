<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Local;

use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Parsing\YAMLParserContract;
use Stillat\Meerkat\Core\Contracts\Storage\TaskStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Tasks\TaskContract;
use Stillat\Meerkat\Core\Storage\Paths;
use Stillat\Meerkat\Core\Tasks\Task;

/**
 * Class LocalTaskStorageManager
 *
 * Manages the interactions between the Tasks sub-system and the user's local filesystem.
 *
 * @package Stillat\Meerkat\Core\Storage\Drivers\Local
 * @since 2.0.0
 */
class LocalTaskStorageManager implements TaskStorageManagerContract
{
    const EXT_TASK = '.task';
    const EXT_DONE = '.done';
    const EXT_CANCELED = '.cancel';

    /**
     * The Meerkat Core configuration.
     *
     * @var Configuration
     */
    protected $config = null;

    /**
     * The Paths instance.
     *
     * @var Paths
     */
    protected $paths = null;

    /**
     * The YAMLParserContract implementation instance.
     *
     * @var YAMLParserContract
     */
    protected $yamlParser = null;

    /**
     * The local task storage path.
     *
     * @var string
     */
    protected $taskStoragePath = '';

    public function __construct(Configuration $config, YAMLParserContract $yamlParser)
    {
        $this->config = $config;
        $this->paths = new Paths($this->config);
        $this->yamlParser = $yamlParser;
        $this->taskStoragePath = $this->config->taskDirectory;

        $this->prepare();
    }

    /**
     * Prepares the task storage path.
     */
    private function prepare()
    {
        if (file_exists($this->taskStoragePath) === false) {
            mkdir($this->taskStoragePath, Paths::DIRECTORY_PERMISSIONS, true);
        }
    }

    /**
     * Saves the provided task to storage.
     *
     * @param TaskContract $task The task to save.
     * @return bool
     */
    public function saveTask(TaskContract $task)
    {
        $storagePath = $this->getTaskFilePath($task->getInstanceId());
        $this->prepareTaskPath($storagePath);

        $dataToSave = $this->yamlParser->toYaml($task->toArray(), null);

        $saveResults = file_put_contents($storagePath, $dataToSave);

        if ($saveResults === false) {
            return false;
        }

        return true;
    }

    /**
     * Constructs the storage path to the main task file.
     *
     * @param string $taskId The task's identifier.
     * @return string
     */
    private function getTaskFilePath($taskId)
    {
        return $this->paths->combine([
            $this->taskStoragePath,
            $taskId, self::EXT_TASK
        ]);
    }

    /**
     * Prepares the base directory for the tasks's file storage.
     *
     * @param string $taskPath The tasks' storage path.
     */
    private function prepareTaskPath($taskPath)
    {
        $dirName = dirname($taskPath);

        if (file_exists($dirName) === false) {
            mkdir($dirName, Paths::DIRECTORY_PERMISSIONS, true);
        }
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
     * Attempts to mark the task as complete.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function markCompleteById($taskId)
    {
        if ($this->existsById($taskId) === false) {
            return false;
        }

        $completeFlagPath = $this->getTaskCompletedPath($taskId);

        touch($completeFlagPath);

        return file_exists($completeFlagPath);
    }

    /**
     * Tests if a task with the provided identifier exists.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function existsById($taskId)
    {
        $taskPath = $this->getTaskFilePath($taskId);

        return file_exists($taskPath);
    }

    /**
     * Constructs the completed-flag path for the task identifier.
     *
     * @param string $taskId The task's identifier.
     * @return string
     */
    private function getTaskCompletedPath($taskId)
    {
        return $this->paths->combine([
            $this->taskStoragePath,
            $taskId, self::EXT_DONE
        ]);
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
     * Attempts to mark the task as canceled.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function markCanceledById($taskId)
    {
        if ($this->existsById($taskId) === false) {
            return false;
        }

        $cancelFlagPath = $this->getTaskCanceledPath($taskId);

        touch($cancelFlagPath);

        return file_exists($cancelFlagPath);
    }

    /**
     * Constructs the canceled-flag path for the task identifier.
     *
     * @param string $taskId The task's identifier.
     * @return string
     */
    private function getTaskCanceledPath($taskId)
    {
        return $this->paths->combine([
            $this->taskStoragePath,
            $taskId, self::EXT_CANCELED
        ]);
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
     * Checks if the identified task was canceled.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function isTaskCanceledById($taskId)
    {
        $completePath = $this->getTaskCanceledPath($taskId);

        return file_exists($completePath);
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
     * Attempts to get the current task execution time, in seconds.
     *
     * Note: This method estimates the current execution time.
     *
     * @param string $taskId The task identifier.
     * @return int
     */
    public function getCurrentRunTimeById($taskId)
    {
        if ($this->existsById($taskId) === false) {
            return 0;
        }

        $taskChangeTime = filectime($this->getTaskFilePath($taskId));

        if ($taskChangeTime === false) {
            return 0;
        }

        $currentTime = time();

        if ($this->isTaskCompleteById($taskId)) {
            $taskCompleteChangeTime = filectime($this->getTaskCompletedPath($taskId));

            if ($taskCompleteChangeTime !== false) {
                return $taskCompleteChangeTime - $taskChangeTime;
            }
        }

        if ($this->isTaskCanceledById($taskId)) {
            $taskCanceledChangeTime = filectime($this->getTaskCompletedPath($taskId));

            if ($taskCanceledChangeTime !== false) {
                return $taskCanceledChangeTime - $taskChangeTime;
            }
        }

        return $currentTime - $taskChangeTime;
    }

    /**
     * Checks if the identified task is complete.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function isTaskCompleteById($taskId)
    {
        $completePath = $this->getTaskCompletedPath($taskId);

        return file_exists($completePath);
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
     * Attempts to remove the provided task.
     *
     * @param string $taskId The task identifier.
     * @return bool
     */
    public function removeTaskById($taskId)
    {
        if ($this->existsById($taskId) === false) {
            return false;
        }

        $taskStorageDirectory = dirname($this->getTaskFilePath($taskId));

        Paths::recursivelyRemoveDirectory($taskStorageDirectory);

        return file_exists($taskStorageDirectory) === false;
    }

    /**
     * Attempts to locate the task instance with the provided identifier.
     *
     * @param string $taskId The task's identifier.
     * @return TaskContract|null
     */
    public function findById($taskId)
    {
        if ($this->existsById($taskId) === false) {
            return null;
        }

        $taskPath = $this->getTaskFilePath($taskId);
        $taskData = $this->yamlParser->parseDocument(file_get_contents($taskPath));

        if ($taskData === null || is_array($taskData) === false) {
            return null;
        }

        $task = Task::fromArray($taskData);

        if ($task !== null) {
            if ($this->isTaskCanceledById($taskId)) {
                $task->setStatus(TaskContract::STATUS_CANCELED);
            } elseif ($this->isTaskCompleteById($taskId)) {
                $task->setStatus(TaskContract::STATUS_DONE);
            } else {
                $task->setStatus(TaskContract::STATUS_IN_PROGRESS);
            }
        }

        return $task;
    }

}
