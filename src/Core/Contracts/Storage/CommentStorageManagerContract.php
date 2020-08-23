<?php

namespace Stillat\Meerkat\Core\Contracts\Storage;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Data\Mutations\ChangeSet;
use Stillat\Meerkat\Core\Storage\Paths;
use Stillat\Meerkat\Core\Threads\ThreadHierarchy;

/**
 * Interface CommentStorageManagerContract
 *
 * Provides a consistent API for managing comment interactions and storage.
 *
 * @package Stillat\Meerkat\Core\Contracts\Storage
 * @since 2.0.0
 */
interface CommentStorageManagerContract
{

    /**
     * Gets the virtual path manager.
     *
     * @return Paths
     */
    public function getPaths();

    /**
     * Attempts to locate a comment by it's identifier.
     *
     * @param string $id The comment's string identifier.
     * @return CommentContract|null
     */
    public function findById($id);

    /**
     * Generates a virtual path for the provided thread and comment.
     *
     * @param string $threadId The thread identifier.
     * @param string $commentId The comment identifier.
     * @return string
     */
    public function generateVirtualPath($threadId, $commentId);

    /**
     * Constructs a comment from the prototype data.
     *
     * @param array $data The comment prototype.
     * @return CommentContract|null
     */
    public function makeFromArrayPrototype($data);

    /**
     * Attempts to get the storage path for the provided comment.
     *
     * @param string $commentId The comment's identifier.
     * @return string
     */
    public function getPathById($commentId);

    /**
     * Attempts to get the reply storage path for the provided parent and child comment.
     *
     * @param string $parentId The parent comment's identifier.
     * @param string $childId The child comment's identifier.
     * @return string
     */
    public function getReplyPathById($parentId, $childId);

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
     * Retrieves a list of all changes made to the comment.
     *
     * @param CommentContract $comment The comment to check.
     * @return ChangeSet
     */
    public function getMutationChangeSet(CommentContract $comment);

    /**
     * Attempts to update the comment data.
     *
     * @param CommentContract $comment The comment to save.
     * @return bool
     */
    public function update(CommentContract $comment);

    /**
     * Attempts to update the comment's spam status.
     *
     * @param CommentContract $comment The comment to update.
     * @param bool $isSpam Whether or not the comment is spam.
     * @return bool
     */
    public function setSpamStatus(CommentContract $comment, $isSpam);

    /**
     * Attempts to update the comment's spam status.
     *
     * @param string $commentId The comment's identifier.
     * @param bool $isSpam Whether or not the comment is spam.
     * @return bool
     */
    public function setSpamStatusById($commentId, $isSpam);

    /**
     * Attempts to mark the comment as spam.
     *
     * @param CommentContract $comment The comment to update.
     * @return bool
     */
    public function setIsSpam(CommentContract $comment);

    /**
     * Attempts to mark the comment as spam.
     *
     * @param string $commentId The comment's identifier.
     * @return bool
     */
    public function setIsSpamById($commentId);

    /**
     * Attempts to mark the comment as not-spam.
     *
     * @param CommentContract $comment The comment to update.
     * @return bool
     */
    public function setIsHam(CommentContract $comment);

    /**
     * Attempts to mark the comment as not-spam.
     *
     * @param string $commentId The comment's identifier.
     * @return bool
     */
    public function setIsHamById($commentId);

    /**
     * Attempts to update the comment's published/approved status.
     *
     * @param CommentContract $comment The comment to update.
     * @param bool $isApproved Whether the comment is "published".
     * @return bool
     */
    public function setApprovedStatus(CommentContract $comment, $isApproved);

    /**
     * Attempts to update the comment's published/approved status.
     *
     * @param string $commentId The comment's identifier.
     * @param bool $isApproved Whether the comment is "published".
     * @return bool
     */
    public function setApprovedStatusById($commentId, $isApproved);

    /**
     * Attempts to mark the comment as approved/published.
     *
     * @param CommentContract $comment The comment to update.
     * @return bool
     */
    public function setIsApproved(CommentContract $comment);

    /**
     * Attempts to mark the comment as approved/published.
     *
     * @param string $commentId The comment's identifier.
     * @return bool
     */
    public function setIsApprovedById($commentId);

    /**
     * Attempts to mark the comment as un-approved/not-published.
     *
     * @param CommentContract $comment The comment to update.
     * @return bool
     */
    public function setIsNotApproved(CommentContract $comment);

    /**
     * Attempts to mark the comment as un-approved/not-published.
     *
     * @param string $commentId The comment's identifier.
     * @return bool
     */
    public function setIsNotApprovedById($commentId);

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

    /**
     * Attempts to locate the comment's child comments.
     *
     * @param string $commentId The comment identifier.
     * @return string[]
     */
    public function getDescendents($commentId);

    /**
     * Attempts to locate the comment's child comments and paths.
     *
     * @param string $commentId The comment identifier.
     * @return string[]
     */
    public function getDescendentsPaths($commentId);

    /**
     * Attempts to locate the comment's parent comments.
     *
     * @param string $commentId The comment identifier.
     * @return string[]
     */
    public function getAncestors($commentId);

    /**
     * Attempts to locate the comment's parent comments and paths.
     *
     * @param string $commentId The comment identifier.
     * @return string[]
     */
    public function getAncestorsPaths($commentId);

    /**
     * Attempts to locate the comment's parent and child comment identifiers.
     *
     * @param string $commentId The comment identifier.
     * @return string[]
     */
    public function getRelatedComments($commentId);

    /**
     * Attempts to locate the comment's parent and child comment identifiers and paths.
     *
     * @param string $commentId The comment's identifier.
     * @return string[]
     */
    public function getRelatedCommentsPaths($commentId);

    /**
     * Attempts to remove the requested comment.
     *
     * @param string $commentId The comment's identifier.
     * @return bool
     */
    public function removeById($commentId);

    /**
     * Attempts to soft delete the requested comment.
     *
     * @param string $commentId The comment's identifier.
     * @return bool
     */
    public function softDeleteById($commentId);

    /**
     * Attempts to restore a soft-deleted comment.
     *
     * @param string $commentId The comment's identifier.
     * @return bool
     */
    public function restoreById($commentId);

}
