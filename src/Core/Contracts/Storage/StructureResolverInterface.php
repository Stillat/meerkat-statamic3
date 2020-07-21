<?php

namespace Stillat\Meerkat\Core\Contracts\Storage;

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
     */
    public function resolve($threadPath, $commentPaths);

    /**
     * Gets the depth of the provided comment identifier.
     *
     * @param string $commentId The comment's identifier.
     * @return integer
     */
    public function getDepth($commentId);

    /**
     * Indicates if the comment has a direct ancestor.
     *
     * @param string $commentId The comment's identifier.
     * @return bool
     */
    public function hasAncestor($commentId);

    /**
     * Gets the comment's ancestor identifiers.
     *
     * @param string $commentId The comment's identifier.
     * @return string[]
     */
    public function getAllAncestors($commentId);

    /**
     * Gets the comment's parent identifier, if any.
     *
     * @param string $commentId The comment's identifier.
     * @return string|null
     */
    public function getParent($commentId);

    /**
     * Gets the comment's descendent identifiers, if any.
     *
     * This method will all comment identifiers from:
     *    Comment Depth + 1 to MaxSubThreadDepth
     *
     * @param string $commentId The comment's identifier.
     * @return string[]
     */
    public function getAllDescendents($commentId);

    /**
     * Gets the comment's direct descendent identifiers, if any.
     *
     * This method will return all comment identifiers from:
     *    Comment Depth + 1
     *
     * @param string $commentId The comment's identifier.
     * @return string[]
     */
    public function getDirectDescendents($commentId);

}