<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Local;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Storage\ThreadStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContract;

class LocalThreadStorageManager implements  ThreadStorageManagerContract
{

    /**
     * @param false $withTrashed
     * @return ThreadContract[]
     */
    public function getAllThreads($withTrashed = false)
    {
        // TODO: Implement getAllThreads() method.
    }

    public function getAllThreadIds($includeTrashed = false)
    {
        // TODO: Implement getAllThreadIds() method.
    }

    public function existsForContext($contextId, $withTrashed)
    {
        // TODO: Implement existsForContext() method.
    }

    /**
     * @param ThreadContract $thread
     * @return CommentContract[]
     */
    public function getAllComments(ThreadContract $thread)
    {
        // TODO: Implement getAllComments() method.
    }

    /**
     * @param $threadId
     * @return CommentContract[]
     */
    public function getAllCommentsById($threadId)
    {
        // TODO: Implement getAllCommentsById() method.
    }

    public function findById($id, $withTrashed = false, $includeComments = true)
    {
        // TODO: Implement findById() method.
    }

    public function save(ThreadContract $thread)
    {
        // TODO: Implement save() method.
    }

    public function determineVirtualPathById($id)
    {
        // TODO: Implement determineVirtualPathById() method.
    }

    public function delete(ThreadContract $thread)
    {
        // TODO: Implement delete() method.
    }

    public function deleteById($id)
    {
        // TODO: Implement deleteById() method.
    }

    public function softDelete(ThreadContract $thread)
    {
        // TODO: Implement softDelete() method.
    }

    public function softDeleteById($id)
    {
        // TODO: Implement softDeleteById() method.
    }

    public function removeById($id)
    {
        // TODO: Implement removeById() method.
    }

    public function moveThread($sourceThreadId, $targetThreadId)
    {
        // TODO: Implement moveThread() method.
    }

    public function restoreThread($threadId)
    {
        // TODO: Implement restoreThread() method.
    }
}