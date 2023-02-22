<?php

namespace Stillat\Meerkat\Core;

use Stillat\Meerkat\Core\Exceptions\ConcurrentResourceAccessViolationException;

/**
 * Class ResourceLock
 *
 * Provides utilities for creating, and managing resource locks.
 *
 * @since 2.0.0
 */
class ResourceLock
{
    /**
     * A list of all active resource locks.
     *
     * @var array
     */
    private $resourceLocks = [];

    /**
     * Acquires a lock on the resource and returns the lock object.
     *
     * @return int
     */
    public function lock()
    {
        $lockObj = time();
        $this->resourceLocks[$lockObj] = $lockObj;

        return $lockObj;
    }

    /**
     * Releases the provided lock on the resource.
     *
     * @param  int  $lockObj The lock to release.
     */
    public function releaseLock($lockObj)
    {
        if (array_key_exists($lockObj, $this->resourceLocks)) {
            unset($this->resourceLocks[$lockObj]);
        }
    }

    /**
     * Determines if the resource is currently locked.
     *
     * @return bool
     */
    public function isLocked()
    {
        return count($this->resourceLocks) > 0;
    }

    /**
     * Returns a string containing all current lock object identifiers.
     *
     * @return string
     */
    public function getLockString()
    {
        return implode(',', $this->resourceLocks);
    }

    /**
     * Throws a new exception due to concurrent access violations.
     *
     * @throws ConcurrentResourceAccessViolationException
     */
    public function raiseAccessViolation()
    {
        throw ConcurrentResourceAccessViolationException::make($this->getLockString());
    }

    /**
     * Checks for concurrent access. If detected, throws an exception.
     *
     * @throws ConcurrentResourceAccessViolationException
     */
    public function checkConcurrentAccess()
    {
        if ($this->isLocked()) {
            $this->raiseAccessViolation();
        }
    }
}
