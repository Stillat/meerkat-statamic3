<?php

namespace Stillat\Meerkat\Core\Contracts\Storage;

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
     *
     * @param string $commentId The comment's identifier.
     * @return array
     */
    public function getAllAncestors($commentId);

    public function getParent($commentId);

    public function getAllDescendents($commentId);

    public function getDirectDescendents($commentId);
}