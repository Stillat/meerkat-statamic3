<?php

namespace Stillat\Meerkat\Core\Exceptions;

/**
 * Class ConcurrentResourceAccessViolationException
 * @package Stillat\Meerkat\Core\Exceptions
 * @since 2.0.0
 */
class ConcurrentResourceAccessViolationException extends MeerkatCoreException
{

    const ERR_MESSAGE = 'Attempted to persist storage while storage access is locked: ';

    /**
     * Creates a new exception with the provided lock string.
     *
     * @param string $lockString The locks currently held.
     * @return ConcurrentResourceAccessViolationException
     */
    public static function make($lockString)
    {
        return new ConcurrentResourceAccessViolationException(self::ERR_MESSAGE . $lockString);
    }

}
