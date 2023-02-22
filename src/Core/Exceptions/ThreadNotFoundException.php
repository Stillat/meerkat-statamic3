<?php

namespace Stillat\Meerkat\Core\Exceptions;

/**
 * Class ThreadNotFoundException
 *
 * Thrown when a thread could not be located.
 *
 * @since 2.0.0
 */
class ThreadNotFoundException extends MeerkatCoreException
{
    /**
     * The thread identifier that could not be located.
     *
     * @var string|null
     */
    public $threadId = null;
}
