<?php

namespace Stillat\Meerkat\Core\Contracts\Tasks;

/**
 * Interface TaskContract
 *
 * Represents a Meerkat background task.
 *
 * @package Stillat\Meerkat\Core\Contracts\Tasks
 * @since 2.0.0
 */
interface TaskContract
{

    /**
     * Returns the task's instance unique identifier.
     *
     * @return string
     */
    public function getInstanceId();

    /**
     * Returns the task's name.
     *
     * @return string
     */
    public function getTaskName();

    /**
     * Returns the task's system code.
     *
     * @return string
     */
    public function getTaskCode();

}