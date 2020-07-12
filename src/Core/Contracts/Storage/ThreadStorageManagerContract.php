<?php

namespace Stillat\Meerkat\Core\Contracts\Storage;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContract;
use Stillat\Meerkat\Core\ValidationResult;

interface ThreadStorageManagerContract
{


    /**
     * Validates the storage driver configuration.
     *
     * @return ValidationResult
     */
    public function validate();

    /**
     * @param false $withTrashed
     * @return ThreadContract[]
     */
    public function getAllThreads($withTrashed = false);
    public function getAllThreadIds($includeTrashed = false);
    public function existsForContext($contextId, $withTrashed);

    /**
     * @param ThreadContract $thread
     * @return CommentContract[]
     */
    public function getAllComments(ThreadContract $thread);

    /**
     * @param $threadId
     * @return CommentContract[]
     */
    public function getAllCommentsById($threadId);

    public function findById($id, $withTrashed = false, $includeComments = true);
    public function save(ThreadContract $thread);
    public function determineVirtualPathById($id);
    public function delete(ThreadContract $thread);
    public function deleteById($id);
    public function softDelete(ThreadContract $thread);
    public function softDeleteById($id);
    public function removeById($id);
    public function moveThread($sourceThreadId, $targetThreadId);
    public function restoreThread($threadId);


}