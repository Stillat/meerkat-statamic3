<?php

namespace Stillat\Meerkat\Core\Contracts\Threads;

use Stillat\Meerkat\Core\Contracts\Threads\ThreadContextContract;

/**
 * Interface ContextResolverContract
 *
 * Defines an interface for locating posts/items in the host system
 *
 * Responsible for locating and returning the context (data object)
 * that a comment thread is associated with. Implementors should
 * implement a caching mechanism if the location routines are
 * expensive each time the `findById` method is invoked.
 *
 * @package Stillat\Meerkat\Core\Contracts\Threads
 * @since 2.0.0
 */
interface ContextResolverContract
{

    /**
     * Attempts to locate a thread context by it's string identifier.
     *
     * @param  string $contextId
     *
     * @return ThreadContextContract
     */
    public function findById($contextId);

}
