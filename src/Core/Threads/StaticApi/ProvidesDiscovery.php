<?php

namespace Stillat\Meerkat\Core\Threads\StaticApi;

use Stillat\Meerkat\Core\Contracts\Threads\ThreadContract;
use Stillat\Meerkat\Core\Exceptions\ThreadNotFoundException;
use Stillat\Meerkat\Core\Threads\ThreadManagerFactory;

/**
 * Trait ProvidesDiscovery
 *
 * Provides a static thread-discovery API.
 *
 * @package Stillat\Meerkat\Core\Threads\StaticApi
 * @since 2.0.0
 */
trait ProvidesDiscovery
{

    /**
     * Attempts to locate the specified thread.
     *
     * @param string $threadId The thread's string identifier.
     * @return ThreadContract|null
     * @throws ThreadNotFoundException
     */
    public static function findOrFail($threadId)
    {
        $thread = self::find($threadId);

        if ($thread === null) {
            throw new ThreadNotFoundException("Thread {$threadId} was not found.");
        }

        return $thread;
    }

    /**
     * Attempts to locate the specified thread.
     *
     * @param string $threadId The thread's string identifier.
     * @return ThreadContract|null
     */
    public static function find($threadId)
    {
        if (ThreadManagerFactory::hasInstance()) {
            return ThreadManagerFactory::$instance->findById($threadId);
        }

        return null;
    }

}
