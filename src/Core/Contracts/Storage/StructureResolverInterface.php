<?php

namespace Stillat\Meerkat\Core\Contracts\Storage;

use Stillat\Meerkat\Core\Threads\ThreadHierarchy;

/**
 * Interface StructureResolverInterface
 *
 * Provides a consistent API for resolving a thread's hierarchy.
 *
 * @package Stillat\Meerkat\Core\Contracts\Storage
 * @since 2.0.0
 */
interface StructureResolverInterface
{

    /**
     * Resets the internal state of the resolver.
     *
     * @return void
     */
    public function reset();

    /**
     * Resolves the comment dependency graph.
     *
     * @param string $threadPath The thread's base path.
     * @param array $commentPaths A collection of comment absolute paths.
     * @return ThreadHierarchy
     */
    public function resolve($threadPath, $commentPaths);

}