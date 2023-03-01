<?php

namespace Stillat\Meerkat\Core\Threads;

use ArrayAccess;
use Iterator;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

/**
 * Class ThreadHierarchy
 *
 * Represents the nested structure of a comment thread.
 *
 * @since 2.0.0
 */
class ThreadHierarchy implements ArrayAccess, Iterator
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
     * @var CommentContract[]
     */
    protected $comments = [];

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
     * Sets the comment ID path mapping.
     *
     * @param  array  $mapping The ID > path mapping.
     */
    public function setCommentIdPathMapping($mapping)
    {
        $this->commentIdPathMapping = $mapping;
    }

    /**
     * Sets the comment identifier depth mapping.
     *
     * @param  array  $mapping The ID > depth mapping.
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
     * Gets the comment depth mapping.
     *
     * @return array
     */
    public function getCommentDepthMapping()
    {
        return $this->depthMapping;
    }

    /**
     * Sets the comment depth mapping.
     *
     * @param  array  $mapping The depth > comment mapping.
     */
    public function setCommentDepthMapping($mapping)
    {
        $this->depthMapping = $mapping;
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
     * Sets the director ancestor comment mapping.
     *
     * @param  array  $mapping The mapping.
     */
    public function setDirectAncestorMapping($mapping)
    {
        $this->directAncestorMapping = $mapping;
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
     * Sets the direct descendent mapping table.
     *
     * @param  array  $mapping The mapping.
     */
    public function setDirectDescendentMapping($mapping)
    {
        $this->directDescendentMapping = $mapping;
    }

    /**
     * Gets the descendent mapping table.
     *
     * @return array
     */
    public function getDescendentMapping()
    {
        return $this->descendentMapping;
    }

    /**
     * Sets the descendent mapping table.
     *
     * @param  array  $mapping The mapping.
     */
    public function setDescendentMapping($mapping)
    {
        $this->descendentMapping = $mapping;
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
     * Sets the ancestor mapping table.
     *
     * @param  array  $mapping The mapping.
     */
    public function setAncestorMapping($mapping)
    {
        $this->ancestorMapping = $mapping;
    }

    /**
     * Gets the hierarchy's comments.
     *
     * @return CommentContract[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Sets the hierarchy's comments.
     *
     * @param  CommentContract[]  $comments The comments.
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * Attempts to retrieve the specified comment.
     *
     * @param  string  $commentId The comment's string identifier.
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
     * Tests if the hierarchy contains the comment.
     *
     * @param  string  $commentId The comment's string identifier.
     * @return bool
     */
    public function hasComment($commentId)
    {
        return array_key_exists($commentId, $this->comments);
    }

    /**
     * Executes the mutation callback on each comment in the hierarchy.
     *
     * @param  callable  $callback The callback to execute.
     */
    public function mapComments(callable $callback)
    {
        $this->comments = array_map($callback, $this->comments);
    }

    /**
     * Gets the depth of the provided comment identifier.
     *
     * @param  string  $commentId The comment's identifier.
     * @return int
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
     * @param  string  $commentId The comment ID.
     * @return bool
     */
    public function hasAncestor($commentId)
    {
        return array_key_exists($commentId, $this->directAncestorMapping);
    }

    /**
     * Gets the comment's ancestor identifiers.
     *
     * @param  string  $commentId The comment's identifier.
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
     * @param  string  $commentId The comment's identifier.
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
     * @param  string  $commentId The comment's identifier.
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
     * @param  string  $commentId The comment's identifier.
     * @return string[]
     */
    public function getDirectDescendents($commentId)
    {
        if (array_key_exists($commentId, $this->directDescendentMapping)) {
            return $this->directDescendentMapping[$commentId];
        }

        return [];
    }

    /**
     * Gets the total comment count.
     *
     * @return int
     */
    public function getTotalCommentCount()
    {
        return count($this->comments);
    }

    /**
     * Gets the total comment count at the root (0) level.
     *
     * @return int
     */
    public function getRootLevelCommentCount()
    {
        if (array_key_exists(0, $this->depthMapping)) {
            return count($this->depthMapping[0]);
        }

        return 0;
    }

    /**
     * Offset to retrieve
     *
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param  mixed  $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet(mixed $offset): mixed
    {
        if ($this->offsetExists($offset)) {
            return $this->comments[$offset];
        }

        return null;
    }

    /**
     * Whether a offset exists
     *
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param  mixed  $offset <p>
     * An offset to check for.
     * </p>
     * @return bool true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->comments[$offset]);
    }

    /**
     * Offset to set
     *
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param  mixed  $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param  mixed  $value <p>
     * The value to set.
     * </p>
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->comments[] = $value;
        } else {
            $this->comments[$offset] = $value;
        }
    }

    /**
     * Offset to unset
     *
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param  mixed  $offset <p>
     * The offset to unset.
     * </p>
     */
    public function offsetUnset(mixed $offset): void
    {
        if ($this->offsetExists($offset)) {
            unset($this->directAncestorMapping[$offset]);
        }
    }

    /**
     * Return the current element
     *
     * @link https://php.net/manual/en/iterator.current.php
     *
     * @return mixed Can return any type.
     */
    public function current(): mixed
    {
        return current($this->comments);
    }

    /**
     * Move forward to next element
     *
     * @link https://php.net/manual/en/iterator.next.php
     */
    public function next(): void
    {
        next($this->comments);
    }

    /**
     * Checks if current position is valid
     *
     * @link https://php.net/manual/en/iterator.valid.php
     *
     * @return bool The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid(): bool
    {
        return isset($this->comments[$this->key()]);
    }

    /**
     * Return the key of the current element
     *
     * @link https://php.net/manual/en/iterator.key.php
     *
     * @return string|float|int|bool|null scalar on success, or null on failure.
     */
    public function key(): mixed
    {
        return key($this->comments);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link https://php.net/manual/en/iterator.rewind.php
     *
     * @return void Any returned value is ignored.
     */
    public function rewind(): void
    {
        reset($this->comments);
    }
}
