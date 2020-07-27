<?php

namespace Stillat\Meerkat\Core\Contracts\Storage;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Storage\Paths;
use Stillat\Meerkat\Core\Threads\ThreadHierarchy;

interface CommentStorageManagerContract
{

    /**
     * Gets the virtual path manager.
     *
     * @return Paths
     */
    public function getPaths();

    public function findById($id);

    public function generateVirtualPath($threadId, $commentId);

    public function getPathById($commentId);

    /**
     * Gets all comments for the requested thread.
     *
     * @param string $threadId The identifier of the thread.
     * @return ThreadHierarchy
     */
    public function getCommentsForThreadId($threadId);

    /**
     * Attempts to save the comment data.
     *
     * @param CommentContract $comment The comment to save.
     * @return bool
     */
    public function save(CommentContract $comment);

    /**
     * Attempts to update the comment data.
     *
     * @param CommentContract $comment The comment to save.
     * @return bool
     */
    public function update(CommentContract $comment);

    /**
     * Tests if the provided comment identifier is a descendent of the parent.
     *
     * @param string $commentId The child identifier to test.
     * @param string $testParent The parent identifier to test.
     * @return bool
     */
    public function isChildOf($commentId, $testParent);

    /**
     * Tests if the parent identifier is the direct ancestor of the provided comment.
     *
     * @param string $testParent The parent identifier to test.
     * @param string $commentId The child identifier to test.
     * @return bool
     */
    public function isParentOf($testParent, $commentId);

}