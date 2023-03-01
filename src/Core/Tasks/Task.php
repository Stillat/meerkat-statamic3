<?php

namespace Stillat\Meerkat\Core\Tasks;

use Stillat\Meerkat\Core\Contracts\Tasks\TaskContract;
use Stillat\Meerkat\Core\Support\Arr;
use Stillat\Meerkat\Core\Tasks\StaticApi\ProvidesCreation;
use Stillat\Meerkat\Core\UuidGenerator;

/**
 * Class Task
 *
 * Represents an individual Core task.
 *
 * @since 2.0.0
 */
class Task implements TaskContract
{
    use ProvidesCreation;

    const KEY_ID = 'id';

    const KEY_CODE = 'code';

    const KEY_STATUS = 'status';

    const KEY_CREATED_ON_DATETIME = 'created_on';

    const KEY_NAME = 'name';

    const KEY_ARGS = 'args';

    /**
     * The tasks's code.
     *
     * @var string
     */
    protected $taskCode = '';

    /**
     * The tasks's status.
     *
     * @var int
     */
    protected $status = 0;

    /**
     * The created on timestamp UTC.
     *
     * @var int
     */
    protected $createdOnUtc = 0;

    /**
     * The task's name.
     *
     * @var string
     */
    protected $taskName = '';

    /**
     * The task's identifier.
     *
     * @var string
     */
    protected $taskId = '';

    /**
     * The task arguments.
     *
     * @var string[]
     */
    protected $args = [];

    public function __construct()
    {
        $this->createdOnUtc = time();
        $this->taskId = UuidGenerator::getInstance()->newId();
    }

    /**
     * Attempts to convert the array to a Task instance.
     *
     * @param  array  $array The task data.
     * @return Task
     */
    public static function fromArray($array)
    {
        $task = new Task();

        if (Arr::matches([
            self::KEY_NAME, self::KEY_CREATED_ON_DATETIME, self::KEY_STATUS,
            self::KEY_CODE, self::KEY_ARGS, self::KEY_ID,
        ], $array)) {
            $task->setInstanceId($array[self::KEY_ID]);
            $task->setTaskCode($array[self::KEY_CODE]);
            $task->setStatus($array[self::KEY_STATUS]);
            $task->setCreateDateTimeUtc($array[self::KEY_CREATED_ON_DATETIME]);
            $task->setTaskName($array[self::KEY_NAME]);
            $task->setArguments($array[self::KEY_ARGS]);
        }

        return $task;
    }

    /**
     * Sets the task's instance identifier.
     *
     * @param  string  $instanceId The identifier.
     */
    public function setInstanceId($instanceId)
    {
        $this->taskId = $instanceId;
    }

    /**
     * Sets the task's created timestamp.
     *
     * @param  int  $dateTimeUtc The timestamp.
     */
    public function setCreateDateTimeUtc($dateTimeUtc)
    {
        $this->createdOnUtc = $dateTimeUtc;
    }

    /**
     * Sets the task arguments.
     *
     * @param  string[]  $args The task arguments.
     */
    public function setArguments($args)
    {
        $this->args = $args;
    }

    /**
     * Gets the task arguments.
     *
     * @return string[]
     */
    public function getArguments()
    {
        return $this->args;
    }

    /**
     * String representation of object
     *
     * @link https://php.net/manual/en/serializable.serialize.php
     *
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return json_encode($this->toArray());
    }

    /**
     * Converts the task to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::KEY_ID => $this->taskId,
            self::KEY_CODE => $this->taskCode,
            self::KEY_STATUS => $this->status,
            self::KEY_CREATED_ON_DATETIME => $this->createdOnUtc,
            self::KEY_NAME => $this->taskName,
            self::KEY_ARGS => $this->args,
        ];
    }

    /**
     * Constructs the object
     *
     * @link https://php.net/manual/en/serializable.unserialize.php
     *
     * @param  string  $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        $arrayFormat = (array) json_decode($serialized);

        $this->taskId = $arrayFormat[self::KEY_ID];
        $this->taskCode = $arrayFormat[self::KEY_CODE];
        $this->status = $arrayFormat[self::KEY_STATUS];
        $this->createdOnUtc = $arrayFormat[self::KEY_CREATED_ON_DATETIME];
        $this->taskName = $arrayFormat[self::KEY_NAME];
        $this->args = $arrayFormat[self::KEY_ARGS];
    }

    /**
     * Returns the task's instance unique identifier.
     *
     * @return string
     */
    public function getInstanceId()
    {
        return $this->taskId;
    }

    /**
     * Returns the task's name.
     *
     * @return string
     */
    public function getTaskName()
    {
        return $this->taskName;
    }

    /**
     * Sets the task name
     *
     * @param  string  $taskName The task name.
     */
    public function setTaskName($taskName)
    {
        $this->taskName = $taskName;
    }

    /**
     * Returns the task's system code.
     *
     * @return string
     */
    public function getTaskCode()
    {
        return $this->taskCode;
    }

    /**
     * Sets the tasks's internal code.
     *
     * @param  string  $taskCode The code.
     */
    public function setTaskCode($taskCode)
    {
        $this->taskCode = $taskCode;
    }

    /**
     * Gets the tasks's current status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the task's current status.
     *
     * @param  int  $taskStatus The current status.
     */
    public function setStatus($taskStatus)
    {
        $this->status = $taskStatus;
    }

    /**
     * Gets the task's created timestamp.
     *
     * @return int
     */
    public function getCreateDateTimeUtc()
    {
        return $this->createdOnUtc;
    }
}
