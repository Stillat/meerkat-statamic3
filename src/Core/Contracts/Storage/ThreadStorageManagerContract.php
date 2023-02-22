<?php

namespace Stillat\Meerkat\Core\Contracts\Storage;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContextContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContract;
use Stillat\Meerkat\Core\Threads\Thread;
use Stillat\Meerkat\Core\Threads\ThreadHierarchy;
use Stillat\Meerkat\Core\Threads\ThreadMetaData;
use Stillat\Meerkat\Core\ValidationResult;

/**
 * Interface ThreadStorageManagerContract
 *
 * Provides a consistent API for interaction with Meerkat threads.
 *
 * @since 2.0.0
 */
interface ThreadStorageManagerContract
{
    /**
     * Validates the storage driver configuration.
     *
     * @return ValidationResult
     */
    public function validate();

    /**
     * Attempts to retrieve all comment threads.
     *
     * @param  bool  $withTrashed Whether to include soft deleted threads.
     * @param  bool  $withComments Whether or not to include comments.
     * @return ThreadContract[]
     */
    public function getAllThreads($withTrashed = false, $withComments = false);

    /**
     * Returns the identifiers of all currently stored threads.
     *
     * @param  bool  $includeTrashed Indicates if soft-deleted threads should be included.
     * @return array
     */
    public function getAllThreadIds($includeTrashed = false);

    /**
     * Returns a value indicating if a thread exists with the provided identifier.
     *
     * @param  string  $contextId The thread's identifier.
     * @param  bool  $withTrashed Indicates if soft-deleted threads are considered.
     * @return bool
     */
    public function existsForContext($contextId, $withTrashed);

    /**
     * Updates the meta data for the provided thread.
     *
     * @param  string  $contextId The thread's string identifier.
     * @return bool
     */
    public function updateMetaData($contextId, ThreadMetaData $metaData);

    /**
     * Retrieves the comments for the provided thread.
     *
     * @return ThreadHierarchy
     */
    public function getAllComments(ThreadContract $thread);

    /**
     * Gets all the comments for the provided thread identifier.
     *
     * @param  string  $threadId The thread's string identifier.
     * @return ThreadHierarchy
     */
    public function getAllCommentsById($threadId);

    /**
     * Returns all comments across all threads.
     *
     * @return CommentContract[]
     */
    public function getAllSystemComments();

    /**
     * Returns all comments across all threads, for the currently authenticated user.
     *
     * @return CommentContract[]
     */
    public function getAllSystemCommentsForCurrentUser();

    /**
     * Returns all comments across all threads, for the provided user.
     *
     * @param  string  $userId The user's identifier.
     * @return CommentContract[]
     */
    public function getAllCommentsForUserId($userId);

    /**
     * Queries all system comments using the provided query builder.
     *
     * @param  callable  $builderCallback The builder callback.
     * @return CommentContract[]
     */
    public function query($builderCallback);

    /**
     * Attempts to locate a thread by its identifier.
     *
     * @param  string  $id The thread's identifier.
     * @param  bool  $withTrashed Indicates if soft-deleted threads should be considered.
     * @param  bool  $includeComments Indicates if comments should be included with the thread.
     * @return Thread|null
     */
    public function findById($id, $withTrashed = false, $includeComments = true);

    /**
     * Attempts the provided thread.
     *
     * @param  ThreadContract  $thread The thread instance.
     * @return bool
     */
    public function save(ThreadContract $thread);

    /**
     * Generates an internal path for the given thread identifier.
     *
     * @param  string  $id The thread's identifier.
     * @return string
     */
    public function determineVirtualPathById($id);

    /**
     * Attempts to permanently delete the provided thread instance.
     *
     * @param  ThreadContract  $thread The thread instance.
     * @return bool
     */
    public function delete(ThreadContract $thread);

    /**
     * Attempts to remove a thread based on its identifier.
     *
     * @param  string  $id The thread's identifier.
     * @return bool
     */
    public function deleteById($id);

    /**
     * Attempts to soft-delete the provided thread instance.
     *
     * @param  ThreadContract  $thread The thread instance.
     * @return bool
     */
    public function softDelete(ThreadContract $thread);

    /**
     * Attempts to soft-delete a thread by its identifier.
     * s
     *
     * @param  string  $id The thread's identifier.
     * @return bool
     */
    public function softDeleteById($id);

    /**
     * Attempts to permanently remove a thread by its identifier.
     *
     * @param  string  $id The thread's identifier.
     * @return bool
     */
    public function removeById($id);

    /**
     * Attempts to move comments from one thread to another thread.
     *
     * @param  string  $sourceThreadId The identifier of the thread to move data from.
     * @param  string  $targetThreadId The identifier of the thread to move data to.
     * @return bool
     */
    public function moveThread($sourceThreadId, $targetThreadId);

    /**
     * Attempts to restore a thread by it's identifier.
     *
     * @param  string  $threadId The thread's identifier.
     * @return bool
     */
    public function restoreThread($threadId);

    /**
     * Attempts to create a new thread for the provided context.
     *
     * @param  ThreadContextContract  $context The thread's context.
     * @return bool
     */
    public function createForContext(ThreadContextContract $context);
}
