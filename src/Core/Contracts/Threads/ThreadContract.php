<?php

namespace Stillat\Meerkat\Core\Contracts\Threads;

use Serializable;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\DataObjectContract;
use Stillat\Meerkat\Core\Contracts\StorableContract;
use Stillat\Meerkat\Core\Threads\ThreadHierarchy;
use Stillat\Meerkat\Core\Threads\ThreadMetaData;

/**
 * Interface ThreadContract
 *
 * Defines a standardized comment thread structure
 *
 * @package Stillat\Meerkat\Core\Contracts\Threads
 * @since 2.0.0
 */
interface ThreadContract extends DataObjectContract, StorableContract, Serializable
{

    const LEGACY_SOFT_DELETE_PREFIX = '_';
    const KEY_ID = 'id';
    const KEY_CONTEXT_ID = 'context_id';
    const KEY_PATH = 'path';
    const KEY_DIRNAME = 'dirname';
    const KEY_TYPE = 'type';
    const KEY_TYPE_FILE = 'file';

    /**
     * Returns the string identifier for the current thread.
     *
     * @return string
     */
    public function getId();

    /**
     * Sets the ID for the current thread.
     *
     * @param string $id
     *
     * @return void
     */
    public function setId($id);

    /**
     * Attempts to locate and return the thread's context.
     *
     * @return ThreadContextContract|null
     */
    public function getContext();

    /**
     * Sets the thread's context.
     *
     * @param ThreadContextContract $context
     *
     * @return void
     */
    public function setContext(ThreadContextContract $context);

    /**
     * Attempts to locate and return the thread context string identifier.
     *
     * @return string
     */
    public function getContextId();

    /**
     * Sets if the thread is usable, based on it's persistence state.
     *
     * @param bool $isUsable If the thread is usable.
     * @return void
     */
    public function setIsUsable($isUsable);

    /**
     * Returns a value indicating if the thread is usable.
     *
     * @return bool
     */
    public function getIsUsable();

    /**
     * Sets the context identifier for the thread.
     *
     * @param string $id
     *
     * @return void
     */
    public function setContextId($id);

    /**
     * Gets the comments for the current thread.
     *
     * @return CommentContract[]
     */
    public function getComments();

    /**
     * Sets the comments for the current thread.
     *
     * @param CommentContract[] $comments The comments to set on the thread.
     * @return void
     */
    public function setComments($comments);

    /**
     * Gets the total number of comments in the thread.
     *
     * @return int
     */
    public function getTotalCommentCount();

    /**
     * Sets the total number of comments in the thread.
     *
     * @param int $count The total number of comments in the thread.
     * @return void
     */
    public function setTotalCommentCount($count);

    /**
     * Returns the total number of root comments in the thread.
     *
     * @return int
     */
    public function getRootCommentCount();

    /**
     * Sets the total number of root comment counts.
     *
     * @param int $count The total number of root-level comments in the thread.
     * @return void
     */
    public function setRootCommentCount($count);

    /**
     * Returns a value indicating if the current thread was soft deleted.
     *
     * @return boolean
     */
    public function isTrashed();

    /**
     * Sets whether or not the Meerkat thread was soft-deleted.
     *
     * @param bool $isTrashed A value indicating if the thread wa soft-deleted.
     * @return void
     */
    public function setIsTrashed($isTrashed);

    /**
     * Sets the thread's meta data.
     *
     * @param ThreadMetaData $metaData The meta data.
     * @return void
     */
    public function setMetaData(ThreadMetaData $metaData);

    /**
     * Gets the thread's meta data, if available.
     *
     * @return ThreadMetaData|null
     */
    public function getMetaData();

    /**
     * Sets the thread's hierarchy.
     *
     * @param ThreadHierarchy $hierarchy The thread's structure.
     * @return void
     */
    public function setHierarchy(ThreadHierarchy $hierarchy);

    /**
     * Gets the thread's hierarchy.
     *
     * @return ThreadHierarchy|null
     */
    public function getHierarchy();

    /**
     * Converts the thread's comments into an array; sets the comment reply property to the provided name
     *
     * @param string $repliesName The replies data property to use.
     * @return array
     */
    public function getCommentCollection($repliesName);

    /**
     * Saves the provided comment to the thread.
     *
     * @param CommentContract $comment The comment to attach to the thread.
     * @return bool
     */
    public function attachNewComment(CommentContract $comment);

}

