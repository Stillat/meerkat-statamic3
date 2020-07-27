<?php

namespace Stillat\Meerkat\Core\Threads;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

/**
 * Class ThreadHierarchy
 *
 * Represents the nested structure of a comment thread.
 *
 * @package Stillat\Meerkat\Core\Threads
 * @since 2.0.0
 */
class ThreadHierarchy
{

    /**
     * A list of all the paths processed by the resolver.
     *
     * @var array
     */
    protected $paths = [];

    /**
     * A mapping between comment identifiers and their storage paths.
     *
     * @var array
     */
    protected $commentIdPathMapping = [];

    /**
     * A mapping between depths and comment identifiers.
     *
     *    [depth][] = 'identifier'
     *
     * @var array
     */
    protected $depthMapping = [];

    /**
     * A mapping between identifiers and depths.
     *
     *     [identifier] = depth
     *
     * @var array
     */
    protected $commentDepthMapping = [];

    /**
     * A mapping between identifiers and direct ancestors.
     *
     *     [identifier][] = 'ancestor-identifier'
     *
     * @var array
     */
    protected $directAncestorMapping = [];

    /**
     * A mapping between identifiers and direct descendents.
     *
     *     [identifier][] = 'descendent-identifier'
     *
     * @var array
     */
    protected $directDescendentMapping = [];

    /**
     * A mapping between identifiers and all ancestors.
     *
     *     [identifier][] = 'any-ancestor-identifier'
     *
     * @var array
     */
    protected $ancestorMapping = [];

    /**
     * A mapping between identifiers and all descendents.
     *
     *     [identifier][] = 'any-descendent-identifier'
     *
     * @var array
     */
    protected $descendentMapping = [];

    /**
     * A collection of comment prototype objects.
     *
     * @var array
     */
    protected $comments = [];

    /**
     * Sets the comment ID path mapping.
     *
     * @param array $mapping The ID > path mapping.
     */
    public function setCommentIdPathMapping($mapping)
    {
        $this->commentIdPathMapping = $mapping;
    }

    /**
     * Gets the comment ID path mapping.
     *
     * @return array
     */
    public function getCommentIdPathMapping()
    {
        return $this->commentIdPathMapping;
    }

    /**
     * Sets the comment identifier depth mapping.
     *
     * @param array $mapping The ID > depth mapping.
     */
    public function setIdentifierDepthMapping($mapping)
    {
        $this->commentDepthMapping = $mapping;
    }

    /**
     * Gets the comment identifier depth mapping.
     *
     * @return array
     */
    public function getIdentifierDepthMapping()
    {
        return $this->commentDepthMapping;
    }

    /**
     * Sets the comment depth mapping.
     *
     * @param array $mapping The depth > comment mapping.
     */
    public function setCommentDepthMapping($mapping)
    {
        $this->depthMapping = $mapping;
    }

    /**
     * Gets the comment depth mapping.
     *
     * @return array
     */
    public function getCommentDepthMapping()
    {
        return $this->depthMapping;
    }

    /**
     * Sets the director ancestor comment mapping.
     *
     * @param array $mapping The mapping.
     */
    public function setDirectAncestorMapping($mapping)
    {
        $this->directAncestorMapping = $mapping;
    }

    /**
     * Gets the direct ancestor comment mapping.
     *
     * @return array
     */
    public function getDirectAncestorMapping()
    {
        return $this->directAncestorMapping;
    }

    /**
     * Sets the direct descendent mapping table.
     *
     * @param array $mapping The mapping.
     */
    public function setDirectDescendentMapping($mapping)
    {
        $this->directDescendentMapping = $mapping;
    }

    /**
     * Gets the direct descendent mapping.
     *
     * @return array
     */
    public function getDirectDescendentMapping()
    {
        return $this->directDescendentMapping;
    }

    /**
     * Sets the ancestor mapping table.
     *
     * @param array $mapping The mapping.
     */
    public function setAncestorMapping($mapping)
    {
        $this->ancestorMapping = $mapping;
    }

    /**
     * Gets the ancestor mapping table.
     *
     * @return array
     */
    public function getAncestorMapping()
    {
        return $this->ancestorMapping;
    }

    /**
     * Sets the hierarchy's comments.
     *
     * @param array $comments The comments.
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * Gets the hierarchy's comments.
     *
     * @return array
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Tests if the hierarchy contains the comment.
     *
     * @param string $commentId The comment's string identifier.
     * @return bool
     */
    public function hasComment($commentId)
    {
       return array_key_exists($commentId, $this->comments);
    }

    /**
     * Attempts to retrieve the specified comment.
     *
     * @param string $commentId The comment's string identifier.
     * @return CommentContract|null
     */
    public function getComment($commentId)
    {
        if ($this->hasComment($commentId)) {
            return $this->comments[$commentId];
        }

        return null;
    }

    /**
     * Executes the mutation callback on each comment in the hierarchy.
     *
     * @param callable $callback The callback to execute.
     */
    public function mapComments(callable $callback)
    {
        $this->comments = array_map($callback, $this->comments);
    }

    /**
     * Gets the depth of the provided comment identifier.
     *
     * @param string $commentId The comment's identifier.
     * @return integer
     */
    public function getDepth($commentId)
    {
        if (array_key_exists($commentId, $this->commentDepthMapping)) {
            return $this->commentDepthMapping[$commentId] + 1;
        }

        return 1;
    }

    /**
     * Indicates if the comment has a direct ancestor.
     *
     * @param string $commentId The comment ID.
     * @return bool
     */
    public function hasAncestor($commentId)
    {
        return array_key_exists($commentId, $this->directAncestorMapping);
    }

    /**
     * Gets the comment's ancestor identifiers.
     *
     * @param string $commentId The comment's identifier.
     * @return string[]
     */
    public function getAllAncestors($commentId)
    {
        if (array_key_exists($commentId, $this->ancestorMapping)) {
            return $this->ancestorMapping[$commentId];
        }

        return [];
    }

    /**
     * Gets the comment's parent identifier, if any.
     *
     * @param string $commentId The comment's identifier.
     * @return string|null
     */
    public function getParent($commentId)
    {
        if (array_key_exists($commentId, $this->directAncestorMapping)) {
            return $this->directAncestorMapping[$commentId];
        }

        return null;
    }

    /**
     * Gets the comment's descendent identifiers, if any.
     *
     * This method will all comment identifiers from:
     *    Comment Depth + 1 to MaxSubThreadDepth
     *
     * @param string $commentId The comment's identifier.
     * @return string[]
     */
    public function getAllDescendents($commentId)
    {
        if (array_key_exists($commentId, $this->descendentMapping)) {
            return $this->descendentMapping[$commentId];
        }

        return [];
    }

    /**
     * Gets the comment's direct descendent identifiers, if any.
     *
     * This method will return all comment identifiers from:
     *    Comment Depth + 1
     *
     * @param string $commentId The comment's identifier.
     * @return string[]
     */
    public function getDirectDescendents($commentId)
    {
        if (array_key_exists($commentId, $this->directDescendentMapping)) {
            return $this->directDescendentMapping[$commentId];
        }

        return [];
    }

    public function getTotalCommentCount()
    {
        return count($this->comments);
    }

    public function getRootLevelCommentCount()
    {
        if (array_key_exists(0, $this->depthMapping)) {
            return count($this->depthMapping[0]);
        }

        return 0;
    }

}