<?php

namespace Stillat\Meerkat\Core;

use Stillat\Meerkat\Core\ResourceLock;

/**
 * Class RuntimeStateGuard
 *
 * Provides utilities to manage resource locks on the comment mutation pipeline.
 *
 * @package Stillat\Meerkat\Core\Comments
 * @since 2.0.0
 */
class RuntimeStateGuard
{

    /**
     * The resource locks for comment mutations.
     *
     * @var ResourceLock
     */
    protected static $commentMutationLocks = null;

    /**
     * The resource locks for storage access.
     *
     * @var ResourceLock
     */
    protected static $storagePersistenceLocks = null;

    /**
     * The resource locks for thread mutations.
     *
     * @var ResourceLock
     */
    protected static $threadMutationLocks = null;

    /**
     * Returns access to the shared storage lock manager.
     *
     * @return ResourceLock
     */
    public static function storageLocks()
    {
        if (self::$storagePersistenceLocks === null) {
            self::$storagePersistenceLocks = new ResourceLock();
        }

        return self::$storagePersistenceLocks;
    }

    /**
     * Returns access to the shared mutation lock manager.
     *
     * @return ResourceLock
     */
    public static function mutationLocks()
    {
        if (self::$commentMutationLocks === null) {
            self::$commentMutationLocks = new ResourceLock();
        }

        return self::$commentMutationLocks;
    }

    /**
     * Returns access to the shared thread mutation lock manager.
     *
     * @return ResourceLock
     */
    public static function threadLocks()
    {
        if (self::$threadMutationLocks === null) {
            self::$threadMutationLocks = new ResourceLock();
        }

        return self::$threadMutationLocks;
    }

}
