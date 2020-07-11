<?php

namespace Stillat\Meerkat\Core\Contracts\Threads;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContract;

/**
 * Provides a consistent API for managing Meerkat threads
 *
 * @since 2.0.0
 */
interface ThreadManagerContract
{

    /**
     * Returns a collection of all thread IDs.
     *
     * @param boolean $includeTrashed Indicates if Meerkat should locate trashed threads.
     *
     * @return array
     */
    public function getAllThreadIds($includeTrashed = false);

    /**
     * Returns a value indicating if a thread exists for the provided context identifier.
     *
     * @param  string  $contextId   The context's string identifier.
     * @param  boolean $withTrashed Indicates if Meerkat should look for soft-deleted threads.
     * @return boolean
     */
    public function existsForContext($contextId, $withTrashed);

    /**
     * Returns all comments attached to the provided thread.
     *
     * @param  ThreadContract $thread
     *
     * @return CommentContract[]
     */
    public function all(ThreadContract $thread);

    /**
     * Attempts to locate and return all comments attached
     * to a thread with the provided string identifier.
     *
     * @param  string $id
     *
     * @return CommentContract[]
     */
    public function allForId($id);

    /**
     * Persists the specified thread to disk.
     *
     * @param  ThreadContract $thread
     *
     * @return ThreadContract
     */
    public function create(ThreadContract $thread);

    /**
     * Attempts to locate and return a thread for the provided string identifier.
     *
     * @param  string  $id The string identifier of the thread to locate.
     * @param  boolean $withTrashed Indicates if Meerkat should look for soft-deleted threads.
     * @param  boolean $includeComments Indicates if Meerkat should pre-load the thread's comments.
     *
     * @return ThreadContract|null
     */
    public function findById($id, $withTrashed = false, $includeComments = true);

    /**
     * Resolves the storage path for the provided thread instance.
     *
     * @param  ThreadContract $thread
     *
     * @return string
     */
    public function determinePath(ThreadContract $thread);

    /**
     * Moves the comments from the source thread to the target thread.
     *
     * @param string $sourceThreadId The identifier of the source thread.
     * @param string $targetThreadId The identifier of the target thread.
     * @return boolean
     */
    public function moveThread($sourceThreadId, $targetThreadId);

    /**
     * Restores a previously soft-deleted thread.
     *
     * @param  string $threadId The string identifier of the thread.
     * @return boolean
     */
    public function restoreThread($threadId);

    /**
     * Resolves the storage path for the provided thread string identifier.
     *
     * @param  string $id
     *
     * @return string
     */
    public function determinePathById($id);

    /**
     * Attempts to remove a thread instance.
     *
     * @param  ThreadContract $thread The thread instance.
     * @return boolean
     */
    public function remove(ThreadContract $thread);

    /**
     * Attempts to remove a thread by it's identifier.
     *
     * @param  string $id The comment's identifier.
     * @return boolean
     */
    public function removeById($id);

}