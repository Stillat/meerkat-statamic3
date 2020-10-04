<?php

namespace Stillat\Meerkat\Core\Contracts\Tasks;

use Serializable;

/**
 * Interface TaskContract
 *
 * Represents a Meerkat background task.
 *
 * @package Stillat\Meerkat\Core\Contracts\Tasks
 * @since 2.0.0
 */
interface TaskContract extends Serializable
{
    const STATUS_IN_PROGRESS = 0;
    const STATUS_DONE  = 1;
    const STATUS_CANCELED = 2;

    /**
     * Returns the task's instance unique identifier.
     *
     * @return string
     */
    public function getInstanceId();

    /**
     * Sets the task's instance identifier.
     *
     * @param string $instanceId The identifier.
     */
    public function setInstanceId($instanceId);

    /**
     * Returns the task's name.
     *
     * @return string
     */
    public function getTaskName();

    /**
     * Sets the task name
     *
     * @param string $taskName The task name.
     */
    public function setTaskName($taskName);

    /**
     * Returns the task's system code.
     *
     * @return string
     */
    public function getTaskCode();

    /**
     * Sets the tasks's internal code.
     *
     * @param string $taskCode The code.
     */
    public function setTaskCode($taskCode);

    /**
     * Gets the tasks's current status.
     *
     * @return int
     */
    public function getStatus();

    /**
     * Sets the task's current status.
     *
     * @param int $taskStatus The current status.
     */
    public function setStatus($taskStatus);

    /**
     * Sets the task's created timestamp.
     *
     * @param int $dateTimeUtc The timestamp.
     */
    public function setCreateDateTimeUtc($dateTimeUtc);

    /**
     * Gets the task's created timestamp.
     *
     * @return int
     */
    public function getCreateDateTimeUtc();

    /**
     * Converts the task to an array.
     *
     * @return array
     */
    public function toArray();

    /**
     * Sets the task arguments.
     *
     * @param string[] $args The task arguments.
     */
    public function setArguments($args);

    /**
     * Gets the task arguments.
     *
     * @return string[]
     */
    public function getArguments();

}
