<?php

namespace Stillat\Meerkat\Core\Threads;

use Stillat\Meerkat\Core\Contracts\Threads\ThreadManagerContract;

/**
 * Class ThreadManagerFactory
 *
 * Allows Meerkat Core internals to reference a global implementation
 *
 * @package Stillat\Meerkat\Core\Threads
 * @since 2.0.0
 */
class ThreadManagerFactory
{

    /**
     * A reference to the ThreadManager instance.
     *
     * @var ThreadManagerContract
     */
    public static $instance = null;

    /**
     * Returns a value that indicates if a ThreadManager instance was set.
     *
     * @return boolean
     */
    public static function hasInstance()
    {
        if (ThreadManagerFactory::$instance === null) {
            return false;
        }

        if ((ThreadManagerFactory::$instance instanceof ThreadManagerContract) == false) {
            return false;
        }

        return true;
    }

}
